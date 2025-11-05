<?php

/**
 * COMPREHENSIVE HASH SYNC FROM LOGS
 * Extract all failing hashes from production logs and update database
 */

echo "=== COMPREHENSIVE HASH SYNC FROM LOGS ===\n\n";

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

    // Get recent logs to extract failing hashes
    echo "=== EXTRACTING FAILING HASHES FROM LOGS ===\n";

    // For now, manually add the hashes we've seen in logs
    // In production, this could be automated by parsing log files
    $failingHashes = [
        'x5gL6xB1' => 'BE 051125 - MCQ 35',
        '53gDGMky' => 'BE 051125 - MCQ 9 & 10',
        'xDkVA1BX' => 'BE 051125 - MCQ 40',
        'z8Kw18ko' => 'BE 051125 - MCQ 7',
        'RxBWnZKr' => 'BE 051125 - MCQ 38',
        'prK2ZjKn' => 'BE 051125 - MCQ 50',
        // Add more as we discover them
    ];

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

    // Let's also try to find patterns for other BE 051125 items
    echo "=== LOOKING FOR MORE BE 051125 ITEMS ===\n";

    $stmt = $pdo->query("SELECT id, title, hash FROM items WHERE title LIKE 'BE 051125%' ORDER BY id");
    $beItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($beItems) . " BE 051125 items in database\n";

    // Check for items that might need hash updates based on our pattern
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

    // Test some of the updated hashes
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

    // Create a list of all remaining problematic hashes for future reference
    echo "\n=== REMAINING ACTION ITEMS ===\n";
    echo "1. Monitor logs for more failing hashes\n";
    echo "2. Create automated log parsing for hash extraction\n";
    echo "3. Consider implementing real-time hash sync mechanism\n";

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== COMPREHENSIVE HASH SYNC COMPLETE ===\n";