<?php

/**
 * CRITICAL INVESTIGATION: Hash Database vs Rust API Comparison
 *
 * Comparing hashes that are failing in PHP but exist in Rust API
 */

echo "=== HASH COMPARISON INVESTIGATION ===\n\n";

// Connect to database
$host = '107.155.75.50';
$port = '5986';
$dbname = 'ionbec-new';
$username = 'postgres';
$password = '6LP0Ojegy7IUU6kaX9lLkmZRUiAdAUNOltWyL3LegfYGR6rPQtB4DUSVqjdA78ES';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✅ Database connected\n\n";

    // Hashes from production logs that are failing
    $failingHashes = [
        '0Qk8Pqko', // MCQ 30 - Item not found
        'lAKjyXg9', // MCQ 42 - Item not found
        'qZgleXK5', // MCQ 5  - Item not found
        'DxKJDpkq', // MCQ 13 - Item not found
        'MlK1PYgN', // MCQ 14 - Item not found
        'nJg75GBl', // MCQ 60 - Item not found
        '7pkZxXK9', // MCQ 28 - Item not found
        '6yk5PWBb', // MCQ 33 - Item not found
        'z8Kw68go', // MCQ 53 - Item not found
    ];

    // Hashes from production logs that are working
    $workingHashes = [
        '3ZBYv4k0', // MCQ 11 - Item found
        '53gDGMky', // MCQ 9 & 10 - Item found
        'xDkVA1BX', // MCQ 40 - Item found
        '3oKMJAB6', // MCQ 39 - Item found
        'RxBWnZKr', // MCQ 38 - Item found
    ];

    echo "=== CHECKING FAILING HASHES ===\n";
    foreach ($failingHashes as $hash) {
        $stmt = $pdo->prepare("SELECT id, title, hash FROM items WHERE hash = ?");
        $stmt->execute([$hash]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            echo "✅ FOUND: $hash - {$item['title']} (ID: {$item['id']})\n";
        } else {
            echo "❌ MISSING: $hash - Not found in database\n";
        }
    }

    echo "\n=== CHECKING WORKING HASHES ===\n";
    foreach ($workingHashes as $hash) {
        $stmt = $pdo->prepare("SELECT id, title, hash FROM items WHERE hash = ?");
        $stmt->execute([$hash]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            echo "✅ FOUND: $hash - {$item['title']} (ID: {$item['id']})\n";
        } else {
            echo "❌ MISSING: $hash - Not found in database\n";
        }
    }

    echo "\n=== RUST API HASHES ===\n";
    // Test the Rust API to get the actual hashes it's returning
    $rustUrl = 'http://rust-service:3000/api/exam/load';
    $postData = json_encode([
        'exam_id' => 74,
        'delivery_id' => 21
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $rustUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        if ($data['success'] && isset($data['items'])) {
            echo "✅ Rust API returned " . count($data['items']) . " items\n";
            echo "First 5 Rust API hashes:\n";
            for ($i = 0; $i < min(5, count($data['items'])); $i++) {
                $rustHash = $data['items'][$i]['hash'] ?? 'N/A';
                $rustTitle = $data['items'][$i]['name'] ?? 'N/A';
                echo "  - $rustHash: $rustTitle\n";
            }
        }
    } else {
        echo "❌ Rust API failed: HTTP $httpCode\n";
    }

    echo "\n=== ROOT CAUSE ANALYSIS ===\n";
    echo "1. Check if failing hashes exist in database at all\n";
    echo "2. Compare Rust API hashes vs database hashes\n";
    echo "3. Identify data synchronization issues\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== INVESTIGATION COMPLETE ===\n";