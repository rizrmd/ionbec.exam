<?php

/**
 * DEBUG DELIVERY QUESTIONS RETRIEVAL
 * Simulate the controller logic to see what questions are being returned
 */

echo "=== DEBUGGING DELIVERY QUESTIONS RETRIEVAL ===\n\n";

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

    // Find delivery j3GlgkLD (ID: 151)
    $deliveryId = 151;
    $examId = 43;

    echo "=== SIMULATING getBaseDataDetail() METHOD ===\n";

    // Step 1: Get items and questions without ClientScope using direct DB query
    // This simulates the logic in lines 755-762 of DeliveryController
    $stmt = $pdo->prepare("
        SELECT i.*
        FROM items i
        JOIN exam_item ei ON i.id = ei.item_id
        WHERE ei.exam_id = ?
        ORDER BY ei.order
    ");
    $stmt->execute([$examId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "  Found " . count($items) . " items\n\n";

    $questions = [];

    foreach ($items as $item) {
        echo "  Item: {$item['title']} (ID: {$item['id']})\n";

        // Get questions for this item (simulating the eager load)
        $stmt = $pdo->prepare("SELECT * FROM questions WHERE item_id = ?");
        $stmt->execute([$item['id']]);
        $itemQuestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "    Questions: " . count($itemQuestions) . "\n";

        foreach ($itemQuestions as $question) {
            $questions[] = $question['id'];
            echo "      Q ID: {$question['id']} - " . substr(strip_tags($question['question']), 0, 50) . "...\n";
        }
        echo "\n";
    }

    echo "  Total questions collected: " . count($questions) . "\n";
    echo "  Question IDs: " . implode(', ', $questions) . "\n\n";

    // Step 2: Simulate the showQuestion method query
    echo "=== SIMULATING showQuestion() METHOD QUERY ===\n";

    if (!empty($questions)) {
        $placeholders = str_repeat('?,', count($questions) - 1) . '?';
        $stmt = $pdo->prepare("SELECT * FROM questions WHERE id IN ($placeholders)");
        $stmt->execute($questions);
        $retrievedQuestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "  Retrieved " . count($retrievedQuestions) . " questions from database\n\n";

        echo "=== RETRIEVED QUESTIONS DETAIL ===\n";
        foreach ($retrievedQuestions as $index => $question) {
            echo "  Question " . ($index + 1) . ":\n";
            echo "    ID: {$question['id']}\n";
            echo "    Item ID: {$question['item_id']}\n";
            echo "    Question: " . substr(strip_tags($question['question']), 0, 100) . "...\n";

            // Get item details
            $stmt = $pdo->prepare("SELECT title, type FROM items WHERE id = ?");
            $stmt->execute([$question['item_id']]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            echo "    Item Title: {$item['title']}\n";
            echo "    Item Type: {$item['type']}\n\n";
        }
    }

    // Step 3: Check for any potential duplication in the query results
    echo "=== DUPLICATION CHECK ===\n";
    $questionIds = [];
    $duplicates = [];

    foreach ($retrievedQuestions as $question) {
        if (in_array($question['id'], $questionIds)) {
            $duplicates[] = $question['id'];
        } else {
            $questionIds[] = $question['id'];
        }
    }

    if (!empty($duplicates)) {
        echo "  ❌ DUPLICATE QUESTION IDs FOUND: " . implode(', ', array_unique($duplicates)) . "\n";
    } else {
        echo "  ✅ No duplicate question IDs in retrieval\n";
    }

    // Step 4: Check if there are any issues with item-question relationships
    echo "\n=== ITEM-QUESTION RELATIONSHIP ANALYSIS ===\n";
    foreach ($items as $item) {
        echo "  Item: {$item['title']} (ID: {$item['id']})\n";

        // Count questions for this item
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM questions WHERE item_id = ?");
        $stmt->execute([$item['id']]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        echo "    Question count: $count\n";

        // Check if questions have identical content (possible duplication)
        $stmt = $pdo->prepare("SELECT question, COUNT(*) as count FROM questions WHERE item_id = ? GROUP BY question HAVING COUNT(*) > 1");
        $stmt->execute([$item['id']]);
        $duplicateContent = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($duplicateContent)) {
            echo "    ❌ DUPLICATE CONTENT FOUND:\n";
            foreach ($duplicateContent as $dup) {
                echo "      Content: " . substr(strip_tags($dup['question']), 0, 50) . "... (Count: {$dup['count']})\n";
            }
        } else {
            echo "    ✅ No duplicate content\n";
        }
        echo "\n";
    }

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== DELIVERY QUESTIONS DEBUGGING COMPLETE ===\n";