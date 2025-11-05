<?php

/**
 * FIND ALL DUPLICATES IN DATABASE
 * Find and clean up all duplicate questions before creating constraints
 */

echo "=== FINDING ALL DUPLICATE QUESTIONS ===\n\n";

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

    // Find all duplicate questions
    echo "=== FINDING ALL DUPLICATES ===\n";
    $stmt = $pdo->query("
        SELECT
            i.id as item_id,
            i.title as item_title,
            q.question,
            COUNT(*) as duplicate_count,
            string_agg(q.id::text, ',' ORDER BY q.id) as question_ids,
            MIN(q.created_at) as first_created,
            MAX(q.created_at) as last_created
        FROM questions q
        JOIN items i ON q.item_id = i.id
        GROUP BY i.id, i.title, q.question
        HAVING COUNT(*) > 1
        ORDER BY i.id, COUNT(*) DESC
    ");
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($duplicates)) {
        echo "✅ No duplicates found\n";
        exit;
    }

    echo "Found " . count($duplicates) . " items with duplicate questions:\n\n";

    $cleanupPlan = [];
    foreach ($duplicates as $dup) {
        echo "Item ID: {$dup['item_id']} - {$dup['item_title']}\n";
        echo "  Question: " . substr(strip_tags($dup['question']), 0, 100) . "...\n";
        echo "  Duplicates: {$dup['duplicate_count']}\n";
        $questionIds = explode(',', $dup['question_ids']);
        echo "  Question IDs: " . implode(', ', $questionIds) . "\n";
        echo "  First created: {$dup['first_created']}\n";
        echo "  Last created: {$dup['last_created']}\n\n";

        // Plan to keep the first question and delete the rest
        $questionIds = explode(',', $dup['question_ids']);
        $keepId = array_shift($questionIds);
        $cleanupPlan[] = [
            'item_id' => $dup['item_id'],
            'item_title' => $dup['item_title'],
            'keep_id' => $keepId,
            'delete_ids' => $questionIds
        ];
    }

    echo "=== CLEANUP PLAN ===\n";
    $totalToDelete = 0;
    foreach ($cleanupPlan as $plan) {
        echo "Item: {$plan['item_title']} (ID: {$plan['item_id']})\n";
        echo "  Keep Question ID: {$plan['keep_id']}\n";
        echo "  Delete Question IDs: " . implode(', ', $plan['delete_ids']) . "\n\n";
        $totalToDelete += count($plan['delete_ids']);
    }

    echo "Total questions to delete: $totalToDelete\n\n";

    // Confirm and proceed
    echo "Proceeding with cleanup...\n\n";

    // Start transaction
    $pdo->beginTransaction();

    try {
        echo "=== DELETING ALL DUPLICATES ===\n";

        foreach ($cleanupPlan as $plan) {
            echo "Cleaning item: {$plan['item_title']} (ID: {$plan['item_id']})\n";

            foreach ($plan['delete_ids'] as $questionId) {
                echo "  Deleting Question ID: $questionId\n";

                // Check references
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM attempt_question WHERE question_id = ?");
                $stmt->execute([$questionId]);
                $attemptCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM answers WHERE question_id = ?");
                $stmt->execute([$questionId]);
                $answerCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

                if ($attemptCount > 0) {
                    echo "    Warning: $attemptCount attempt records will be deleted\n";
                    $stmt = $pdo->prepare("DELETE FROM attempt_question WHERE question_id = ?");
                    $stmt->execute([$questionId]);
                }

                if ($answerCount > 0) {
                    echo "    Warning: $answerCount answer records will be deleted\n";
                    $stmt = $pdo->prepare("DELETE FROM answers WHERE question_id = ?");
                    $stmt->execute([$questionId]);
                }

                // Delete the question
                $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
                $stmt->execute([$questionId]);
                echo "    ✅ Deleted question $questionId\n";
            }
            echo "\n";
        }

        // Commit transaction
        $pdo->commit();

        echo "✅ ALL DUPLICATES CLEANED UP!\n";
        echo "✅ Deleted $totalToDelete duplicate questions\n\n";

        // Verify no more duplicates
        echo "=== VERIFICATION ===\n";
        $stmt = $pdo->query("
            SELECT COUNT(*) as count
            FROM (
                SELECT item_id, question
                FROM questions
                GROUP BY item_id, question
                HAVING COUNT(*) > 1
            ) dups
        ");
        $remaining = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        if ($remaining == 0) {
            echo "✅ No more duplicates found - ready for constraints!\n";
        } else {
            echo "❌ Still found $remaining duplicate groups\n";
        }

    } catch (Exception $e) {
        // Rollback on error
        $pdo->rollback();
        throw $e;
    }

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== DUPLICATE CLEANUP COMPLETE ===\n";