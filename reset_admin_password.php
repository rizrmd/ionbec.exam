<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// Update admin password
$newPassword = '123qwe123';
$hashedPassword = Hash::make($newPassword);

$updated = DB::table('users')
    ->where('username', 'admin')
    ->update(['password' => $hashedPassword, 'updated_at' => now()]);

if ($updated) {
    echo "✓ Admin password updated successfully!\n";
    echo "Username: admin\n";
    echo "New Password: 123qwe123\n";

    // Verify the new password works
    $user = DB::table('users')->where('username', 'admin')->first();
    if ($user && password_verify($newPassword, $user->password)) {
        echo "✓ Password verification successful\n";
    } else {
        echo "✗ Password verification failed\n";
    }
} else {
    echo "✗ Failed to update admin password\n";
    echo "Make sure the admin user exists in the database.\n";
}