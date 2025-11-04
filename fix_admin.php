<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

// Direct database update using raw SQL
use Illuminate\Support\Facades\DB;

try {
    $result = DB::update('UPDATE users SET is_admin = true, admin_role = \'super_admin\' WHERE email = ?', ['admin@localhost.com']);

    echo "Updated {$result} user(s) with admin@localhost.com\n";

    // Verify the update
    $user = DB::select('SELECT id, name, email, is_admin, admin_role FROM users WHERE email = ?', ['admin@localhost.com']);

    if (!empty($user)) {
        $user = $user[0];
        echo "Verification:\n";
        echo "ID: {$user->id}\n";
        echo "Name: {$user->name}\n";
        echo "Email: {$user->email}\n";
        echo "Is Admin: " . ($user->is_admin ? 'true' : 'false') . "\n";
        echo "Admin Role: " . ($user->admin_role ?? 'NULL') . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}