<?php

/**
 * DELIVERY SNAPSHOT INVESTIGATION
 *
 * Check if delivery snapshot contains different items than the main database
 */

echo "=== DELIVERY SNAPSHOT INVESTIGATION ===\n\n";

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
    $examId = 74;

    echo "=== DELIVERY SNAPSHOT ANALYSIS ===\n";
    echo "Delivery ID: $deliveryId\n";
    echo "Exam ID: $examId\n\n";

    // Check delivery snapshot
    $stmt = $pdo->prepare("
        SELECT exam_structure, total_questions, total_items, created_at
        FROM delivery_snapshots
        WHERE delivery_id = ?
    ");
    $stmt->execute([$deliveryId]);
    $snapshot = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($snapshot) {
        echo "✅ Snapshot found:\n";
        echo "- Total Questions: {$snapshot['total_questions']}\n";
        echo "- Total Items: {$snapshot['total_items']}\n";
        echo "- Created: {$snapshot['created_at']}\n\n";

        // Decode exam_structure
        $examStructure = json_decode($snapshot['exam_structure'], true);
        if ($examStructure) {
            echo "=== EXAM STRUCTURE ANALYSIS ===\n";
            echo "Structure keys: " . implode(', ', array_keys($examStructure)) . "\n\n";

            // Check if items exist in structure
            if (isset($examStructure['items'])) {
                $snapshotItems = $examStructure['items'];
                echo "Snapshot items count: " . count($snapshotItems) . "\n\n";

                // Get first few snapshot hashes
                echo "First 5 snapshot item hashes:\n";
                $count = 0;
                foreach ($snapshotItems as $item) {
                    if ($count >= 5) break;
                    $hash = $item['hash'] ?? 'N/A';
                    $name = $item['name'] ?? 'N/A';
                    echo "- $hash: $name\n";
                    $count++;
                }
                echo "\n";

                // Check failing hashes against snapshot
                $failingHashes = [
                    '0Qk8Pqko', // MCQ 30 - Item not found in getQuestions
                    'lAKjyXg9', // MCQ 42 - Item not found
                    'qZgleXK5', // MCQ 5  - Item not found
                    'DxKJDpkq', // MCQ 13 - Item not found
                    'MlK1PYgN', // MCQ 14 - Item not found
                ];

                $workingHashes = [
                    '3ZBYv4k0', // MCQ 11 - Item found
                    '53gDGMky', // MCQ 9 & 10 - Item found
                    'xDkVA1BX', // MCQ 40 - Item found
                ];

                echo "=== FAILING HASHES IN SNAPSHOT ===\n";
                foreach ($failingHashes as $hash) {
                    $foundInSnapshot = false;
                    foreach ($snapshotItems as $item) {
                        if (($item['hash'] ?? '') === $hash) {
                            $foundInSnapshot = true;
                            echo "✅ IN SNAPSHOT: $hash - " . ($item['name'] ?? 'N/A') . "\n";
                            break;
                        }
                    }
                    if (!$foundInSnapshot) {
                        echo "❌ NOT IN SNAPSHOT: $hash\n";
                    }
                }

                echo "\n=== WORKING HASHES IN SNAPSHOT ===\n";
                foreach ($workingHashes as $hash) {
                    $foundInSnapshot = false;
                    foreach ($snapshotItems as $item) {
                        if (($item['hash'] ?? '') === $hash) {
                            $foundInSnapshot = true;
                            echo "✅ IN SNAPSHOT: $hash - " . ($item['name'] ?? 'N/A') . "\n";
                            break;
                        }
                    }
                    if (!$foundInSnapshot) {
                        echo "❌ NOT IN SNAPSHOT: $hash\n";
                    }
                }

            } else {
                echo "❌ No 'items' key in exam_structure\n";
                echo "Available keys: " . implode(', ', array_keys($examStructure)) . "\n";
            }

        } else {
            echo "❌ Failed to decode exam_structure JSON\n";
        }

    } else {
        echo "❌ No snapshot found for delivery $deliveryId\n";
    }

    echo "\n=== EXAM ITEMS COMPARISON ===\n";
    // Check exam_items table
    $stmt = $pdo->prepare("
        SELECT i.id, i.hash, i.title
        FROM exam_items ei
        JOIN items i ON ei.item_id = i.id
        WHERE ei.exam_id = ?
        ORDER BY ei.order_index
        LIMIT 10
    ");
    $stmt->execute([$examId]);
    $examItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Exam items (first 10):\n";
    foreach ($examItems as $item) {
        echo "- {$item['hash']}: {$item['title']}\n";
    }

    echo "\n=== ROOT CAUSE HYPOTHESIS ===\n";
    echo "If MainController uses delivery snapshot but getQuestions() uses direct database:\n";
    echo "1. Initial load uses snapshot data (some items)\n";
    echo "2. AJAX getQuestions() uses database query (different items)\n";
    echo "3. Hash mismatch causes Item not found errors\n";
    echo "4. Green indicators fail because frontend expects snapshot items\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== INVESTIGATION COMPLETE ===\n";