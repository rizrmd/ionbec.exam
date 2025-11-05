<?php

/**
 * CHECK DELIVERY SNAPSHOT DATA
 * Check if the delivery has snapshot and what hashes it contains
 */

echo "=== CHECKING DELIVERY SNAPSHOT DATA ===\n\n";

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

    // Find the most recent delivery (from log data)
    $stmt = $pdo->query("SELECT id, name, hash, snapshot_id, exam_id, created_at FROM deliveries WHERE name LIKE '%COBA%' OR name LIKE '%DEMO%' ORDER BY created_at DESC LIMIT 5");
    $deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "=== RECENT DELIVERIES ===\n";
    foreach ($deliveries as $delivery) {
        echo "Delivery ID: {$delivery['id']}, Name: {$delivery['name']}, Hash: {$delivery['hash']}, Snapshot ID: " . ($delivery['snapshot_id'] ?? 'NULL') . "\n";
    }
    echo "\n";

    // Check snapshot for the most recent delivery
    $recentDelivery = $deliveries[0];
    echo "=== CHECKING SNAPSHOT FOR DELIVERY: {$recentDelivery['name']} (ID: {$recentDelivery['id']}) ===\n";

    if ($recentDelivery['snapshot_id']) {
        $stmt = $pdo->prepare("SELECT id, total_items, total_questions, created_at FROM delivery_snapshots WHERE id = ?");
        $stmt->execute([$recentDelivery['snapshot_id']]);
        $snapshot = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($snapshot) {
            echo "✅ Snapshot found: ID {$snapshot['id']}, Total Items: {$snapshot['total_items']}, Total Questions: {$snapshot['total_questions']}\n";
            echo "   Created: {$snapshot['created_at']}\n";

            // Get the exam structure from snapshot
            $stmt = $pdo->prepare("SELECT exam_structure FROM delivery_snapshots WHERE id = ?");
            $stmt->execute([$recentDelivery['snapshot_id']]);
            $snapshotData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($snapshotData) {
                $examStructure = json_decode($snapshotData['exam_structure'], true);
                if ($examStructure && isset($examStructure['items'])) {
                    echo "\n=== ANALYZING SNAPSHOT ITEMS ===\n";
                    $items = $examStructure['items'];
                    echo "Total items in snapshot: " . count($items) . "\n\n";

                    // Check first few items and their hashes
                    $sampleCount = min(5, count($items));
                    for ($i = 0; $i < $sampleCount; $i++) {
                        $item = $items[$i];
                        echo "Item " . ($i + 1) . ":\n";
                        echo "  Title: " . substr($item['title'] ?? 'No title', 0, 50) . "\n";
                        echo "  Hash: " . ($item['hash'] ?? 'NULL') . "\n";

                        // Check if this hash exists in current database
                        if (isset($item['hash']) && $item['hash']) {
                            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM items WHERE hash = ?");
                            $stmt->execute([$item['hash']]);
                            $exists = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                            echo "  Exists in current database: " . ($exists > 0 ? 'YES' : 'NO') . "\n";
                        }
                        echo "\n";
                    }

                    // Let's search for the failing hashes from logs
                    $failingHashes = ['3ZBYv4k0', '3oKMJAB6', '1Ogda5Kz', 'dVg6X0Bp'];
                    echo "=== SEARCHING FOR FAILING HASHES IN SNAPSHOT ===\n";

                    foreach ($failingHashes as $failingHash) {
                        $found = false;
                        foreach ($items as $item) {
                            if (isset($item['hash']) && $item['hash'] === $failingHash) {
                                echo "✅ Found hash '$failingHash' in snapshot item: " . substr($item['title'], 0, 50) . "\n";
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) {
                            echo "❌ Hash '$failingHash' NOT FOUND in snapshot\n";
                        }
                    }
                }
            }
        } else {
            echo "❌ Snapshot data not found in database\n";
        }
    } else {
        echo "❌ No snapshot ID for this delivery\n";
    }

    // Also check for any recent exam data that might be used instead of snapshot
    echo "\n=== CHECKING EXAM DATA ===\n";
    $stmt = $pdo->prepare("SELECT id, code, name FROM exams WHERE id = ? LIMIT 1");
    $stmt->execute([$recentDelivery['exam_id']]);
    $exam = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($exam) {
        echo "Exam: {$exam['name']} (Code: {$exam['code']})\n";

        // Check some recent items from this exam
        $stmt = $pdo->prepare("SELECT id, title, hash FROM items WHERE exam_id = ? ORDER BY id LIMIT 5");
        $stmt->execute([$recentDelivery['exam_id']]);
        $examItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "Recent exam items:\n";
        foreach ($examItems as $item) {
            echo "  ID: {$item['id']}, Hash: {$item['hash']}, Title: " . substr($item['title'], 0, 50) . "\n";
        }
    }

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== SNAPSHOT CHECKING COMPLETE ===\n";