<?php

/**
 * FIX REMAINING FAILING HASHES
 * Fix all the hashes currently causing "Item not found" errors in real-time logs
 */

echo "=== FIXING REMAINING FAILING HASHES ===\n\n";

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

    // Hashes currently failing in real-time logs
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
    ];

    echo "=== SYNCING " . count($failingHashes) . " FAILING HASHES ===\n";
    $updatedCount = 0;

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
            }
        } else {
            echo "  ❌ Item not found with exact title match\n";

            // Try pattern matching for variations
            if (strpos($title, 'MCQ 30') !== false) {
                $searchPattern = '%BE 051125%MCQ 30%';
            } elseif (strpos($title, 'MCQ 42') !== false) {
                $searchPattern = '%BE 051125%MCQ 42%';
            } elseif (strpos($title, 'MCQ 5') !== false) {
                $searchPattern = '%BE 051125%MCQ 5%';
            } elseif (strpos($title, 'MCQ 13') !== false) {
                $searchPattern = '%BE 051125%MCQ 13%';
            } elseif (strpos($title, 'MCQ 14') !== false) {
                $searchPattern = '%BE 051125%MCQ 14%';
            } elseif (strpos($title, 'MCQ 60') !== false) {
                $searchPattern = '%BE 051125%MCQ 60%';
            } elseif (strpos($title, 'MCQ 28') !== false) {
                $searchPattern = '%BE 051125%MCQ 28%';
            } elseif (strpos($title, 'MCQ 33') !== false) {
                $searchPattern = '%BE 051125%MCQ 33%';
            } elseif (strpos($title, 'MCQ 53') !== false) {
                $searchPattern = '%BE 051125%MCQ 53%';
            } else {
                $searchPattern = '%' . preg_replace('/\s*\d+.*$/', '', $title) . '%';
            }

            $stmt = $pdo->prepare("SELECT id, title, hash FROM items WHERE title LIKE ? ORDER BY id LIMIT 3");
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
        foreach ($failingHashes as $rustHash => $title) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM items WHERE hash = ?");
            $stmt->execute([$rustHash]);
            $exists = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            echo "Hash '$rustHash': " . ($exists > 0 ? '✅ EXISTS' : '❌ MISSING') . "\n";
        }
    }

    // Test that the items are now found
    echo "\n=== TESTING ITEM LOOKUP ===\n";
    foreach ($failingHashes as $rustHash => $title) {
        $stmt = $pdo->prepare("SELECT id, title FROM items WHERE hash = ? LIMIT 1");
        $stmt->execute([$rustHash]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($item) {
            echo "✅ Hash '$rustHash' -> Found: ID {$item['id']} '{$item['title']}'\n";
        } else {
            echo "❌ Hash '$rustHash' -> NOT FOUND\n";
        }
    }

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== FIXING REMAINING FAILING HASHES COMPLETE ===\n";