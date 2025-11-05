<?php

/**
 * DEBUG HASH LOOKUP
 * Test why some hashes are found and others arent in database queries
 */

echo "=== DEBUGGING HASH LOOKUP ===\n\n";

// Connect to database directly using production credentials
$host = "107.155.75.50";
$port = "5986";
$dbname = "ionbec-new";
$username = "postgres";
$password = "6LP0Ojegy7IUU6kaX9lLkmZRUiAdAUNOltWyL3LegfYGR6rPQtB4DUSVqjdA78ES";

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✅ Database connected successfully\n\n";

    // Test both working and failing hashes from recent logs
    $testHashes = [
        // Working hashes from logs
        "3ZBYv4k0" => ["title" => "BE 051125 - MCQ 11", "status" => "working"],
        "53gDGMky" => ["title" => "BE 051125 - MCQ 9 & 10", "status" => "working"],
        "xDkVA1BX" => ["title" => "BE 051125 - MCQ 40", "status" => "working"],
        "3oKMJAB6" => ["title" => "BE 051125 - MCQ 39", "status" => "working"],
        "RxBWnZKr" => ["title" => "BE 051125 - MCQ 38", "status" => "working"],

        // Failing hashes from logs
        "0Qk8Pqko" => ["title" => "BE 051125 - MCQ 30", "status" => "failing"],
        "lAKjyXg9" => ["title" => "BE 051125 - MCQ 42", "status" => "failing"],
        "qZgleXK5" => ["title" => "BE 051125 - MCQ 5", "status" => "failing"],
        "DxKJDpkq" => ["title" => "BE 051125 - MCQ 13", "status" => "failing"],
        "MlK1PYgN" => ["title" => "BE 051125 - MCQ 14", "status" => "failing"],
    ];

    echo "=== TESTING DIRECT DATABASE LOOKUPS ===\n";

    foreach ($testHashes as $hash => $info) {
        echo "\nTesting hash:  ({$info[title]}) [{$info[status]}]\n";

        // Test 1: Simple hash lookup
        $stmt = $pdo->prepare("SELECT id, title, hash FROM items WHERE hash = ? LIMIT 1");
        $stmt->execute([$hash]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            echo "  ✅ Direct lookup: FOUND - ID: {$item[id]}, Title: {[title]}\n";
        } else {
            echo "  ❌ Direct lookup: NOT FOUND\n";
        }
    }

    // Test 2: Check the ClientScope that might be affecting queries
    echo "\n=== TESTING CLIENT SCOPE EFFECTS ===\n";

    // Check client_id for items that work vs dont work
    foreach ($testHashes as $hash => $info) {
        $stmt = $pdo->prepare("SELECT id, title, hash, client_id FROM items WHERE hash = ? LIMIT 1");
        $stmt->execute([$hash]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            echo "Hash  -> client_id: {$item[client_id]}\n";
        }
    }

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== DEBUG HASH LOOKUP COMPLETE ===\n";
