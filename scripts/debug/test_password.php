<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test common passwords for the admin user
$passwordHash = '$2y$10$kLK9wnzqAQ0IIqn98gaopOgzvVqA97GqxQ3qRWeYb84aUWntKHAa6';

$commonPasswords = [
    'password',
    'admin',
    '123456',
    'admin123',
    'administrator',
    'root',
    'secret',
    '12345678',
    'qwerty',
    'letmein'
];

echo "Testing common passwords for admin user:\n";
foreach ($commonPasswords as $password) {
    if (password_verify($password, $passwordHash)) {
        echo "✓ PASSWORD FOUND: '{$password}'\n";
        exit(0);
    } else {
        echo "- '{$password}' - no match\n";
    }
}

echo "No common passwords found. You may need to reset the admin password.\n";