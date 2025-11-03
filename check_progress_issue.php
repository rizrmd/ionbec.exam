<?php

// Direct database connection to avoid Laravel bootstrap errors
$host = '107.155.75.50';
$port = '5986';
$dbname = 'ionbec-new';
$user = 'postgres';
$password = '6LP0Ojegy7IUU6kaX9lLkmZRUiAdAUNOltWyL3LegfYGR6rPQtB4DUSVqjdA78ES';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

echo "=== CHECKING PROGRESS > 100% ISSUE ===\n\n";

// First, let's find ALL deliveries with attempts that have progress > 100%
$stmt = $pdo->query("
    SELECT DISTINCT d.id, d.name, d.exam_id,
           (SELECT COUNT(*) FROM attempts WHERE delivery_id = d.id AND progress > 100) as problematic_count
    FROM deliveries d
    JOIN attempts a ON a.delivery_id = d.id
    WHERE a.progress > 100
    ORDER BY problematic_count DESC
    LIMIT 5
");
$deliveriesWithIssues = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($deliveriesWithIssues) . " deliveries with progress > 100% issues:\n";
foreach ($deliveriesWithIssues as $d) {
    echo "  - Delivery ID {$d['id']}: {$d['name']} ({$d['problematic_count']} attempts affected)\n";
}
echo "\n";

// Use the first one with most issues
if (empty($deliveriesWithIssues)) {
    die("No deliveries found with progress > 100% issues.\n");
}

$delivery = $deliveriesWithIssues[0];
echo "Analyzing delivery: {$delivery['name']} (ID: {$delivery['id']})\n";
echo "Exam ID: {$delivery['exam_id']}\n\n";

// Get total questions currently in exam
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT q.id) as total_questions
    FROM questions q
    JOIN exam_item ei ON q.item_id = ei.item_id
    WHERE ei.exam_id = :exam_id
");
$stmt->execute(['exam_id' => $delivery['exam_id']]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$totalQuestionsInExam = $result['total_questions'];

echo "Total questions currently in exam: {$totalQuestionsInExam}\n\n";

// Get attempts with progress > 100%
echo "=== ATTEMPTS WITH PROGRESS > 100% ===\n";
$stmt = $pdo->prepare("
    SELECT id, attempted_by, progress, score
    FROM attempts
    WHERE delivery_id = :delivery_id AND progress > 100
    ORDER BY progress DESC
");
$stmt->execute(['delivery_id' => $delivery['id']]);
$problematicAttempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($problematicAttempts as $attempt) {
    echo "\nAttempt ID: {$attempt['id']}\n";
    echo "Taker ID: {$attempt['attempted_by']}\n";
    echo "Progress: {$attempt['progress']}%\n";
    echo "Score: {$attempt['score']}\n";

    // Count distinct answered questions
    $stmt2 = $pdo->prepare("
        SELECT COUNT(DISTINCT question_id) as answered_count
        FROM attempt_question
        WHERE attempt_id = :attempt_id
    ");
    $stmt2->execute(['attempt_id' => $attempt['id']]);
    $result = $stmt2->fetch(PDO::FETCH_ASSOC);
    $answeredQuestions = $result['answered_count'];

    echo "Questions answered: {$answeredQuestions}\n";
    echo "Total questions in exam: {$totalQuestionsInExam}\n";

    $calculatedProgress = $totalQuestionsInExam > 0 ? ceil($answeredQuestions * 100 / $totalQuestionsInExam) : 0;
    echo "Expected progress (recalculated): {$calculatedProgress}%\n";

    // Check for orphaned questions (answered but not in exam anymore)
    $stmt3 = $pdo->prepare("
        SELECT DISTINCT aq.question_id, q.question, q.item_id
        FROM attempt_question aq
        LEFT JOIN questions q ON aq.question_id = q.id
        WHERE aq.attempt_id = :attempt_id
    ");
    $stmt3->execute(['attempt_id' => $attempt['id']]);
    $answeredQuestionsList = $stmt3->fetchAll(PDO::FETCH_ASSOC);

    // Check which questions are still in the exam
    $orphanedCount = 0;
    $orphanedQuestions = [];

    foreach ($answeredQuestionsList as $aq) {
        if (!$aq['item_id']) {
            $orphanedCount++;
            $orphanedQuestions[] = [
                'id' => $aq['question_id'],
                'question' => '[DELETED QUESTION]',
                'item_id' => null
            ];
            continue;
        }

        // Check if this question's item is still in the exam
        $stmt4 = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM exam_item
            WHERE exam_id = :exam_id AND item_id = :item_id
        ");
        $stmt4->execute([
            'exam_id' => $delivery['exam_id'],
            'item_id' => $aq['item_id']
        ]);
        $result = $stmt4->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] == 0) {
            $orphanedCount++;
            $orphanedQuestions[] = [
                'id' => $aq['question_id'],
                'question' => $aq['question'],
                'item_id' => $aq['item_id']
            ];
        }
    }

    if ($orphanedCount > 0) {
        echo "⚠️  ORPHANED QUESTIONS FOUND: {$orphanedCount}\n";
        echo "These questions were answered but are no longer in the exam:\n";
        foreach ($orphanedQuestions as $oq) {
            $questionText = substr($oq['question'] ?? '[NO TEXT]', 0, 100);
            echo "  - Question ID {$oq['id']}: {$questionText}...\n";
            if ($oq['item_id']) {
                echo "    Item ID: {$oq['item_id']} (item removed from exam)\n";
            } else {
                echo "    [QUESTION DELETED FROM DATABASE]\n";
            }
        }
    }

    echo str_repeat("-", 80) . "\n";
}

echo "\n=== SUMMARY ===\n";
echo "Total attempts with progress > 100%: " . count($problematicAttempts) . "\n";

// Check all attempts
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total_attempts
    FROM attempts
    WHERE delivery_id = :delivery_id
");
$stmt->execute(['delivery_id' => $delivery['id']]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$totalAttempts = $result['total_attempts'];

echo "Total attempts in this delivery: {$totalAttempts}\n";
$percentage = $totalAttempts > 0 ? round(count($problematicAttempts) / $totalAttempts * 100, 2) : 0;
echo "Percentage with issue: {$percentage}%\n";
