<?php

/**
 * EMERGENCY HASH FIX FOR 2 MISSING HASHES
 * Fix the 2 critical hashes causing Item not found errors
 */

echo "=== EMERGENCY HASH FIX FOR 2 MISSING HASHES ===\n\n";

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

    // The 2 missing critical hashes from Rust API
    $missingHashes = [
        'wJkqwPKO' => 'BE 051125 - MCQ 37',
        'rxKX0xKm' => 'BE 051125 - MCQ 32',
    ];

    echo "=== SYNCING 2 MISSING CRITICAL HASHES ===\n";
    $updatedCount = 0;

    foreach ($missingHashes as $rustHash => $title) {
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
            $searchPattern = '%BE 051125%MCQ 37%';
            if (strpos($title, 'MCQ 32') !== false) {
                $searchPattern = '%BE 051125%MCQ 32%';
            }

            $stmt = $pdo->prepare("SELECT id, title, hash FROM items WHERE title LIKE ? ORDER BY id LIMIT 5");
            $stmt->execute([$searchPattern]);
            $similarItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($similarItems) > 0) {
                echo "  🔍 Found items with similar pattern:\n";
                foreach ($similarItems as $similar) {
                    echo "    - ID: {$similar['id']}, Title: '{$similar['title']}', Hash: '{$similar['hash']}'\n";
                }

                // Try to match the most similar one based on the MCQ number
                $bestMatch = null;
                foreach ($similarItems as $similar) {
                    if (strpos($similar['title'], 'MCQ 37') !== false && strpos($title, 'MCQ 37') !== false) {
                        $bestMatch = $similar;
                        break;
                    } elseif (strpos($similar['title'], 'MCQ 32') !== false && strpos($title, 'MCQ 32') !== false) {
                        $bestMatch = $similar;
                        break;
                    }
                }

                if (!$bestMatch && count($similarItems) > 0) {
                    $bestMatch = $similarItems[0]; // fallback to first
                }

                if ($bestMatch && $bestMatch['hash'] !== $rustHash) {
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
        foreach ($missingHashes as $rustHash => $title) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM items WHERE hash = ?");
            $stmt->execute([$rustHash]);
            $exists = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            echo "Hash '$rustHash': " . ($exists > 0 ? '✅ EXISTS' : '❌ MISSING') . "\n";
        }
    }

    // Check for any other potentially missing hashes by looking at recent logs
    echo "\n=== CHECKING FOR OTHER POTENTIAL ISSUES ===\n";
    $stmt = $pdo->query("SELECT id, title, hash FROM items WHERE title LIKE 'BE 051125%' AND hash NOT IN (
        '0Qk8Pqko', 'lAKjyXg9', 'qZgleXK5', 'DxKJDpkq', 'MlK1PYgN', 'nJg75GBl',
        '7pkZxXK9', '6yk5PWBb', 'z8Kw68go', '53gDGMky', 'xDkVA1BX', 'wJkqwPKO',
        'rxKX0xKm', 'qzKolGBD', 'VbBa9OBw', 'vAKzzbKj', 'DjBrExK2', 'x5gL6xB1',
        'prK2ZjKn', 'RxBWnZKr', '53gDEJKy', 'z8Kw18ko', 'DxKJEpgq'
    ) ORDER BY id LIMIT 10");

    $otherItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($otherItems) > 0) {
        echo "Other BE 051125 items that might need attention:\n";
        foreach ($otherItems as $item) {
            echo "  - ID: {$item['id']}, Title: '{$item['title']}', Hash: '{$item['hash']}'\n";
        }
    } else {
        echo "✅ All BE 051125 items appear to be synced\n";
    }

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== EMERGENCY HASH FIX COMPLETE ===\n";