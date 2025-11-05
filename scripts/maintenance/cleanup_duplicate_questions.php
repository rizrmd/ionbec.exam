<?php

/**
 * CLEANUP DUPLICATE QUESTIONS
 * Script to remove duplicate questions, keeping only 4 questions total
 */

echo "=== CLEANING UP DUPLICATE QUESTIONS ===\n\n";

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

    // Start transaction
    $pdo->beginTransaction();

    try {
        echo "=== ANALYZING CURRENT STATE ===\n";

        // Get current questions count
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM questions");
        $totalQuestions = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "Current total questions: $totalQuestions\n";

        // Define the problematic items and which questions to keep
        $cleanupPlan = [
            ['item_id' => 1, 'item_title' => 'Test 01-0411', 'keep_id' => 1, 'delete_id' => 2],
            ['item_id' => 3, 'item_title' => 'Test 03-0411', 'keep_id' => 4, 'delete_id' => 5],
            ['item_id' => 723, 'item_title' => 'Test 04-0411', 'keep_id' => 1055, 'delete_id' => 1056],
        ];

        echo "\n=== CLEANUP PLAN ===\n";
        foreach ($cleanupPlan as $plan) {
            echo "Item: {$plan['item_title']} (ID: {$plan['item_id']})\n";
            echo "  Keep Question ID: {$plan['keep_id']}\n";
            echo "  Delete Question ID: {$plan['delete_id']}\n\n";
        }

        // Get exam questions before cleanup
        $examId = 43;
        $stmt = $pdo->prepare("
            SELECT q.id, i.title as item_title, q.question
            FROM questions q
            JOIN items i ON q.item_id = i.id
            JOIN exam_item ei ON i.id = ei.item_id
            WHERE ei.exam_id = ?
            ORDER BY ei.order, q.id
        ");
        $stmt->execute([$examId]);
        $beforeQuestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "=== QUESTIONS BEFORE CLEANUP ===\n";
        echo "Total questions in exam $examId: " . count($beforeQuestions) . "\n\n";
        foreach ($beforeQuestions as $index => $q) {
            echo ($index + 1) . ". [ID:{$q['id']}] {$q['item_title']}\n";
            echo "   " . substr(strip_tags($q['question']), 0, 80) . "...\n\n";
        }

        // Check for any references to questions we're about to delete
        echo "=== CHECKING REFERENCES ===\n";
        $questionsToDelete = array_column($cleanupPlan, 'delete_id');
        $placeholders = str_repeat('?,', count($questionsToDelete) - 1) . '?';

        // Check attempt_question references
        $stmt = $pdo->prepare("
            SELECT question_id, COUNT(*) as count
            FROM attempt_question
            WHERE question_id IN ($placeholders)
            GROUP BY question_id
        ");
        $stmt->execute($questionsToDelete);
        $attemptReferences = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($attemptReferences)) {
            echo "⚠️  WARNING: Questions have attempt references:\n";
            foreach ($attemptReferences as $ref) {
                echo "  Question ID {$ref['question_id']}: {$ref['count']} attempt records\n";
            }
            echo "  These will also be deleted!\n\n";
        } else {
            echo "✅ No attempt references found\n\n";
        }

        // Check answer references
        $stmt = $pdo->prepare("
            SELECT question_id, COUNT(*) as count
            FROM answers
            WHERE question_id IN ($placeholders)
            GROUP BY question_id
        ");
        $stmt->execute($questionsToDelete);
        $answerReferences = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($answerReferences)) {
            echo "⚠️  WARNING: Questions have answer references:\n";
            foreach ($answerReferences as $ref) {
                echo "  Question ID {$ref['question_id']}: {$ref['count']} answer records\n";
            }
            echo "  These will also be deleted!\n\n";
        } else {
            echo "✅ No answer references found\n\n";
        }

        // Confirm before proceeding
        echo "=== READY TO DELETE ===\n";
        echo "Questions to delete: " . implode(', ', $questionsToDelete) . "\n";
        echo "This action is IRREVERSIBLE!\n\n";

        // Proceed with deletion
        echo "=== DELETING DUPLICATE QUESTIONS ===\n";

        foreach ($questionsToDelete as $questionId) {
            echo "Deleting Question ID: $questionId\n";

            // First delete any attempt_question references
            $stmt = $pdo->prepare("DELETE FROM attempt_question WHERE question_id = ?");
            $affected = $stmt->execute([$questionId]);
            if ($affected) {
                $count = $stmt->rowCount();
                echo "  Deleted $count attempt_question records\n";
            }

            // Delete any answer references
            $stmt = $pdo->prepare("DELETE FROM answers WHERE question_id = ?");
            $affected = $stmt->execute([$questionId]);
            if ($affected) {
                $count = $stmt->rowCount();
                echo "  Deleted $count answer records\n";
            }

            // Finally delete the question
            $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
            $affected = $stmt->execute([$questionId]);
            if ($affected) {
                echo "  ✅ Deleted question $questionId\n";
            }
            echo "\n";
        }

        // Update delivery snapshot
        echo "=== UPDATING DELIVERY SNAPSHOT ===\n";
        $deliveryId = 151;

        // Get new question count
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM questions q
            JOIN items i ON q.item_id = i.id
            JOIN exam_item ei ON i.id = ei.item_id
            WHERE ei.exam_id = ?
        ");
        $stmt->execute([$examId]);
        $newQuestionCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        echo "New question count: $newQuestionCount\n";

        // Update snapshot
        $stmt = $pdo->prepare("
            UPDATE delivery_snapshots
            SET total_questions = ?, updated_at = NOW()
            WHERE delivery_id = ?
        ");
        $stmt->execute([$newQuestionCount, $deliveryId]);

        echo "✅ Updated delivery snapshot\n\n";

        // Get questions after cleanup
        $stmt = $pdo->prepare("
            SELECT q.id, i.title as item_title, q.question
            FROM questions q
            JOIN items i ON q.item_id = i.id
            JOIN exam_item ei ON i.id = ei.item_id
            WHERE ei.exam_id = ?
            ORDER BY ei.order, q.id
        ");
        $stmt->execute([$examId]);
        $afterQuestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "=== QUESTIONS AFTER CLEANUP ===\n";
        echo "Total questions in exam $examId: " . count($afterQuestions) . "\n\n";
        foreach ($afterQuestions as $index => $q) {
            echo ($index + 1) . ". [ID:{$q['id']}] {$q['item_title']}\n";
            echo "   " . substr(strip_tags($q['question']), 0, 80) . "...\n\n";
        }

        // Commit transaction
        $pdo->commit();

        echo "✅ CLEANUP COMPLETED SUCCESSFULLY!\n";
        echo "✅ Questions reduced from " . count($beforeQuestions) . " to " . count($afterQuestions) . "\n";
        echo "✅ Deleted " . count($questionsToDelete) . " duplicate questions\n";

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

echo "\n=== DUPLICATE QUESTIONS CLEANUP COMPLETE ===\n";