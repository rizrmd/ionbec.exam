<?php

/**
 * TEST ATTEMPT FIX FOR DELIVERY 22
 */

echo "=== TESTING ATTEMPT FIX FOR DELIVERY 22 ===\n\n";

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

    // Test the fixed query
    echo "=== TESTING FIXED QUERY ===\n";
    $deliveryId = 22;
    $takerId = 114; // Based on logs from previous attempt

    echo "Looking for attempt with:\n";
    echo "- delivery_id: $deliveryId\n";
    echo "- attempted_by: $takerId\n\n";

    $stmt = $pdo->prepare("
        SELECT id, attempted_by, delivery_id, exam_id, started_at, score, progress
        FROM attempts
        WHERE delivery_id = ? AND attempted_by = ?
        ORDER BY id DESC
        LIMIT 1
    ");
    $stmt->execute([$deliveryId, $takerId]);
    $attempt = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($attempt) {
        echo "✅ ATTEMPT FOUND:\n";
        echo "- Attempt ID: {$attempt['id']}\n";
        echo "- Attempted by: {$attempt['attempted_by']}\n";
        echo "- Delivery ID: {$attempt['delivery_id']}\n";
        echo "- Exam ID: {$attempt['exam_id']}\n";
        echo "- Started at: {$attempt['started_at']}\n";
        echo "- Score: {$attempt['score']}\n";
        echo "- Progress: {$attempt['progress']}\n\n";

        // Check for attempt questions
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as question_count
            FROM attempt_questions
            WHERE attempt_id = ?
        ");
        $stmt->execute([$attempt['id']]);
        $questionCount = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Questions linked to attempt: {$questionCount['question_count']}\n\n";

        // Get some sample attempt questions
        $stmt = $pdo->prepare("
            SELECT aq.answer_hash, aq.answer, aq.is_correct, q.hash as question_hash, q.content
            FROM attempt_questions aq
            JOIN questions q ON aq.question_id = q.id
            WHERE aq.attempt_id = ?
            LIMIT 5
        ");
        $stmt->execute([$attempt['id']]);
        $attemptQuestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($attemptQuestions) {
            echo "Sample attempt questions:\n";
            foreach ($attemptQuestions as $index => $aq) {
                echo "  " . ($index + 1) . ". Question {$aq['question_hash']}: ";
                echo "Answer {$aq['answer_hash']} (" . ($aq['is_correct'] ? 'Correct' : 'Incorrect') . ")\n";
            }
        } else {
            echo "❌ No questions found linked to this attempt\n";
        }

    } else {
        echo "❌ NO ATTEMPT FOUND\n\n";

        // Check if there are any attempts for this delivery
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM attempts WHERE delivery_id = ?");
        $stmt->execute([$deliveryId]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Total attempts for delivery $deliveryId: {$count['count']}\n";

        if ($count['count'] > 0) {
            echo "\nAttempts for this delivery (other takers):\n";
            $stmt = $pdo->prepare("
                SELECT id, attempted_by, started_at, score, progress
                FROM attempts
                WHERE delivery_id = ?
                ORDER BY id DESC
                LIMIT 5
            ");
            $stmt->execute([$deliveryId]);
            $attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($attempts as $att) {
                echo "- Attempt {$att['id']}: Taker {$att['attempted_by']}, Score {$att['score']}, Progress {$att['progress']}%\n";
            }
        }

        // Check if this taker has attempts for other deliveries
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM attempts WHERE attempted_by = ?");
        $stmt->execute([$takerId]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "\nTotal attempts for taker $takerId: {$count['count']}\n";
    }

    // Compare with old broken query
    echo "\n=== TESTING OLD BROKEN QUERY ===\n";
    try {
        $stmt = $pdo->prepare("
            SELECT id, attempted_by, delivery_id
            FROM attempts
            WHERE delivery_id = ? AND taker_id = ?
        ");
        $stmt->execute([$deliveryId, $takerId]);
        $oldResult = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($oldResult) {
            echo "❌ OLD QUERY FOUND RESULTS (unexpected)\n";
        } else {
            echo "✅ OLD QUERY RETURNS NULL (expected - column doesn't exist)\n";
        }
    } catch (Exception $e) {
        echo "✅ OLD QUERY FAILS (expected): " . $e->getMessage() . "\n";
    }

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";