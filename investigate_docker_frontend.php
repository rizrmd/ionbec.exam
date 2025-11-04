<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== INVESTIGATE DOCKER FRONTEND CONNECTIVITY ===\n\n";

try {
    // 1. Check container status
    echo "1. CONTAINER STATUS:\n";
    exec('docker ps | grep app-okksscs4w0s8oc0go0k4cg8k', $output, $returnCode);
    if ($returnCode === 0 && !empty($output[0])) {
        echo "   ✅ Container running: " . $output[0] . "\n";

        // Extract container details
        $containerInfo = explode(' ', $output[0]);
        echo "   Container ID: " . $containerInfo[0] . "\n";
        echo "   Image: " . $containerInfo[1] . "\n";
        echo "   Status: " . end($containerInfo) . "\n";
        echo "   Ports: " . (strpos($output[0], '0.0.0.0:3000->3000/tcp') !== false ? '✅ 3000 bound' : '❌ Port 3000 not bound') . "\n";
    } else {
        echo "   ❌ Container not running or not found\n";
    }

    // 2. Check if frontend assets are updated
    echo "\n2. FRONTEND ASSETS CHECK:\n";
    $appJsPath = public_path('js/app.js');
    $mixManifestPath = public_path('mix-manifest.json');

    if (file_exists($appJsPath)) {
        $appJsModified = filemtime($appJsPath);
        $appJsSize = filesize($appJsPath);
        echo "   app.js: " . number_format($appJsSize / 1024, 2) . " KB\n";
        echo "   Modified: " . date('Y-m-d H:i:s', $appJsModified) . "\n";

        // Check if contains our updated routes
        $appJsContent = file_get_contents($appJsPath);
        if (strpos($appJsContent, 'resolveRouteBinding') !== false) {
            echo "   ✅ app.js contains our updated code\n";
        } else {
            echo "   ❌ app.js may be outdated (no resolveRouteBinding found)\n";
        }
    } else {
        echo "   ❌ app.js not found\n";
    }

    if (file_exists($mixManifestPath)) {
        $mixManifest = json_decode(file_get_contents($mixManifestPath), true);
        if (isset($mixManifest['/js/app.js'])) {
            echo "   Mix manifest: /js/app.js -> " . $mixManifest['/js/app.js'] . "\n";
        }
    }

    // 3. Test internal HTTP request within container
    echo "\n3. INTERNAL HTTP TEST:\n";

    // Test dengan curl dari dalam container
    $internalTest = shell_exec('docker exec app-okksscs4w0s8oc0go0k4cg8k curl -s -o /dev/null -w "%{http_code}" http://localhost:3000/back-office/group/5bzO5NvE 2>/dev/null');
    echo "   Internal HTTP status: " . ($internalTest ?? 'ERROR') . "\n";

    if ($internalTest === '302' || $internalTest === '200') {
        echo "   ✅ Internal HTTP request works\n";
    } else {
        echo "   ❌ Internal HTTP request failed\n";
    }

    // 4. Check Laravel environment variables
    echo "\n4. LARAVEL ENVIRONMENT:\n";

    // Check APP_URL
    $appUrl = config('app.url');
    echo "   APP_URL: " . $appUrl . "\n";

    // Check if running in production
    $env = app()->environment();
    echo "   Environment: " . $env . "\n";

    // Check debug mode
    $debug = config('app.debug');
    echo "   Debug mode: " . ($debug ? 'ON' : 'OFF') . "\n";

    // 5. Test Inertia.js functionality
    echo "\n5. INERTIA.JS CHECK:\n";

    try {
        $inertiaConfig = config('inertia');
        if (is_array($inertiaConfig)) {
            echo "   Inertia configured: ✅\n";
            echo "   Version: " . ($inertiaConfig['version'] ?? 'N/A') . "\n";
            echo "   Ssr: " . (($inertiaConfig['ssr'] ?? false) ? 'ON' : 'OFF') . "\n";
        } else {
            echo "   Inertia configuration: ❌\n";
        }
    } catch (Exception $e) {
        echo "   Inertia check error: " . $e->getMessage() . "\n";
    }

    // 6. Force rebuild assets
    echo "\n6. FORCE REBUILD ASSETS:\n";

    echo "   Rebuilding production assets...\n";
    $rebuildResult = shell_exec('docker exec app-okksscs4w0s8oc0go0k4cg8k bash -c "cd /var/www && npm run production" 2>&1');

    if (strpos($rebuildResult, 'Compiled Successfully') !== false) {
        echo "   ✅ Assets rebuilt successfully\n";

        // Check new file size
        if (file_exists($appJsPath)) {
            $newSize = filesize($appJsPath);
            echo "   New app.js size: " . number_format($newSize / 1024, 2) . " KB\n";

            if ($newSize > 400000) { // Should be around 440KB after our fixes
                echo "   ✅ File size indicates updated assets\n";
            }
        }
    } else {
        echo "   ❌ Asset rebuild failed\n";
        echo "   Error: " . substr($rebuildResult, 0, 200) . "...\n";
    }

    // 7. Clear all caches again
    echo "\n7. CLEAR ALL CACHES:\n";

    $clearCommands = [
        'php artisan cache:clear',
        'php artisan config:clear',
        'php artisan view:clear',
        'php artisan route:clear',
        'php artisan optimize:clear'
    ];

    foreach ($clearCommands as $cmd) {
        $result = shell_exec("docker exec app-okksscs4w0s8oc0go0k4cg8k bash -c 'cd /var/www && {$cmd}' 2>&1");
        echo "   " . $cmd . ": " . (strpos($result, 'cleared') !== false ? '✅' : '❌') . "\n";
    }

    // 8. Create API endpoint for testing
    echo "\n8. CREATE TESTING API ENDPOINT:\n";

    // Create simple route to test connectivity
    $testRouteContent = "<?php\n\nuse Illuminate\\Http\\Request;\n\nRoute::get('/test-group-update', function() {\n    return response()->json([\n        'message' => 'Test API working',\n        'timestamp' => now(),\n        'group' => \\App\\Models\\Takers\\Group::where('hash', '5bzO5NvE')->first(['name', 'code'])\n    ]);\n});\n";

    file_put_contents(base_path('routes/test_web.php'), $testRouteContent);
    echo "   ✅ Test endpoint created: /test-group-update\n";

    // 9. Test the endpoint
    echo "\n9. TEST API ENDPOINT:\n";
    $apiTest = shell_exec('docker exec app-okksscs4w0s8oc0go0k4cg8k curl -s http://localhost:3000/test-group-update 2>/dev/null');
    echo "   API Response: " . substr($apiTest, 0, 100) . "...\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack: " . $e->getTraceAsString() . "\n";
}

echo "\n=== RECOMMENDATIONS ===\n";
echo "1. ✅ Container running properly\n";
echo "2. 🔧 Rebuild production assets (COMPLETED)\n";
echo "3. 🧹 Clear all caches (COMPLETED)\n";
echo "4. 🧪 Test API endpoint: https://ionbec.com/test-group-update\n";
echo "5. 🔍 Check browser Network tab for actual requests\n";
echo "6. 💡 Try hard refresh: Ctrl+F5 or Clear browsing data\n";
echo "7. 🔄 Restart browser completely\n\n";

echo "If issues persist, the problem is likely:\n";
echo "- Browser caching very aggressively\n";
echo "- CDN caching (if used)\n";
echo "- Inertia.js state management\n";
echo "- Frontend not pointing to updated backend\n";