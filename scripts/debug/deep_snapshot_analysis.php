<?php

/**
 * DEEP SNAPSHOT STRUCTURE ANALYSIS
 */

echo "=== DEEP SNAPSHOT STRUCTURE ANALYSIS ===\n\n";

// Connect to database
$host = '107.155.75.50';
$port = '5986';
$dbname = 'ionbec-new';
$username = 'postgres';
$password = '6LP0Ojegy7IUU6kaX9lLkmZRUiAdAUNOltWyL3LegfYGR6rPQtB4DUSVqjdA78ES';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✅ Database connected\n\n";

    $deliveryId = 21;

    // Get delivery snapshot
    $stmt = $pdo->prepare("
        SELECT exam_structure, total_questions, total_items, created_at
        FROM delivery_snapshots
        WHERE delivery_id = ?
    ");
    $stmt->execute([$deliveryId]);
    $snapshot = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($snapshot && $snapshot['exam_structure']) {
        $examStructure = json_decode($snapshot['exam_structure'], true);

        if ($examStructure && isset($examStructure['items'])) {
            $snapshotItems = $examStructure['items'];

            echo "=== SNAPSHOT ITEMS STRUCTURE ===\n";
            echo "Total items in snapshot: " . count($snapshotItems) . "\n\n";

            // Analyze first item structure
            if (count($snapshotItems) > 0) {
                $firstItem = $snapshotItems[0];
                echo "First item structure:\n";
                echo "Keys: " . implode(', ', array_keys($firstItem)) . "\n\n";

                // Show first 3 items with all their data
                echo "First 3 items with full data:\n";
                for ($i = 0; $i < min(3, count($snapshotItems)); $i++) {
                    $item = $snapshotItems[$i];
                    echo "\n--- Item $i ---\n";
                    foreach ($item as $key => $value) {
                        if (is_array($value)) {
                            echo "$key: [" . count($value) . " items]\n";
                            if ($key === 'questions' && count($value) > 0) {
                                $firstQuestion = $value[0];
                                echo "  First question keys: " . implode(', ', array_keys($firstQuestion)) . "\n";
                                if (isset($firstQuestion['hash'])) {
                                    echo "  First question hash: " . $firstQuestion['hash'] . "\n";
                                }
                            }
                        } else {
                            // Truncate long strings
                            if (is_string($value) && strlen($value) > 100) {
                                echo "$key: " . substr($value, 0, 100) . "...\n";
                            } else {
                                echo "$key: $value\n";
                            }
                        }
                    }
                }
            }

            // Extract all item hashes from snapshot
            echo "\n=== ALL SNAPSHOT ITEM HASHES ===\n";
            $snapshotHashes = [];
            foreach ($snapshotItems as $index => $item) {
                $hash = $item['hash'] ?? null;
                $name = $item['name'] ?? 'No name';
                $snapshotHashes[] = $hash;
                echo sprintf("%2d. %-12s - %s\n", $index + 1, $hash, $name);
            }

            // Check database for these hashes
            echo "\n=== HASH VERIFICATION ===\n";
            $inDbCount = 0;
            $notInDbCount = 0;

            foreach ($snapshotHashes as $hash) {
                if ($hash) {
                    $stmt = $pdo->prepare("SELECT id, title FROM items WHERE hash = ?");
                    $stmt->execute([$hash]);
                    $dbItem = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($dbItem) {
                        $inDbCount++;
                    } else {
                        $notInDbCount++;
                        echo "❌ Missing from DB: $hash\n";
                    }
                }
            }

            echo "\nHash verification summary:\n";
            echo "✅ Found in database: $inDbCount\n";
            echo "❌ Missing from database: $notInDbCount\n";

            // Now check the reverse - items from database that should be in this delivery
            echo "\n=== DATABASE ITEMS FOR EXAM ===\n";
            $examId = 74;

            // Try different relationship patterns
            $stmt = $pdo->prepare("
                SELECT i.id, i.hash, i.title, i.created_at
                FROM items i
                WHERE i.client_id = 16
                AND i.created_at <= '2025-11-03 12:53:09'
                ORDER BY i.created_at
                LIMIT 20
            ");
            $stmt->execute();
            $dbItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo "Database items (recent, client 16):\n";
            foreach ($dbItems as $item) {
                $inSnapshot = in_array($item['hash'], $snapshotHashes);
                $status = $inSnapshot ? "✅" : "❌";
                echo sprintf("%s %-12s - %s\n", $status, $item['hash'], $item['title']);
            }

        } else {
            echo "❌ No items found in snapshot structure\n";
        }
    } else {
        echo "❌ No snapshot found or empty structure\n";
    }

    echo "\n=== FINAL ANALYSIS ===\n";
    echo "1. Snapshot contains items with specific hashes\n";
    echo "2. getQuestions() tries to find items by hash\n";
    echo "3. Some hashes from snapshot may not exist in database\n";
    echo "4. OR database has newer/different items\n";
    echo "5. This causes Item not found errors in getQuestions()\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== ANALYSIS COMPLETE ===\n";