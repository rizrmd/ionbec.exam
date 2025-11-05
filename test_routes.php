<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING NEW ROUTES ===\n\n";

use Illuminate\Support\Facades\Route;

// Get all registered routes
$routes = Route::getRoutes();

echo "Looking for new public exam routes...\n\n";

$targetRoutes = [
    'exam.login.form',
    'exam.token.login',
    'exam.login.submit',
    'exam.logout',
    'exam.waiting-room'
];

foreach ($targetRoutes as $routeName) {
    $route = $routes->getByName($routeName);

    if ($route) {
        echo "✅ Route found: {$routeName}\n";
        echo "   URI: " . $route->uri() . "\n";
        echo "   Methods: " . implode(', ', $route->methods()) . "\n";
        echo "   Action: " . $route->getActionName() . "\n\n";
    } else {
        echo "❌ Route NOT found: {$routeName}\n\n";
    }
}

echo "=== TESTING TOKEN ACCESS ===\n\n";

// Test if our controller can be instantiated
try {
    $controller = new \App\Http\Controllers\PublicTokenLoginController();
    echo "✅ PublicTokenLoginController instantiated successfully\n\n";

    // Test if the route URLs would work
    echo "Testing direct URLs:\n";
    echo "- Form: http://your-domain.com/exam-login\n";
    echo "- Direct Token: http://your-domain.com/exam/3AfDf\n";
    echo "- POST Form: http://your-domain.com/exam-login (POST)\n";
    echo "- Logout: http://your-domain.com/exam-logout\n\n";

} catch (Exception $e) {
    echo "❌ Error instantiating controller: " . $e->getMessage() . "\n\n";
}

echo "=== VERIFICATION STEPS ===\n\n";
echo "1. Deploy the code to production\n";
echo "2. Visit: https://ionbec.com/exam-login\n";
echo "3. Enter token: 3AfDf\n";
echo "4. Should redirect to waiting room\n";
echo "5. Check logs for debugging\n\n";

echo "=== ALTERNATIVE DIRECT ACCESS ===\n\n";
echo "You can also directly access:\n";
echo "https://ionbec.com/exam/3AfDf\n\n";

echo "This should automatically:\n";
echo "1. Find token 3AfDf in database\n";
echo "2. Create session with taker and delivery data\n";
echo "3. Redirect to waiting room (since scheduled_at > now)\n";
echo "4. Mark is_login = true in delivery_taker table\n\n";

echo "Expected flow for token 3AfDf:\n";
echo "1. ✅ Token found in delivery_taker table\n";
echo "2. ✅ Delivery XjrMy4rQ found and active\n";
echo "3. ✅ Taker ID 2 found\n";
echo "4. ✅ Session created\n";
echo "5. ✅ Redirect to exam.waiting-room (because automatic_start=true and scheduled_at > now)\n";