<?php

// List of scorer names
$scorers = [
    'Prof. Dr. dr. Dwikora November Utomo, SpOT(K)',
    'dr. Teddy Heri Wardhana, SpOT(K)',
    'dr. Istan Irmansyah, SpOT(K)',
    'Dr. dr. Muhammad Sakti, SpOT(K)',
    'Dr. dr. Mouli Edward, SpOT(K)',
    'dr. Syaifullah Asmiragani, SpOT(K)',
    'Dr. dr. Mujaddid Idulhaq, SpOT(K)',
    'Dr. dr. Ihsan Oesman, SpOT(K)',
    'Dr. dr. R. Andri Primadhi, SpOT(K)',
    'Dr. dr. Yudha Mathan Sakti, SpOT(K)',
    'dr. Pranajaya Dharma Kadar, SpOT(K)',
    'Dr. dr. Rieva Ermawan, SpOT(K)',
    'Dr. dr. I Gusti Ngurah Wien Aryana, SpOT(K)',
    'Dr. dr. Krisna Yuarno Phatama, SpOT(K)',
    'Dr. dr. Rendra Leonas, SpOT(K)',
    'Dr. dr. Roni Eko Sahputra, SpOT(K)',
    'Prof. Dr. dr. Azharuddin, SpOT(K)'
];

echo "Processing scorer users...\n\n";

// Function to generate username from name
function generateUsername($name) {
    // Extract first name or create short username
    $name = trim($name);

    // Handle special cases
    if (strpos($name, 'Prof.') !== false || strpos($name, 'Dr.') !== false) {
        // Remove titles
        $name = preg_replace('/^(Prof\.?\s*|Dr\.?\s*)+/', '', $name);
    }

    // Get first word
    $parts = explode(' ', $name);
    $firstName = strtolower($parts[0]);

    // Remove special characters
    $username = preg_replace('/[^a-z0-9]/', '', $firstName);

    // If empty or just 'dr', use actual first name
    if (empty($username) || $username === 'dr') {
        // Find first meaningful word (not 'dr' or 'prof')
        $meaningfulWords = ['dwikora', 'teddy', 'istan', 'muhammad', 'mouli', 'syaifullah', 'mujaddid', 'ihsan', 'andri', 'yudha', 'pranajaya', 'rieva', 'krisna', 'rendra', 'roni', 'azharuddin', 'gusti', 'ngurah', 'wien', 'aryana'];

        foreach ($meaningfulWords as $word) {
            if (stripos($name, $word) !== false) {
                $username = $word;
                break;
            }
        }

        // Fallback
        if (empty($username) || $username === 'dr') {
            $username = 'user' . rand(100, 999);
        }
    }

    return $username . '@ionbec.com';
}

// Function to generate password
function generatePassword($name) {
    // Extract first meaningful word for password
    $meaningfulWords = ['Dwikora', 'Teddy', 'Istan', 'Muhammad', 'Mouli', 'Syaifullah', 'Mujaddid', 'Ihsan', 'Andri', 'Yudha', 'Pranajaya', 'Rieva', 'Krisna', 'Rendra', 'Roni', 'Azharuddin', 'Gusti', 'Wien'];

    $name = trim($name);

    foreach ($meaningfulWords as $word) {
        if (stripos($name, $word) !== false) {
            return strtolower($word) . '2025';
        }
    }

    // Fallback to first word
    $parts = explode(' ', $name);
    $firstName = strtolower($parts[0]);
    $passwordBase = preg_replace('/[^a-zA-Z]/', '', $firstName);

    return $passwordBase . '2025';
}

$results = [];
$client_id = 3; // Based on the URL parameter from earlier

foreach ($scorers as $index => $scorerName) {
    $username = generateUsername($scorerName);
    $password = generatePassword($scorerName);
    $email = $username;

    echo sprintf("%d. %s\n", $index + 1, $scorerName);
    echo sprintf("   Username: %s\n", $username);
    echo sprintf("   Email: %s\n", $email);
    echo sprintf("   Password: %s\n", $password);
    echo "\n";

    $results[] = [
        'name' => $scorerName,
        'username' => $username,
        'email' => $email,
        'password' => $password
    ];
}

// Save results to file
$output = "Nama,Username,Email,Password\n";
foreach ($results as $result) {
    $output .= sprintf('"%s",%s,%s,%s',
        $result['name'],
        $result['username'],
        $result['email'],
        $result['password']
    ) . "\n";
}

file_put_contents('scorer_credentials.csv', $output);
echo "Credentials saved to scorer_credentials.csv\n";

// Display summary
echo "\n=== SUMMARY ===\n";
echo "Total users processed: " . count($results) . "\n";
echo "Username pattern: firstname@ionbec.com\n";
echo "Password pattern: firstname2025\n";
echo "\nAll users need to be created/updated in the database with role 'scorer'\n";

?>