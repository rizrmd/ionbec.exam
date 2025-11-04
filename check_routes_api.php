<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "🔍 CHECKING ROUTES FOR DELIVERY 152 (Hash: 26EAx9r9)\n";
echo "=================================================\n\n";

try {
    // Get all routes that might handle answer submission
    echo "📋 RELEVANT API ROUTES:\n";

    $routes = app('router')->getRoutes();
    $relevantRoutes = [];

    foreach ($routes as $route) {
        $uri = $route->uri();
        $methods = $route->methods();

        // Look for routes that might handle exams/answers
        if (
            (strpos($uri, 'exam') !== false || strpos($uri, 'answer') !== false) &&
            (in_array('POST', $methods) || in_array('GET', $methods))
        ) {
            $relevantRoutes[] = [
                'methods' => implode(', ', $methods),
                'uri' => $uri,
                'action' => $route->getActionName(),
                'name' => $route->getName()
            ];
        }
    }

    foreach ($relevantRoutes as $route) {
        echo "   " . $route['methods'] . " " . $route['uri'];
        if ($route['name']) echo " [" . $route['name'] . "]";
        echo " -> " . $route['action'] . "\n";
    }

    echo "\n🎯 CHECKING SPECIFIC ROUTE PATTERNS:\n";

    // Check patterns that might match the URL
    $patterns = [
        'api/exams/{hash}/answer',
        'api/exams/{hash}/answers',
        'api/exams/{delivery}/answer',
        'api/exams/{delivery}/answers',
        'exams/{hash}/answer',
        'exams/{hash}/answers',
    ];

    foreach ($patterns as $pattern) {
        echo "   Pattern: $pattern\n";
        try {
            $route = $routes->matchByPattern($pattern);
            if ($route) {
                echo "     ✅ Found: " . implode(', ', $route->methods()) . " -> " . $route->getActionName() . "\n";
            }
        } catch (Exception $e) {
            echo "     ❌ Not found or error: " . $e->getMessage() . "\n";
        }
    }

    echo "\n🔍 TESTING ROUTE MATCHING:\n";

    // Test actual URL patterns that might be called
    $testUrls = [
        'api/exams/26EAx9r9/answer',
        'api/exams/26EAx9r9/answers',
        'exams/26EAx9r9/answer',
        'exams/26EAx9r9/answers',
        'api/delivery/26EAx9r9/answer',
        'api/delivery/26EAx9r9/answers',
    ];

    foreach ($testUrls as $url) {
        echo "   Testing: $url\n";
        try {
            $request = \Illuminate\Http\Request::create($url, 'POST');
            $route = $routes->match($request);
            if ($route) {
                echo "     ✅ Matches: " . $route->getActionName() . "\n";

                // Get the controller and method
                $action = $route->getActionName();
                if (strpos($action, '@') !== false) {
                    list($controller, $method) = explode('@', $action);
                    echo "     Controller: $controller\n";
                    echo "     Method: $method\n";

                    // Check if controller file exists
                    $controllerFile = base_path(str_replace('App\\', 'app/', str_replace('\\', '/', $controller))) . '.php';
                    if (file_exists($controllerFile)) {
                        echo "     ✅ Controller file exists: $controllerFile\n";
                    } else {
                        echo "     ❌ Controller file missing: $controllerFile\n";
                    }
                }
            }
        } catch (Exception $e) {
            echo "     ❌ No match: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }

    // Check if there are any middleware that might be blocking
    echo "🛡️  MIDDLEWARE CHECK:\n";
    $routeNames = ['exams.answer', 'exams.answers', 'api.exams.answer', 'api.exams.answers'];

    foreach ($routeNames as $routeName) {
        if ($routes->hasNamedRoute($routeName)) {
            $route = $routes->getByName($routeName);
            echo "   Route: $routeName\n";
            echo "   Middleware: " . implode(', ', $route->middleware()) . "\n\n";
        }
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🎯 ROUTE ANALYSIS COMPLETE\n";