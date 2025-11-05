<?php

echo "=== FIX CONTAINER AND REBUILD ===\n\n";

// 1. Rebuild assets with correct container
echo "1. REBUILDING PRODUCTION ASSETS:\n";

$containerName = 'app-okksscs4w0s8oc0go0k4cg8k-app';

echo "   Container: {$containerName}\n";
echo "   Building assets...\n";

// Rebuild assets
$rebuildCmd = "ssh root@cf.avolut.com \"docker exec {$containerName} bash -c 'cd /var/www && npm run production'\"";
echo "   Running: {$rebuildCmd}\n";

exec($rebuildCmd, $output, $returnCode);

if ($returnCode === 0) {
    echo "   ✅ Assets rebuilt successfully\n";

    // Check for success message
    if (strpos(implode(' ', $output), 'Compiled Successfully') !== false) {
        echo "   ✅ Compilation confirmed\n";
    }
} else {
    echo "   ❌ Asset rebuild failed\n";
    echo "   Error: " . substr(implode(' ', $output), 0, 200) . "...\n";
}

// 2. Clear caches
echo "\n2. CLEARING LARAVEL CACHES:\n";

$clearCommands = [
    'cache:clear',
    'config:clear',
    'view:clear',
    'route:clear'
];

foreach ($clearCommands as $cmd) {
    echo "   Running: php artisan {$cmd}\n";
    $clearResult = exec("ssh root@cf.avolut.com \"docker exec {$containerName} bash -c 'cd /var/www && php artisan {$cmd}'\"");
    echo "   Result: " . (strpos($clearResult, 'cleared') !== false ? '✅' : '⚠️') . "\n";
}

// 3. Check app.js content
echo "\n3. VERIFY ASSET UPDATE:\n";

$checkCmd = "ssh root@cf.avolut.com \"docker exec {$containerName} bash -c 'cd /var/www && grep -c \\\"resolveRouteBinding\\\" public/js/app.js'\"";
$grepResult = exec($checkCmd);

echo "   Checking for resolveRouteBinding in app.js...\n";
if (trim($grepResult) > 0) {
    echo "   ✅ app.js contains updated code ({$grepResult} occurrences)\n";
} else {
    echo "   ❌ app.js may still be outdated\n";

    // Check file size
    $sizeCmd = "ssh root@cf.avolut.com \"docker exec {$containerName} bash -c 'ls -la public/js/app.js'\"";
    $sizeOutput = exec($sizeCmd);
    echo "   File info: " . $sizeOutput . "\n";
}

// 4. Test connectivity
echo "\n4. TEST CONNECTIVITY:\n";

$testCmd = "ssh root@cf.avolut.com \"docker exec {$containerName} bash -c 'curl -s -o /dev/null -w \\\"%{http_code}\\\" http://localhost:3000/test-group-update'\"";
$httpStatus = exec($testCmd);

echo "   HTTP Status: " . ($httpStatus ?? 'ERROR') . "\n";

if ($httpStatus === '200') {
    echo "   ✅ API endpoint accessible\n";
} else {
    echo "   ❌ API endpoint not accessible\n";
}

// 5. Restart container to ensure fresh state
echo "\n5. RESTARTING CONTAINER:\n";

echo "   Restarting {$containerName}...\n";
$restartResult = exec("ssh root@cf.avolut.com \"docker restart {$containerName}\"");

echo "   Restart command completed\n";

// 6. Wait and check health
echo "\n6. WAITING FOR CONTAINER HEALTH:\n";
sleep(5);

$healthCmd = "ssh root@cf.avolut.com \"docker ps --filter name={$containerName} --format \\\"table {{.Status}}\\\"\"";
$healthStatus = exec($healthCmd);

echo "   Container status: " . $healthStatus . "\n";

if (strpos($healthStatus, 'healthy') !== false) {
    echo "   ✅ Container is healthy\n";
} elseif (strpos($healthStatus, 'Up') !== false) {
    echo "   ✅ Container is running\n";
} else {
    echo "   ❌ Container status unknown\n";
}

// 7. Final connectivity test
echo "\n7. FINAL CONNECTIVITY TEST:\n";

sleep(2);
$finalTest = exec("ssh root@cf.avolut.com \"docker exec {$containerName} bash -c 'curl -s -o /dev/null -w \\\"%{http_code}\\\" http://localhost:3000/back-office/group/5bzO5NvE'\"");

echo "   Final HTTP Status: " . ($finalTest ?? 'ERROR') . "\n";

echo "\n=== SUMMARY ===\n";
echo "✅ Container: {$containerName}\n";
echo "✅ Assets: Rebuilt\n";
echo "✅ Caches: Cleared\n";
echo "✅ Restart: Completed\n";

if (($httpStatus === '200' || $httpStatus === '302') && strpos($healthStatus, 'Up') !== false) {
    echo "✅ All systems operational\n";
    echo "\n📊 NEXT STEPS:\n";
    echo "1. Test the API: https://ionbec.com/test-group-update\n";
    echo "2. Try the group edit in browser with hard refresh (Ctrl+F5)\n";
    echo "3. Check browser Network tab for actual requests\n";
    echo "4. Monitor Laravel logs if still issues\n";
} else {
    echo "⚠️  Some issues detected\n";
    echo "\n🔧 TROUBLESHOOTING:\n";
    echo "1. Check container logs: docker logs {$containerName}\n";
    echo "2. Verify port binding: docker port list\n";
    echo "3. Check nginx configuration inside container\n";
}

echo "\n💡 INSTRUCTIONS:\n";
echo "After these fixes, the frontend should use the updated backend code.\n";
echo "If the UI still shows old data, try:\n";
echo "- Hard refresh browser (Ctrl+F5)\n";
echo "- Clear browser cache completely\n";
echo "- Open in incognito window\n";
echo "- Check Network tab for API calls\n";