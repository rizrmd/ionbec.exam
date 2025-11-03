<?php

/**
 * SYNC HASHES FROM RUST API
 * Update database hashes to match what the Rust API is generating
 * Based on the failing hashes from logs and their corresponding titles
 */

echo "=== SYNCING HASHES FROM RUST API ===\n\n";

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

    // Hash mappings from the logs - these are the hashes Rust API is generating
    $rustHashMappings = [
        'BE 051125 - MCQ 11' => '3ZBYv4k0',
        'BE 051125 - MCQ 39' => '3oKMJAB6',
        'BE 051125 - MCQ 47' => '1Ogda5Kz',
        'BE 051125 - MCQ 57' => 'dVg6X0Bp',
        // Add more mappings as we discover them from logs
    ];

    echo "=== UPDATING HASHES BASED ON RUST API MAPPINGS ===\n";
    $updatedCount = 0;
    $notFoundCount = 0;

    foreach ($rustHashMappings as $title => $rustHash) {
        echo "Processing: '$title' -> '$rustHash'\n";

        // Find the item in database by title (exact match)
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

            // Try partial match (in case there are slight differences)
            $stmt = $pdo->prepare("SELECT id, title, hash FROM items WHERE title LIKE ? ORDER BY id LIMIT 3");
            $stmt->execute(["%$title%"]);
            $similarItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($similarItems) > 0) {
                echo "  🔍 Found similar items:\n";
                foreach ($similarItems as $similar) {
                    echo "    - ID: {$similar['id']}, Title: '{$similar['title']}', Hash: '{$similar['hash']}'\n";
                }
                echo "  ⚠️  Please verify which item should be updated manually\n";
            }

            $notFoundCount++;
        }
        echo "\n";
    }

    // Let's also try to extract more potential mappings from the logs
    echo "=== LOOKING FOR MORE HASH MAPPINGS ===\n";

    // Look for patterns in recent log data (we can add more as we discover them)
    $recentPatterns = [
        'BE 051125 - MCQ 12' => null, // Will be filled from logs if found
        'BE 051125 - MCQ 64 / BE12018-32' => null,
        'BE 051125 - MCQ 68 / BE12018-67' => null,
        'BE 051125 - MCQ 70 / BE18718-UI9' => null,
        'BE 051125 - MCQ 77/ BE191218-90' => null,
    ];

    foreach ($recentPatterns as $title => $expectedHash) {
        $stmt = $pdo->prepare("SELECT id, title, hash FROM items WHERE title LIKE ? ORDER BY id LIMIT 1");
        $stmt->execute(["%$title%"]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            echo "Found: '$title' -> Current hash: '{$item['hash']}'\n";
            // We'll need to get the Rust API hash for these from logs
        }
    }

    // Verification
    echo "=== VERIFICATION ===\n";
    echo "Items updated: $updatedCount\n";
    echo "Items not found: $notFoundCount\n\n";

    // Test the updated hashes
    if ($updatedCount > 0) {
        echo "=== TESTING UPDATED HASHES ===\n";
        foreach ($rustHashMappings as $title => $rustHash) {
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

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== HASH SYNCING COMPLETE ===\n";