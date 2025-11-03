<?php

/**
 * CHECK SPECIFIC HASHES FROM LOGS
 * Check if the hashes extracted from logs exist in the database
 */

echo "=== CHECKING SPECIFIC HASHES FROM LOGS ===\n\n";

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

    // Hashes from the logs that are failing
    $failingHashes = ['3ZBYv4k0', '3oKMJAB6', '1Ogda5Kz', 'dVg6X0Bp'];

    foreach ($failingHashes as $hash) {
        echo "=== CHECKING HASH: $hash ===\n";

        // Check if this hash exists in database
        $stmt = $pdo->prepare("SELECT id, title, hash, created_at, updated_at FROM items WHERE hash = ? LIMIT 1");
        $stmt->execute([$hash]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            echo "✅ FOUND: ID {$item['id']}, Title: {$item['title']}, Hash: {$item['hash']}\n";
            echo "   Created: {$item['created_at']}\n";
            echo "   Updated: {$item['updated_at']}\n";
        } else {
            echo "❌ NOT FOUND: Hash $hash does not exist in database\n";

            // Let's search for similar items by title
            $stmt = $pdo->prepare("SELECT id, title, hash FROM items WHERE title LIKE '%MCQ 11%' OR title LIKE '%MCQ 39%' OR title LIKE '%MCQ 47%' OR title LIKE '%MCQ 57%' ORDER BY id LIMIT 10");
            $stmt->execute();
            $similarItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($similarItems) > 0) {
                echo "   Similar items found:\n";
                foreach ($similarItems as $similar) {
                    echo "   - ID: {$similar['id']}, Title: {$similar['title']}, Hash: {$similar['hash']}\n";
                }
            }
        }
        echo "\n";
    }

    // Check overall hash status
    echo "=== OVERALL HASH STATUS ===\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total_items FROM items");
    $totalItems = $stmt->fetch(PDO::FETCH_ASSOC)['total_items'];

    $stmt = $pdo->query("SELECT COUNT(*) as with_hash FROM items WHERE hash IS NOT NULL");
    $withHash = $stmt->fetch(PDO::FETCH_ASSOC)['with_hash'];

    $stmt = $pdo->query("SELECT COUNT(*) as null_hash FROM items WHERE hash IS NULL");
    $nullHash = $stmt->fetch(PDO::FETCH_ASSOC)['null_hash'];

    echo "Total items: $totalItems\n";
    echo "Items with hash: $withHash\n";
    echo "Items with NULL hash: $nullHash\n";

    // Get some sample hashes to verify format
    echo "\n=== SAMPLE HASHES ===\n";
    $stmt = $pdo->query("SELECT id, title, hash FROM items WHERE hash IS NOT NULL ORDER BY id LIMIT 10");
    $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($samples as $sample) {
        echo "ID: {$sample['id']}, Hash: {$sample['hash']}, Title: " . substr($sample['title'], 0, 50) . "\n";
    }

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== HASH CHECKING COMPLETE ===\n";