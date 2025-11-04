<?php

/**
 * ANALYZE SNAPSHOT CREATION PROCESS
 * Check if snapshot creation caused the duplication
 */

echo "=== ANALYZING SNAPSHOT CREATION PROCESS ===\n\n";

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

    // Get delivery 151 details
    $deliveryId = 151;

    echo "=== DELIVERY 151 DETAILS ===\n";
    $stmt = $pdo->prepare("
        SELECT id, name, hash, exam_id, created_at, updated_at
        FROM deliveries
        WHERE id = ?
    ");
    $stmt->execute([$deliveryId]);
    $delivery = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($delivery) {
        echo "  Delivery ID: {$delivery['id']}\n";
        echo "  Name: {$delivery['name']}\n";
        echo "  Hash: {$delivery['hash']}\n";
        echo "  Created: {$delivery['created_at']}\n";
        echo "  Updated: {$delivery['updated_at']}\n\n";

        // Get snapshot details
        echo "=== SNAPSHOT DETAILS ===\n";
        $stmt = $pdo->prepare("
            SELECT id, total_items, total_questions, created_at, updated_at
            FROM delivery_snapshots
            WHERE delivery_id = ?
        ");
        $stmt->execute([$deliveryId]);
        $snapshot = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($snapshot) {
            echo "  Snapshot ID: {$snapshot['id']}\n";
            echo "  Total Items: {$snapshot['total_items']}\n";
            echo "  Total Questions: {$snapshot['total_questions']}\n";
            echo "  Created: {$snapshot['created_at']}\n";
            echo "  Updated: {$snapshot['updated_at']}\n\n";

            // Analyze timing
            $deliveryCreated = strtotime($delivery['created_at']);
            $snapshotCreated = strtotime($snapshot['created_at']);
            $timeDiff = $snapshotCreated - $deliveryCreated;

            echo "  ⏰ TIMING ANALYSIS:\n";
            echo "    Delivery created: {$delivery['created_at']}\n";
            echo "    Snapshot created: {$snapshot['created_at']}\n";
            echo "    Time difference: " . ($timeDiff > 0 ? "+{$timeDiff} seconds" : "{$timeDiff} seconds") . "\n";
            echo "    Time difference: " . round($timeDiff / 60, 2) . " minutes\n\n";

            if ($timeDiff < 300) { // Less than 5 minutes
                echo "    ⚠️  Snapshot created very quickly after delivery - possible automated process\n\n";
            }

            // Get snapshot structure
            echo "=== SNAPSHOT STRUCTURE ANALYSIS ===\n";
            $stmt = $pdo->prepare("
                SELECT exam_structure
                FROM delivery_snapshots
                WHERE delivery_id = ?
            ");
            $stmt->execute([$deliveryId]);
            $snapshotData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($snapshotData) {
                $examStructure = json_decode($snapshotData['exam_structure'], true);
                if ($examStructure && isset($examStructure['items'])) {
                    echo "  Items in snapshot: " . count($examStructure['items']) . "\n\n";

                    $snapshotQuestionIds = [];
                    foreach ($examStructure['items'] as $index => $item) {
                        echo "  Item " . ($index + 1) . ": " . ($item['title'] ?? 'No title') . "\n";
                        if (isset($item['questions']) && is_array($item['questions'])) {
                            echo "    Questions in snapshot: " . count($item['questions']) . "\n";
                            foreach ($item['questions'] as $qIndex => $question) {
                                $qId = $question['id'] ?? null;
                                if ($qId) {
                                    $snapshotQuestionIds[] = $qId;
                                    echo "      Q" . ($qIndex + 1) . " (ID: {$qId}): " . substr(strip_tags($question['question'] ?? ''), 0, 50) . "...\n";
                                }
                            }
                        }
                        echo "\n";
                    }

                    echo "  Total question IDs in snapshot: " . count($snapshotQuestionIds) . "\n";
                    echo "  Question IDs in snapshot: " . implode(', ', $snapshotQuestionIds) . "\n\n";

                    // Compare with current database
                    echo "=== CURRENT DATABASE COMPARISON ===\n";
                    $currentQuestionIds = [];

                    foreach ($examStructure['items'] as $item) {
                        $itemId = $item['id'] ?? null;
                        if ($itemId) {
                            $stmt = $pdo->prepare("SELECT id FROM questions WHERE item_id = ?");
                            $stmt->execute([$itemId]);
                            $questions = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
                            $currentQuestionIds = array_merge($currentQuestionIds, $questions);
                        }
                    }

                    echo "  Current question IDs: " . implode(', ', $currentQuestionIds) . "\n";
                    echo "  Total current questions: " . count($currentQuestionIds) . "\n\n";

                    // Check for differences
                    $missingInSnapshot = array_diff($currentQuestionIds, $snapshotQuestionIds);
                    $extraInSnapshot = array_diff($snapshotQuestionIds, $currentQuestionIds);

                    if (!empty($missingInSnapshot)) {
                        echo "  ❌ Questions in database but NOT in snapshot: " . implode(', ', $missingInSnapshot) . "\n";
                    }
                    if (!empty($extraInSnapshot)) {
                        echo "  ❌ Questions in snapshot but NOT in database: " . implode(', ', $extraInSnapshot) . "\n";
                    }
                    if (empty($missingInSnapshot) && empty($extraInSnapshot)) {
                        echo "  ✅ Snapshot and database question IDs match\n";
                    }
                    echo "\n";
                }
            }
        } else {
            echo "  ❌ No snapshot found for delivery\n\n";
        }
    }

    // Check for any database transaction logs or patterns
    echo "=== QUESTION CREATION PATTERNS ===\n";

    $problematicItems = [1, 3, 723];
    foreach ($problematicItems as $itemId) {
        echo "  Item ID: $itemId\n";

        $stmt = $pdo->prepare("
            SELECT id, question, created_at, updated_at
            FROM questions
            WHERE item_id = ?
            ORDER BY created_at
        ");
        $stmt->execute([$itemId]);
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($questions) > 1) {
            echo "    Questions created rapidly:\n";
            for ($i = 1; $i < count($questions); $i++) {
                $prevTime = strtotime($questions[$i-1]['created_at']);
                $currTime = strtotime($questions[$i]['created_at']);
                $diff = $currTime - $prevTime;

                echo "      Q{$questions[$i-1]['id']} -> Q{$questions[$i]['id']}: {$diff} seconds apart\n";

                if ($diff < 60) { // Less than 1 minute
                    echo "        ⚠️  Very rapid creation - possible duplicate submission\n";
                }
            }
        }
        echo "\n";
    }

    // Check if this could be related to ExamSnapshotService
    echo "=== EXAM SNAPSHOT SERVICE ANALYSIS ===\n";

    // Look for the ExamSnapshotService in the codebase
    $servicePath = __DIR__ . '/app/Services/ExamSnapshotService.php';
    if (file_exists($servicePath)) {
        echo "  ✅ ExamSnapshotService exists\n";

        $serviceContent = file_get_contents($servicePath);

        // Check for potential duplication logic
        if (strpos($serviceContent, 'duplicate') !== false) {
            echo "  ⚠️  Found 'duplicate' in ExamSnapshotService\n";
        }
        if (strpos($serviceContent, 'clone') !== false) {
            echo "  ⚠️  Found 'clone' in ExamSnapshotService\n";
        }
        if (strpos($serviceContent, 'copy') !== false) {
            echo "  ⚠️  Found 'copy' in ExamSnapshotService\n";
        }
    } else {
        echo "  ❌ ExamSnapshotService not found\n";
    }

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== SNAPSHOT CREATION ANALYSIS COMPLETE ===\n";