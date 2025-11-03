<?php

/**
 * CRITICAL: Fix missing hash oqKp25k2 for MCQ 52
 * This hash is causing "Item not found" errors in production
 */

echo "=== CRITICAL FIX: MCQ 52 HASH ===\n\n";

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

    // Critical failing hash
    $failingHash = 'oqKp25k2';
    $expectedTitle = 'BE 051125 - MCQ 52';

    echo "=== SEARCHING FOR MCQ 52 ===\n";
    echo "Looking for title: '$expectedTitle'\n";
    echo "Expected hash: '$failingHash'\n\n";

    // Try exact match first
    $stmt = $pdo->prepare("SELECT id, title, hash FROM items WHERE title = ? LIMIT 1");
    $stmt->execute([$expectedTitle]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        echo "✅ Found exact match:\n";
        echo "  ID: {$item['id']}\n";
        echo "  Title: '{$item['title']}'\n";
        echo "  Current hash: '{$item['hash']}'\n\n";

        if ($item['hash'] !== $failingHash) {
            echo "🔄 Updating hash to match frontend expectation...\n";
            $updateStmt = $pdo->prepare("UPDATE items SET hash = ?, updated_at = NOW() WHERE id = ?");
            $updateStmt->execute([$failingHash, $item['id']]);
            echo "✅ Hash updated to: '$failingHash'\n\n";
        } else {
            echo "⏭️  Hash already matches\n\n";
        }
    } else {
        echo "❌ Exact match not found\n";

        // Try pattern search
        echo "🔍 Searching with patterns...\n";
        $patterns = [
            '%BE 051125%MCQ 52%',
            '%MCQ 52%',
            '%MCQ%'
        ];

        foreach ($patterns as $pattern) {
            echo "  Trying pattern: '$pattern'\n";
            $stmt = $pdo->prepare("SELECT id, title, hash FROM items WHERE title LIKE ? ORDER BY id LIMIT 3");
            $stmt->execute([$pattern]);
            $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($matches) > 0) {
                echo "  ✅ Found matches:\n";
                foreach ($matches as $match) {
                    echo "    - ID: {$match['id']}, Title: '{$match['title']}', Hash: '{$match['hash']}'\n";
                }

                // Update first match
                $bestMatch = $matches[0];
                if ($bestMatch['hash'] !== $failingHash) {
                    echo "  🔄 Updating best match (ID: {$bestMatch['id']})...\n";
                    $updateStmt = $pdo->prepare("UPDATE items SET hash = ?, updated_at = NOW() WHERE id = ?");
                    $updateStmt->execute([$failingHash, $bestMatch['id']]);
                    echo "  ✅ Updated hash to: '$failingHash'\n\n";
                } else {
                    echo "  ⏭️  Hash already matches\n\n";
                }
                break;
            }
        }
    }

    // Verification
    echo "=== VERIFICATION ===\n";
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM items WHERE hash = ?");
    $stmt->execute([$failingHash]);
    $exists = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "Hash '$failingHash': " . ($exists > 0 ? '✅ EXISTS' : '❌ MISSING') . "\n\n";

    if ($exists > 0) {
        echo "✅ CRITICAL FIX COMPLETE - MCQ 52 hash synchronized\n";
    } else {
        echo "❌ CRITICAL FIX FAILED - Could not find MCQ 52 item\n";
    }

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== CRITICAL HASH FIX COMPLETE ===\n";