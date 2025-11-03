<?php

/**
 * URGENT HASH FIX - Fix latest failing hash from real-time logs
 * Fix hash 4Og9PRk6 (BE 051125 - MCQ 34) that's currently failing
 */

echo "=== URGENT HASH FIX - LATEST FAILING HASH ===\n\n";

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

    // Latest failing hash from real-time logs
    $urgentHashes = [
        '4Og9PRk6' => 'BE 051125 - MCQ 34',
    ];

    echo "=== SYNCING URGENT FAILING HASH ===\n";
    $updatedCount = 0;

    foreach ($urgentHashes as $rustHash => $title) {
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
            }
        } else {
            echo "  ❌ Item not found with exact title match\n";

            // Try broader search for similar items
            $searchPattern = '%BE 051125%MCQ 34%';
            $stmt = $pdo->prepare("SELECT id, title, hash FROM items WHERE title LIKE ? ORDER BY id LIMIT 5");
            $stmt->execute([$searchPattern]);
            $similarItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($similarItems) > 0) {
                echo "  🔍 Found similar items:\n";
                foreach ($similarItems as $similar) {
                    echo "    - ID: {$similar['id']}, Title: '{$similar['title']}', Hash: '{$similar['hash']}'\n";
                }

                // Update the first match if hash is different
                $bestMatch = $similarItems[0];
                if ($bestMatch['hash'] !== $rustHash) {
                    $updateStmt = $pdo->prepare("UPDATE items SET hash = ?, updated_at = NOW() WHERE id = ?");
                    $updateStmt->execute([$rustHash, $bestMatch['id']]);
                    echo "  🔄 Updated best match (ID: {$bestMatch['id']}) to: '$rustHash'\n";
                    $updatedCount++;
                }
            } else {
                echo "  ❌ No similar items found\n";
            }
        }
        echo "\n";
    }

    // Verification
    echo "=== VERIFICATION ===\n";
    echo "Hashes updated: $updatedCount\n\n";

    if ($updatedCount > 0) {
        echo "=== TESTING UPDATED HASHES ===\n";
        foreach ($urgentHashes as $rustHash => $title) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM items WHERE hash = ?");
            $stmt->execute([$rustHash]);
            $exists = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            echo "Hash '$rustHash': " . ($exists > 0 ? '✅ EXISTS' : '❌ MISSING') . "\n";
        }
    }

    // Check for any other potentially missing hashes by looking for BE 051125 items with old hash patterns
    echo "\n=== CHECKING FOR ANY REMAINING ISSUES ===\n";
    $stmt = $pdo->query("SELECT id, title, hash FROM items WHERE title LIKE 'BE 051125%' AND LENGTH(hash) != 8 ORDER BY id LIMIT 10");
    $problematicItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($problematicItems) > 0) {
        echo "Found " . count($problematicItems) . " BE 051125 items with non-8-char hashes:\n";
        foreach ($problematicItems as $item) {
            echo "  - ID: {$item['id']}, Title: '{$item['title']}', Hash: '{$item['hash']}' (Length: " . strlen($item['hash']) . ")\n";
        }
    } else {
        echo "✅ No BE 051125 items with problematic hash lengths found\n";
    }

    // Also check for any items with hashes containing special characters
    $stmt = $pdo->query("SELECT id, title, hash FROM items WHERE title LIKE 'BE 051125%' AND hash !~ '^[a-zA-Z0-9]+$' ORDER BY id LIMIT 10");
    $specialCharItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($specialCharItems) > 0) {
        echo "\nFound " . count($specialCharItems) . " BE 051125 items with special character hashes:\n";
        foreach ($specialCharItems as $item) {
            echo "  - ID: {$item['id']}, Title: '{$item['title']}', Hash: '{$item['hash']}'\n";
        }
    } else {
        echo "\n✅ No BE 051125 items with special character hashes found\n";
    }

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== URGENT HASH FIX COMPLETE ===\n";