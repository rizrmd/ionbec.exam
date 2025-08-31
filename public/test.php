<?php
// Direct test bypassing Laravel routing
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "Direct Laravel Test\n";
echo "==================\n\n";

// Check if routes are loaded
$router = app('router');
$routes = $router->getRoutes();

echo "Total routes registered: " . count($routes) . "\n\n";

echo "Routes list:\n";
foreach ($routes as $route) {
    $methods = implode('|', $route->methods());
    $uri = $route->uri();
    $name = $route->getName() ?: 'unnamed';
    echo "  $methods $uri [$name]\n";
}

echo "\n\nLooking for test routes:\n";
$testRoute = $router->getRoutes()->match(
    app('request')->create('/test', 'GET')
);
if ($testRoute) {
    echo "  /test route found!\n";
} else {
    echo "  /test route NOT found\n";
}

$healthRoute = $router->getRoutes()->match(
    app('request')->create('/health', 'GET')
);
if ($healthRoute) {
    echo "  /health route found!\n";
} else {
    echo "  /health route NOT found\n";
}