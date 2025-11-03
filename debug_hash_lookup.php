<?php

/**
 * Debug script untuk memeriksa hash lookup issue
 */

echo "=== DEBUG HASH LOOKUP ISSUE ===\n\n";

// Test hash yang bermasalah dari logs
$problemHashes = [
    'Adkn2GkR',  // BE 051125 - MCQ 24
    'DxKJG4Bq',  // BE 051125 - MCQ 70 / BE18718-UI9
    'VbBa9OBw',  // BE 051125 - MCQ 36
    'dVg6P0Bp',  // BE 051125 - MCQ 12
];

foreach ($problemHashes as $hash) {
    echo "Testing hash: $hash\n";

    // Build URL untuk testing API
    $url = "https://ionbec.com/exam/questions/$hash";

    // Test dengan curl (bypass SSL untuk testing)
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    // Add cookies untuk session (dummy session)
    curl_setopt($ch, CURLOPT_COOKIE, 'exam_session=test');

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

        if ($httpCode === 302) {
            echo "Redirected to login (expected for non-auth session)\n";
        } elseif ($httpCode === 200) {
            echo "Success! Got response\n";
            $jsonData = json_decode($body, true);
            if ($jsonData && isset($jsonData['questions'])) {
                echo "Questions found: " . (is_array($jsonData['questions']) ? count($jsonData['questions']) : 'not an array') . "\n";
            }
        } elseif ($httpCode === 404) {
            echo "Item not found (404)\n";
        } else {
            echo "Unexpected response code: $httpCode\n";
            echo "Response: " . substr($body, 0, 200) . "...\n";
        }
    }

    echo "----------------------------------------\n\n";
}

echo "Hash lookup test completed.\n";