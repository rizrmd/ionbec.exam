<?php

// Test beberapa kemungkinan UUID untuk attachment
$testIds = [
    '5f74bb4e-0aeb-478a-80a7-178f3522907a', // Known working
    '7f8e9a0b-1c2d-3e4f-5a6b-7c8d9e0f1a2b', // Random
    'a1b2c3d4-e5f6-7a8b-9c0d-1e2f3a4b5c6d', // Random
];

echo "=== TESTING MULTIPLE IMAGE URLs ===\n\n";

foreach ($testIds as $id) {
    $url = "https://ionbec.com/attachment/stream/{$id}";
    echo "Testing: {$id}\n";
    echo "URL: {$url}\n";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    echo "HTTP Status: {$httpCode}\n";
    echo "Content-Type: {$contentType}\n";

    if ($httpCode == 200) {
        echo "✅ SUCCESS: Image accessible\n";
    } elseif ($httpCode == 404) {
        echo "⚠️  NOT FOUND: Image doesn't exist\n";
    } else {
        echo "❌ ERROR: HTTP {$httpCode}\n";
    }

    echo str_repeat("-", 50) . "\n";
}

echo "\n=== CHECKING EXISTING ATTACHMENTS IN DATABASE ===\n";

// Coba query ke database untuk mendapatkan beberapa attachment ID yang valid
$ch = curl_init('https://ionbec.com/back-office/question-set/YRgyGB68');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Back-office page HTTP Status: {$httpCode}\n";

if ($httpCode == 200) {
    echo "✅ Back-office accessible - check if images appear correctly\n";
} else {
    echo "❌ Back-office not accessible\n";
}

echo "\n=== DONE ===\n";