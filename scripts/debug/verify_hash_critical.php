<?php

/**
 * VERIFY HASH SYNTAX CRITICAL ISSUES
 * Check what's happening with the "missing" hashes
 */

echo "=== CRITICAL HASH VERIFICATION ===\n\n";

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

    // Critical hashes from recent error logs
    $criticalHashes = [
        '53gDGMky' => 'BE 051125 - MCQ 9 & 10',
        'xDkVA1BX' => 'BE 051125 - MCQ 40',
        'wJkqwPKO' => 'BE 051125 - MCQ 37',
        'rxKX0xKm' => 'BE 051125 - MCQ 32',
        'nJg75GBl' => 'BE 051125 - MCQ 60',
        'qzKolGBD' => 'BE 051125 - MCQ 45',
    ];

    echo "=== CHECKING CRITICAL HASHES ===\n";
    foreach ($criticalHashes as $hash => $expectedTitle) {
        echo "Checking hash: '$hash' (Expected: $expectedTitle)\n";

        $stmt = $pdo->prepare("SELECT id, title, hash FROM items WHERE hash = ? LIMIT 1");
        $stmt->execute([$hash]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            echo "  ✅ FOUND - ID: {$item['id']}, Title: '{$item['title']}', Hash: '{$item['hash']}'\n";
        } else {
            echo "  ❌ NOT FOUND!\n";

            // Search for similar titles
            $searchTitle = preg_replace('/\s*\d+.*$/', '', $expectedTitle); // Remove numbers and everything after
            $stmt = $pdo->prepare("SELECT id, title, hash FROM items WHERE title LIKE ? ORDER BY id LIMIT 5");
            $stmt->execute(["%$searchTitle%"]);
            $similar = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($similar) > 0) {
                echo "  🔍 Similar items found:\n";
                foreach ($similar as $sim) {
                    echo "    - ID: {$sim['id']}, Title: '{$sim['title']}', Hash: '{$sim['hash']}'\n";
                }
            } else {
                echo "  🔍 No similar items found\n";
            }
        }
        echo "\n";
    }

    // Check for potential character encoding issues or whitespace
    echo "=== CHECKING FOR HASH ANOMALIES ===\n";
    $stmt = $pdo->query("SELECT id, title, hash, LENGTH(hash) as hash_length, hash != TRIM(hash) as has_whitespace FROM items WHERE hash IS NOT NULL ORDER BY id LIMIT 10");
    $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($samples as $sample) {
        echo "ID: {$sample['id']}, Hash: '{$sample['hash']}', Length: {$sample['hash_length']}, Has Whitespace: " . ($sample['has_whitespace'] ? 'YES' : 'NO') . "\n";
    }

    // Check all BE 051125 items for hash patterns
    echo "\n=== BE 051125 HASH PATTERNS ===\n";
    $stmt = $pdo->query("SELECT id, title, hash FROM items WHERE title LIKE 'BE 051125%' ORDER BY id LIMIT 20");
    $beItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $hashPatterns = [];
    foreach ($beItems as $item) {
        $pattern = strlen($item['hash']) . '_' . (preg_match('/^[a-zA-Z0-9]+$/', $item['hash']) ? 'alphanumeric' : 'special_chars');
        if (!isset($hashPatterns[$pattern])) {
            $hashPatterns[$pattern] = [];
        }
        $hashPatterns[$pattern][] = $item;
    }

    foreach ($hashPatterns as $pattern => $items) {
        echo "Pattern $pattern: " . count($items) . " items\n";
        if (count($items) <= 3) {
            foreach ($items as $item) {
                echo "  - {$item['title']} -> '{$item['hash']}'\n";
            }
        } else {
            echo "  - (showing first 3 of " . count($items) . ")\n";
            for ($i = 0; $i < 3; $i++) {
                echo "  - {$items[$i]['title']} -> '{$items[$i]['hash']}'\n";
            }
        }
    }

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== CRITICAL HASH VERIFICATION COMPLETE ===\n";