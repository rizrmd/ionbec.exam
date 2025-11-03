<?php

/**
 * CRITICAL EMERGENCY FIX - Fix all failing hashes including prK2Pjkn
 * Address all remaining hash failures causing 404/500 errors
 */

echo "=== CRITICAL EMERGENCY FIX - ALL FAILING HASHES ===\n\n";

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

    // All failing hashes from recent error logs
    $criticalHashes = [
        'prK2Pjkn' => 'BE 051125 - MCQ 4',
        // Add any other failing hashes we find
    ];

    echo "=== SYNCING CRITICAL FAILING HASHES ===\n";
    $updatedCount = 0;

    foreach ($criticalHashes as $rustHash => $title) {
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
            $searchPattern = '%BE 051125%MCQ 4%';
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
        foreach ($criticalHashes as $rustHash => $title) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM items WHERE hash = ?");
            $stmt->execute([$rustHash]);
            $exists = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            echo "Hash '$rustHash': " . ($exists > 0 ? '✅ EXISTS' : '❌ MISSING') . "\n";
        }
    }

    // Check for any BE 051125 items that might still have old hash patterns
    echo "\n=== COMPREHENSIVE BE 051125 CHECK ===\n";
    $stmt = $pdo->query("SELECT id, title, hash FROM items WHERE title LIKE 'BE 051125%' ORDER BY id LIMIT 20");
    $beItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Checking first 20 BE 051125 items for potential issues:\n";
    $problemCount = 0;
    foreach ($beItems as $item) {
        $hasIssue = false;
        $issues = [];

        // Check hash length
        if (strlen($item['hash']) !== 8) {
            $issues[] = "Length: " . strlen($item['hash']);
            $hasIssue = true;
        }

        // Check for alphanumeric only
        if (!preg_match('/^[a-zA-Z0-9]+$/', $item['hash'])) {
            $issues[] = "Special chars";
            $hasIssue = true;
        }

        // Check for common patterns that indicate old hashes
        if (preg_match('/^[a-f0-9]+$/', $item['hash']) && strlen($item['hash']) === 8) {
            $issues[] = "Hex only";
            $hasIssue = true;
        }

        if ($hasIssue) {
            echo "  ⚠️  ID: {$item['id']}, Title: '{$item['title']}', Hash: '{$item['hash']}' . (count($issues) > 0 ? " [" . implode(", ", $issues) . "]" : "") . "\n";
            $problemCount++;
        } else {
            echo "  ✅ ID: {$item['id']}, Title: '{$item['title']}', Hash: '{$item['hash']}'\n";
        }
    }

    echo "\nProblematic items found: $problemCount\n";

    if ($problemCount > 0) {
        echo "\n⚠️  There are still BE 051125 items with potentially problematic hashes\n";
        echo "These may need manual investigation and updates\n";
    } else {
        echo "\n✅ First 20 BE 051125 items look good!\n";
    }

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== CRITICAL EMERGENCY FIX COMPLETE ===\n";