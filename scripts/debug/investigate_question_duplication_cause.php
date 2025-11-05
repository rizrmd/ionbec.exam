<?php

/**
 * INVESTIGATE QUESTION DUPLICATION CAUSE
 * Deep analysis of why duplicate questions exist in the database
 */

echo "=== INVESTIGATING QUESTION DUPLICATION CAUSE ===\n\n";

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

    // Focus on the problematic items
    $problematicItems = [
        ['id' => 1, 'title' => 'Test 01-0411'],
        ['id' => 3, 'title' => 'Test 03-0411'],
        ['id' => 723, 'title' => 'Test 04-0411']
    ];

    foreach ($problematicItems as $item) {
        echo "=== INVESTIGATING ITEM: {$item['title']} (ID: {$item['id']}) ===\n";

        // Get all questions for this item with detailed info
        $stmt = $pdo->prepare("
            SELECT id, question, created_at, updated_at, hash
            FROM questions
            WHERE item_id = ?
            ORDER BY id
        ");
        $stmt->execute([$item['id']]);
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "  Total questions: " . count($questions) . "\n";

        foreach ($questions as $index => $question) {
            echo "    Question " . ($index + 1) . " (ID: {$question['id']}):\n";
            echo "      Hash: " . ($question['hash'] ?? 'NULL') . "\n";
            echo "      Created: " . ($question['created_at'] ?? 'NULL') . "\n";
            echo "      Updated: " . ($question['updated_at'] ?? 'NULL') . "\n";
            echo "      Content: " . substr(strip_tags($question['question']), 0, 100) . "...\n";
            echo "\n";
        }

        // Check if questions have different created_at timestamps
        if (count($questions) > 1) {
            echo "  📅 CREATION ANALYSIS:\n";
            $firstCreated = $questions[0]['created_at'];
            $secondCreated = $questions[1]['created_at'];

            if ($firstCreated && $secondCreated) {
                $diff = strtotime($secondCreated) - strtotime($firstCreated);
                echo "    Time difference: " . ($diff > 0 ? "+{$diff} seconds" : "{$diff} seconds") . "\n";

                if ($diff > 0 && $diff < 3600) { // Less than 1 hour
                    echo "    ⚠️  Questions created within 1 hour - possible duplicate creation\n";
                } elseif ($diff > 86400) { // More than 1 day
                    echo "    ⚠️  Questions created on different days - possible manual copy\n";
                }
            }
            echo "\n";
        }

        // Check for answer options
        echo "  🔘 ANSWER OPTIONS ANALYSIS:\n";
        foreach ($questions as $index => $question) {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count,
                       array_agg(answer ORDER BY id) as answers
                FROM answers
                WHERE question_id = ?
            ");
            $stmt->execute([$question['id']]);
            $answerInfo = $stmt->fetch(PDO::FETCH_ASSOC);

            echo "    Question " . ($index + 1) . " has {$answerInfo['count']} answers\n";
            if ($answerInfo['count'] > 0 && $answerInfo['answers']) {
                $answers = json_decode($answerInfo['answers']);
                if (is_array($answers)) {
                    echo "      First few answers: " . implode(', ', array_slice($answers, 0, 2)) . "...\n";
                }
            }
        }
        echo "\n";
    }

    // Check exam-item relationship
    echo "=== EXAM-ITEM RELATIONSHIP ANALYSIS ===\n";
    $examId = 43;

    $stmt = $pdo->prepare("
        SELECT i.id, i.title, ei.order, i.created_at
        FROM items i
        JOIN exam_item ei ON i.id = ei.item_id
        WHERE ei.exam_id = ?
        ORDER BY ei.order
    ");
    $stmt->execute([$examId]);
    $examItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "  Exam ID: $examId\n";
    echo "  Items in exam: " . count($examItems) . "\n\n";

    foreach ($examItems as $item) {
        echo "    Item: {$item['title']} (ID: {$item['id']})\n";
        echo "      Order in exam: {$item['order']}\n";
        echo "      Item created: " . ($item['created_at'] ?? 'NULL') . "\n\n";
    }

    // Check for any logs or audit trails
    echo "=== ACTIVITY LOG ANALYSIS ===\n";
    $stmt = $pdo->prepare("
        SELECT al.log_name, al.description, al.created_at, u.name as user_name
        FROM activity_logs al
        LEFT JOIN users u ON al.causer_id = u.id
        WHERE al.description ILIKE '%question%'
           OR al.description ILIKE '%item%'
           OR al.log_name ILIKE '%question%'
           OR al.log_name ILIKE '%item%'
        ORDER BY al.created_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($logs)) {
        echo "  Recent question/item activities:\n";
        foreach ($logs as $log) {
            echo "    " . ($log['created_at'] ?? 'NULL') . " - " . ($log['user_name'] ?? 'Unknown') . "\n";
            echo "      {$log['log_name']}: {$log['description']}\n\n";
        }
    } else {
        echo "  ❌ No recent question/item activity logs found\n\n";
    }

    // Check database constraints and unique indexes
    echo "=== DATABASE CONSTRAINTS ANALYSIS ===\n";
    $stmt = $pdo->query("
        SELECT tc.table_name, tc.constraint_name, tc.constraint_type,
               kcu.column_name
        FROM information_schema.table_constraints tc
        JOIN information_schema.key_column_usage kcu
             ON tc.constraint_name = kcu.constraint_name
        WHERE tc.table_name IN ('questions', 'items', 'exam_item')
          AND tc.constraint_type = 'UNIQUE'
        ORDER BY tc.table_name, tc.constraint_name
    ");
    $constraints = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($constraints)) {
        echo "  Unique constraints found:\n";
        foreach ($constraints as $constraint) {
            echo "    Table: {$constraint['table_name']}, Column: {$constraint['column_name']}, Constraint: {$constraint['constraint_name']}\n";
        }
    } else {
        echo "  ⚠️  No unique constraints found on questions table - this could allow duplicates\n";
    }
    echo "\n";

    // Check for migration files or version info
    echo "=== MIGRATION PATTERNS ANALYSIS ===\n";
    $stmt = $pdo->query("
        SELECT table_name, column_name, data_type, is_nullable, column_default
        FROM information_schema.columns
        WHERE table_name = 'questions'
        ORDER BY ordinal_position
    ");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "  Questions table structure:\n";
    foreach ($columns as $column) {
        echo "    {$column['column_name']}: {$column['data_type']} " .
             ($column['is_nullable'] === 'YES' ? '(NULLABLE)' : '(NOT NULL)') .
             ($column['column_default'] ? " DEFAULT {$column['column_default']}" : "") . "\n";
    }
    echo "\n";

    // Check for potential data import patterns
    echo "=== DATA IMPORT PATTERN ANALYSIS ===\n";

    // Check if questions have sequential IDs (possible bulk insert)
    foreach ($problematicItems as $item) {
        $stmt = $pdo->prepare("
            SELECT id, created_at
            FROM questions
            WHERE item_id = ?
            ORDER BY id
        ");
        $stmt->execute([$item['id']]);
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($questions) >= 2) {
            $idDiff = $questions[1]['id'] - $questions[0]['id'];
            echo "  Item '{$item['title']}' question ID difference: $idDiff\n";

            if ($idDiff === 1) {
                echo "    ⚠️  Sequential IDs - possible bulk import or rapid creation\n";
            }
        }
    }
    echo "\n";

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== QUESTION DUPLICATION CAUSE INVESTIGATION COMPLETE ===\n";