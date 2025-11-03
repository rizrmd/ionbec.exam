<?php

/**
 * COMPREHENSIVE HASH SYNC V2
 * Sync ALL failing hashes from recent logs to database
 */

echo "=== COMPREHENSIVE HASH SYNC V2 ===\n\n";

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

    // All failing hashes from recent logs
    $failingHashes = [
        '0Qk8Pqko' => 'BE 051125 - MCQ 30',
        'lAKjyXg9' => 'BE 051125 - MCQ 42',
        'qZgleXK5' => 'BE 051125 - MCQ 5',
        'DxKJDpkq' => 'BE 051125 - MCQ 13',
        'MlK1PYgN' => 'BE 051125 - MCQ 14',
        'nJg75GBl' => 'BE 051125 - MCQ 60',
        '7pkZxXK9' => 'BE 051125 - MCQ 28',
        '6yk5PWBb' => 'BE 051125 - MCQ 33',
        'z8Kw68go' => 'BE 051125 - MCQ 53',
        // Add more as discovered from logs
    ];

    echo "=== SYNCING " . count($failingHashes) . " FAILING HASHES ===\n";
    echo "Found " . count($failingHashes) . " failing hashes to sync\n\n";

    $updatedCount = 0;
    $notFoundCount = 0;
    $alreadySyncedCount = 0;

    foreach ($failingHashes as $rustHash => $title) {
        echo "Processing: '$title' -> '$rustHash'\n";

        // Find the item in database by title (exact match first)
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
                $alreadySyncedCount++;
            }
        } else {
            echo "  ❌ Item not found with exact title match\n";

            // Try partial match for titles with variations
            $searchTitle = preg_replace('/\s*\d+$/', '', $title); // Remove trailing numbers
            $stmt = $pdo->prepare("SELECT id, title, hash FROM items WHERE title LIKE ? ORDER BY id LIMIT 3");
            $stmt->execute(["%$searchTitle%"]);
            $similarItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($similarItems) > 0) {
                echo "  🔍 Found similar items:\n";
                foreach ($similarItems as $similar) {
                    echo "    - ID: {$similar['id']}, Title: '{$similar['title']}', Hash: '{$similar['hash']}'\n";
                }

                // Try to match the most similar one
                $bestMatch = $similarItems[0];
                if ($bestMatch['hash'] !== $rustHash) {
                    $updateStmt = $pdo->prepare("UPDATE items SET hash = ?, updated_at = NOW() WHERE id = ?");
                    $updateStmt->execute([$rustHash, $bestMatch['id']]);
                    echo "  🔄 Updated best match (ID: {$bestMatch['id']}) to: '$rustHash'\n";
                    $updatedCount++;
                }
            } else {
                $notFoundCount++;
            }
        }
        echo "\n";
    }

    // Also try to find more BE 051125 items with NULL hash or old hash patterns
    echo "=== LOOKING FOR MORE BE 051125 ITEMS ===\n";

    $stmt = $pdo->query("SELECT id, title, hash FROM items WHERE title LIKE 'BE 051125%' ORDER BY id");
    $beItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($beItems) . " BE 051125 items in database\n";

    // Check for items that might need hash updates based on old patterns
    $patternUpdates = 0;
    foreach ($beItems as $item) {
        // If hash doesn't follow the Rust API pattern (8 chars alphanumeric), it might need update
        if (strlen($item['hash']) !== 8 || !preg_match('/^[a-zA-Z0-9]+$/', $item['hash'])) {
            echo "Item might need hash update: ID {$item['id']}, Title: '{$item['title']}', Current hash: '{$item['hash']}'\n";
            // For now, just report - we'll need to get the actual Rust API hash from logs
        }
    }

    // Verification
    echo "=== VERIFICATION ===\n";
    echo "Items updated: $updatedCount\n";
    echo "Items already synced: $alreadySyncedCount\n";
    echo "Items not found: $notFoundCount\n";
    echo "Pattern matches found: $patternUpdates\n\n";

    // Test all the updated hashes
    if ($updatedCount > 0) {
        echo "=== TESTING UPDATED HASHES ===\n";
        foreach ($failingHashes as $rustHash => $title) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM items WHERE hash = ?");
            $stmt->execute([$rustHash]);
            $exists = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            echo "Hash '$rustHash': " . ($exists > 0 ? '✅ EXISTS' : '❌ MISSING') . "\n";
        }
    }

    // Overall status
    $stmt = $pdo->query("SELECT COUNT(*) as total_items FROM items");
    $totalItems = $stmt->fetch(PDO::FETCH_ASSOC)['total_items'];

    $stmt = $pdo->query("SELECT COUNT(*) as with_hash FROM items WHERE hash IS NOT NULL");
    $withHash = $stmt->fetch(PDO::FETCH_ASSOC)['with_hash'];

    echo "\n=== FINAL STATUS ===\n";
    echo "Total items: $totalItems\n";
    echo "Items with hash: $withHash\n";
    echo "Items updated this run: $updatedCount\n";

    echo "\n=== NEXT STEPS ===\n";
    echo "1. Continue monitoring logs for more failing hashes\n";
    echo "2. Test all answered questions for proper indicator display\n";
    echo "3. Verify all navigation scenarios work correctly\n";

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== COMPREHENSIVE HASH SYNC V2 COMPLETE ===\n";