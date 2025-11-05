<?php

/**
 * SIMPLE CRITICAL FIX - Fix prK2Pjkn hash immediately
 * Quick fix for the failing hash causing 404 errors
 */

echo "=== SIMPLE CRITICAL FIX - prK2Pjkn ===\n\n";

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

    // Fix the failing hash prK2Pjkn
    $criticalHashes = [
        'prK2Pjkn' => 'BE 051125 - MCQ 4',
    ];

    echo "=== SYNCING CRITICAL HASH ===\n";
    $updatedCount = 0;

    foreach ($criticalHashes as $rustHash => $title) {
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

            // Try pattern search
            $searchPattern = '%BE 051125%MCQ 4%';
            $stmt = $pdo->prepare("SELECT id, title, hash FROM items WHERE title LIKE ? ORDER BY id LIMIT 3");
            $stmt->execute([$searchPattern]);
            $similarItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($similarItems) > 0) {
                echo "  🔍 Found similar items:\n";
                foreach ($similarItems as $similar) {
                    echo "    - ID: {$similar['id']}, Title: '{$similar['title']}', Hash: '{$similar['hash']}'\n";
                }

                // Update the first match
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
        foreach ($criticalHashes as $rustHash => $title) {
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

echo "\n=== SIMPLE CRITICAL FIX COMPLETE ===\n";