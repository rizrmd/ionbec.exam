<?php

/**
 * Test script untuk debugging item hash issue
 */

echo "=== Test Item Hash Issue ===\n\n";

// Test beberapa hash yang ada di logs
$testHashes = [
    'vAKzzbKj',  // BE 051125 - MCQ 17
    'wJkqwPKO',  // BE 051125 - MCQ 37
    'VbBa9OBw',  // BE 051125 - MCQ 36
    'j4B4PQBz',  // BE 051125 - MCQ 29
    'pwBeWwgQ',  // BE 051125 - MCQ 1 & 2
    'n0BPZAgL',  // BE 051125 - MCQ 88 / BE12018-24
];

foreach ($testHashes as $hash) {
    echo "Testing hash: $hash\n";

    // Build URL untuk testing
    $url = "https://ionbec.com/exam/questions/$hash";

    echo "URL: $url\n";

    // Test dengan curl
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    echo "HTTP Code: $httpCode\n";

    if ($error) {
        echo "CURL Error: $error\n";
    } else {
        // Parse response
        $headerSize = strpos($response, "\r\n\r\n");
        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize + 4);

        echo "Response headers: " . substr($headers, 0, 200) . "...\n";

        $jsonData = json_decode($body, true);
        if ($jsonData) {
            echo "Response JSON keys: " . implode(', ', array_keys($jsonData)) . "\n";
            if (isset($jsonData['questions'])) {
                echo "Questions count: " . (is_array($jsonData['questions']) ? count($jsonData['questions']) : 'not an array') . "\n";
            }
            if (isset($jsonData['error'])) {
                echo "Error: " . $jsonData['error'] . "\n";
            }
        } else {
            echo "Response is not valid JSON\n";
            echo "Response body: " . substr($body, 0, 200) . "...\n";
        }
    }

    echo "----------------------------------------\n\n";
}

echo "Test completed.\n";