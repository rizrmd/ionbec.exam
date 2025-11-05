<?php

/**
 * FINAL HASH SYNC - Complete remaining problematic hashes
 * Sync any remaining hashes causing issues in production logs
 */

echo "=== FINAL HASH SYNC - COMPLETE REMAINING HASHES ===\n\n";

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

    // All hashes seen in recent logs that might need syncing
    $remainingHashes = [
        '3oKMJAB6' => 'BE 051125 - MCQ 39',
        'qZglyXK5' => 'BE 051125 - MCQ 51',  // Note: different from qZgleXK5
        'VxgO77g3' => 'BE 051125 - MCQ 44',
        'J8KQzZBW' => 'BE 051125 - MCQ 18',
        'dVg6X0Bp' => 'BE 051125 - MCQ 57',
        '53gDEJKy' => 'BE 051125 - MCQ 68 / BE12018-67',
        'DxKJG4Bq' => 'BE 051125 - MCQ 70 / BE18718-UI9',
        'DxKJEpgq' => 'BE 051125 - MCQ 58',
    ];

    echo "=== SYNCING " . count($remainingHashes) . " REMAINING HASHES ===\n";
    $updatedCount = 0;
    $notFoundCount = 0;
    $alreadySyncedCount = 0;

    foreach ($remainingHashes as $rustHash => $title) {
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
            $notFoundCount++;

            // Try pattern matching for titles with variations
            if (strpos($title, 'MCQ 39') !== false) {
                $searchPattern = '%BE 051125%MCQ 39%';
            } elseif (strpos($title, 'MCQ 51') !== false) {
                $searchPattern = '%BE 051125%MCQ 51%';
            } elseif (strpos($title, 'MCQ 44') !== false) {
                $searchPattern = '%BE 051125%MCQ 44%';
            } elseif (strpos($title, 'MCQ 18') !== false) {
                $searchPattern = '%BE 051125%MCQ 18%';
            } elseif (strpos($title, 'MCQ 57') !== false) {
                $searchPattern = '%BE 051125%MCQ 57%';
            } elseif (strpos($title, 'MCQ 68') !== false) {
                $searchPattern = '%BE 051125%MCQ 68%';
            } elseif (strpos($title, 'MCQ 70') !== false) {
                $searchPattern = '%BE 051125%MCQ 70%';
            } elseif (strpos($title, 'MCQ 58') !== false) {
                $searchPattern = '%BE 051125%MCQ 58%';
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
            }
        }
        echo "\n";
    }

    // Verification
    echo "=== VERIFICATION ===\n";
    echo "Items updated: $updatedCount\n";
    echo "Items already synced: $alreadySyncedCount\n";
    echo "Items not found: $notFoundCount\n\n";

    if ($updatedCount > 0) {
        echo "=== TESTING UPDATED HASHES ===\n";
        foreach ($remainingHashes as $rustHash => $title) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM items WHERE hash = ?");
            $stmt->execute([$rustHash]);
            $exists = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            echo "Hash '$rustHash': " . ($exists > 0 ? '✅ EXISTS' : '❌ MISSING') . "\n";
        }
    }

    // Final comprehensive check for any BE 051125 items with old hash patterns
    echo "\n=== FINAL COMPREHENSIVE CHECK ===\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM items WHERE title LIKE 'BE 051125%'");
    $totalBE = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $stmt = $pdo->query("SELECT COUNT(*) as synced FROM items WHERE title LIKE 'BE 051125%' AND hash SIMILAR TO '[a-zA-Z0-9]{8}'");
    $syncedBE = $stmt->fetch(PDO::FETCH_ASSOC)['synced'];

    echo "Total BE 051125 items: $totalBE\n";
    echo "BE 051125 items with proper 8-char alphanumeric hashes: $syncedBE\n";
    echo "Coverage: " . round(($syncedBE / $totalBE) * 100, 1) . "%\n";

    if ($syncedBE < $totalBE) {
        echo "\n⚠️  There are still " . ($totalBE - $syncedBE) . " BE 051125 items that may need hash updates\n";
    } else {
        echo "\n✅ All BE 051125 items appear to have proper hashes!\n";
    }

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== FINAL HASH SYNC COMPLETE ===\n";