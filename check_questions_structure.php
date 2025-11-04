<?php

/**
 * CHECK QUESTIONS TABLE STRUCTURE
 */

echo "=== CHECKING QUESTIONS TABLE STRUCTURE ===\n\n";

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

    // Check questions table structure
    echo "=== QUESTIONS TABLE COLUMNS ===\n";
    $stmt = $pdo->prepare("
        SELECT column_name, data_type, is_nullable, column_default
        FROM information_schema.columns
        WHERE table_name = 'questions'
        ORDER BY ordinal_position
    ");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $column) {
        echo "- {$column['column_name']}: {$column['data_type']}" .
             ($column['is_nullable'] === 'YES' ? ' (nullable)' : '') .
             ($column['column_default'] ? " (default: {$column['column_default']})" : '') . "\n";
    }
    echo "\n";

    // Check items table structure
    echo "=== ITEMS TABLE COLUMNS ===\n";
    $stmt = $pdo->prepare("
        SELECT column_name, data_type, is_nullable, column_default
        FROM information_schema.columns
        WHERE table_name = 'items'
        ORDER BY ordinal_position
    ");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $column) {
        echo "- {$column['column_name']}: {$column['data_type']}" .
             ($column['is_nullable'] === 'YES' ? ' (nullable)' : '') .
             ($column['column_default'] ? " (default: {$column['column_default']})" : '') . "\n";
    }
    echo "\n";

    // Get sample data from delivery 22
    echo "=== SAMPLE DATA FROM DELIVERY 22 ===\n";
    $stmt = $pdo->prepare("
        SELECT ei.exam_id, ei.item_id, ei.order,
               i.id, i.title, i.hash, i.is_vignette, i.content
        FROM exam_item ei
        JOIN items i ON ei.item_id = i.id
        WHERE ei.exam_id = (SELECT exam_id FROM deliveries WHERE id = 22)
        ORDER BY ei.order
        LIMIT 3
    ");
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as $index => $item) {
        echo "Item #" . ($index + 1) . "\n";
        echo "ID: {$item['id']}\n";
        echo "Hash: {$item['hash']}\n";
        echo "Title: " . substr($item['title'], 0, 50) . "...\n";
        echo "Is Vignette: " . ($item['is_vignette'] ? 'YES' : 'NO') . "\n";

        // Get questions for this item
        $stmt2 = $pdo->prepare("
            SELECT id, hash, question
            FROM questions
            WHERE item_id = ?
            ORDER BY id
        ");
        $stmt2->execute([$item['id']]);
        $questions = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        echo "Questions: " . count($questions) . "\n";
        foreach ($questions as $qIndex => $question) {
            echo "  " . ($qIndex + 1) . ". ID: {$question['id']}, Hash: {$question['hash']}\n";
            echo "     Question: " . substr(strip_tags($question['question']), 0, 80) . "...\n";
        }
        echo "\n";
    }

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== CHECK COMPLETE ===\n";