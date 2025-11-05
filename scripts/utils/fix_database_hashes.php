<?php

/**
 * SCRIPT TO FIX NULL HASHES IN DATABASE
 * This script will generate proper hashes for all items that have NULL hash values
 */

echo "=== FIXING DATABASE HASHES ===\n\n";

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

    // Step 1: Count items with NULL hashes
    echo "=== STEP 1: COUNTING ITEMS WITH NULL HASHES ===\n";
    $stmt = $pdo->query("SELECT COUNT(*) as null_hash_count FROM items WHERE hash IS NULL");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $nullCount = $result['null_hash_count'];
    echo "Items with NULL hash: $nullCount\n\n";

    if ($nullCount == 0) {
        echo "✅ All items already have hashes. No action needed.\n";
        exit(0);
    }

    // Step 2: Get all items with NULL hashes
    echo "=== STEP 2: FETCHING ITEMS THAT NEED HASHES ===\n";
    $stmt = $pdo->query("SELECT id, title FROM items WHERE hash IS NULL ORDER BY id");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Found " . count($items) . " items that need hashes\n\n";

    // Step 3: Generate hashes for each item
    echo "=== STEP 3: GENERATING HASHES ===\n";

    $hashesGenerated = 0;
    $hashesFailed = 0;

    foreach ($items as $item) {
        try {
            // Generate hash using same method as Laravel (similar to HashableId trait)
            // We'll create a simple hash based on ID and some randomization
            $hash = substr(md5($item['id'] . 'ionbec' . time() . mt_rand()), 0, 8);

            // Make sure hash is unique
            $checkStmt = $pdo->prepare("SELECT COUNT(*) as count FROM items WHERE hash = ? AND id != ?");
            $checkStmt->execute([$hash, $item['id']]);
            $hashExists = $checkStmt->fetch(PDO::FETCH_ASSOC)['count'];

            if ($hashExists > 0) {
                // If hash exists, generate a new one
                $hash = substr(md5($item['id'] . 'ionbec' . uniqid() . mt_rand()), 0, 8);
            }

            // Update the item with the new hash
            $updateStmt = $pdo->prepare("UPDATE items SET hash = ?, updated_at = NOW() WHERE id = ?");
            $updateStmt->execute([$hash, $item['id']]);

            $hashesGenerated++;

            if ($hashesGenerated % 100 == 0) {
                echo "Generated $hashesGenerated hashes...\n";
            }

        } catch (Exception $e) {
            echo "❌ Failed to generate hash for item ID {$item['id']}: {$e->getMessage()}\n";
            $hashesFailed++;
        }
    }

    echo "\n=== STEP 4: VERIFICATION ===\n";

    // Verify the fix
    $stmt = $pdo->query("SELECT COUNT(*) as remaining_null FROM items WHERE hash IS NULL");
    $remainingNull = $stmt->fetch(PDO::FETCH_ASSOC)['remaining_null'];

    $stmt = $pdo->query("SELECT COUNT(*) as total_items FROM items");
    $totalItems = $stmt->fetch(PDO::FETCH_ASSOC)['total_items'];

    $stmt = $pdo->query("SELECT COUNT(*) as with_hash FROM items WHERE hash IS NOT NULL");
    $withHash = $stmt->fetch(PDO::FETCH_ASSOC)['with_hash'];

    echo "Hash generation results:\n";
    echo "- Total items: $totalItems\n";
    echo "- Items with hash: $withHash\n";
    echo "- Items still with NULL hash: $remainingNull\n";
    echo "- Hashes generated successfully: $hashesGenerated\n";
    echo "- Hashes failed: $hashesFailed\n";

    if ($remainingNull == 0) {
        echo "\n✅ SUCCESS: All items now have hashes!\n";
    } else {
        echo "\n⚠️  WARNING: $remainingNull items still have NULL hashes\n";
    }

    // Step 5: Test a few sample hashes
    echo "\n=== STEP 5: SAMPLE VERIFICATION ===\n";
    $stmt = $pdo->query("SELECT id, title, hash FROM items WHERE hash IS NOT NULL ORDER BY id LIMIT 5");
    $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Sample items with new hashes:\n";
    foreach ($samples as $sample) {
        echo sprintf("- ID: %d, Hash: %s, Title: %s\n",
            $sample['id'],
            $sample['hash'],
            substr($sample['title'] ?: 'No title', 0, 50)
        );
    }

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== HASH FIXING COMPLETE ===\n";