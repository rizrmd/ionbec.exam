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

echo "\n\nService Providers Status:\n";
$providers = $app->getLoadedProviders();
echo "Total providers loaded: " . count($providers) . "\n";

echo "\nKey providers:\n";
$keyProviders = [
    'App\Providers\RouteServiceProvider',
    'Dentro\Yalr\RouteServiceProvider',
    'App\Providers\AppServiceProvider',
];

foreach ($keyProviders as $provider) {
    if (isset($providers[$provider])) {
        echo "  ✓ $provider is loaded\n";
    } else {
        echo "  ✗ $provider is NOT loaded\n";
    }
}