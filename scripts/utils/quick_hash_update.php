<?php

/**
 * QUICK HASH UPDATE FOR NEW FAILING HASHES
 * Update newly discovered failing hashes from real-time logs
 */

echo "=== QUICK HASH UPDATE FOR NEW FAILING HASHES ===\n\n";

// Connect to database directly using production credentials
$host = '107.155.75.50';
$port = '5986';
$dbname = 'ionbec-new';
$username = 'postgres';
$password = '6LP0Ojegy7IUU6kaX9lLkmZRUiAdAUNOltWyL3LegfYGR6rPQtB4DUSVqjdA78ES';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✅ Database connected successfully\n\n";

    // New failing hashes from recent logs
    $newFailingHashes = [
        'VbBa9OBw' => 'BE 051125 - MCQ 36',
        'qzKolGBD' => 'BE 051125 - MCQ 45',
        'XGB3PGk6' => 'BE 051125 - MCQ 22',
        'DxKJEpgq' => 'BE 051125 - MCQ 58',
        'vAKzzbKj' => 'BE 051125 - MCQ 17',
        'DjBrExK2' => 'BE 051125 - MCQ 43',
        // Add more as we discover them from real-time monitoring
    ];

    echo "=== UPDATING NEW FAILING HASHES ===\n";
    echo "Found " . count($newFailingHashes) . " new failing hashes to update\n\n";

    $updatedCount = 0;

    foreach ($newFailingHashes as $rustHash => $title) {
        echo "Processing: '$title' -> '$rustHash'\n";

        // Find the item in database by title
        $stmt = $pdo->prepare("SELECT id, title, hash FROM items WHERE title = ? LIMIT 1");
        $stmt->execute([$title]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            echo "  ✅ Found item ID: {$item['id']}, Current hash: '{$item['hash']}'\n";

            if ($item['hash'] !== $rustHash) {
                // Update the hash to match Rust API
                $updateStmt = $pdo->prepare("UPDATE items SET hash = ?, updated_at = NOW() WHERE id = ?");
                $updateStmt->execute([$rustHash, $item['id']]);

                echo "  🔄 Updated hash to: '$rustHash'\n";
                $updatedCount++;
            } else {
                echo "  ⏭️  Hash already matches, no update needed\n";
            }
        } else {
            echo "  ❌ Item not found with exact title match\n";
        }
        echo "\n";
    }

    echo "=== UPDATE SUMMARY ===\n";
    echo "Hashes updated: $updatedCount\n\n";

    // Verify the update
    if ($updatedCount > 0) {
        echo "=== VERIFICATION ===\n";
        foreach ($newFailingHashes as $rustHash => $title) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM items WHERE hash = ?");
            $stmt->execute([$rustHash]);
            $exists = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            echo "Hash '$rustHash': " . ($exists > 0 ? '✅ EXISTS' : '❌ MISSING') . "\n";
        }
    }

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== QUICK HASH UPDATE COMPLETE ===\n";