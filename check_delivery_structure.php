<?php

/**
 * CHECK DELIVERY AND SNAPSHOT STRUCTURE
 * Check database structure and find the correct column names
 */

echo "=== CHECKING DELIVERY AND SNAPSHOT STRUCTURE ===\n\n";

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

    // Check deliveries table structure
    echo "=== DELIVERIES TABLE STRUCTURE ===\n";
    $stmt = $pdo->query("
        SELECT column_name, data_type, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'deliveries'
        ORDER BY ordinal_position
    ");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $column) {
        echo "Column: {$column['column_name']} ({$column['data_type']}) - " . ($column['is_nullable'] === 'YES' ? 'NULLABLE' : 'NOT NULL') . "\n";
    }
    echo "\n";

    // Check delivery_snapshots table structure
    echo "=== DELIVERY SNAPSHOTS TABLE STRUCTURE ===\n";
    $stmt = $pdo->query("
        SELECT column_name, data_type, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'delivery_snapshots'
        ORDER BY ordinal_position
    ");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $column) {
        echo "Column: {$column['column_name']} ({$column['data_type']}) - " . ($column['is_nullable'] === 'YES' ? 'NULLABLE' : 'NOT NULL') . "\n";
    }
    echo "\n";

    // Find recent deliveries
    echo "=== RECENT DELIVERIES ===\n";
    $stmt = $pdo->query("SELECT id, name, hash, exam_id, created_at FROM deliveries ORDER BY created_at DESC LIMIT 5");
    $deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($deliveries as $delivery) {
        echo "Delivery ID: {$delivery['id']}, Name: {$delivery['name']}, Hash: {$delivery['hash']}, Exam ID: {$delivery['exam_id']}\n";
    }
    echo "\n";

    // Check if there are any delivery_snapshots
    echo "=== DELIVERY SNAPSHOTS ===\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM delivery_snapshots");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "Total snapshots: $count\n\n";

    if ($count > 0) {
        $stmt = $pdo->query("SELECT id, delivery_id, total_items, total_questions, created_at FROM delivery_snapshots ORDER BY created_at DESC LIMIT 3");
        $snapshots = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($snapshots as $snapshot) {
            echo "Snapshot ID: {$snapshot['id']}, Delivery ID: {$snapshot['delivery_id']}, Items: {$snapshot['total_items']}, Questions: {$snapshot['total_questions']}\n";
        }
        echo "\n";

        // Check the most recent snapshot structure
        $recentSnapshot = $snapshots[0];
        echo "=== ANALYZING MOST RECENT SNAPSHOT ===\n";

        $stmt = $pdo->prepare("SELECT exam_structure FROM delivery_snapshots WHERE id = ?");
        $stmt->execute([$recentSnapshot['id']]);
        $snapshotData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($snapshotData) {
            $examStructure = json_decode($snapshotData['exam_structure'], true);
            if ($examStructure && isset($examStructure['items'])) {
                $items = $examStructure['items'];
                echo "Total items in snapshot: " . count($items) . "\n\n";

                // Check first few items and their hashes
                $sampleCount = min(3, count($items));
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
    }

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== STRUCTURE CHECKING COMPLETE ===\n";