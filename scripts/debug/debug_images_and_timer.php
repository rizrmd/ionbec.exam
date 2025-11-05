<?php

/**
 * DEBUG: Investigate image loading and timer issues
 */

echo "=== DEBUG: IMAGES & TIMER ISSUES ===\n\n";

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

    // Check for items with attachments
    echo "=== CHECKING ITEMS WITH ATTACHMENTS ===\n";
    $stmt = $pdo->prepare("SELECT id, title, hash FROM items WHERE id IN (SELECT DISTINCT item_id FROM attachments) ORDER BY id LIMIT 10");
    $stmt->execute();
    $itemsWithAttachments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Items with attachments: " . count($itemsWithAttachments) . "\n\n";

    foreach ($itemsWithAttachments as $item) {
        echo "Item ID: {$item['id']}, Hash: {$item['hash']}, Title: {$item['title']}\n";

        // Check attachments for this item
        $attachStmt = $pdo->prepare("SELECT id, filename, url, description FROM attachments WHERE item_id = ? ORDER BY id");
        $attachStmt->execute([$item['id']]);
        $attachments = $attachStmt->fetchAll(PDO::FETCH_ASSOC);

        echo "  Attachments (" . count($attachments) . "):\n";
        foreach ($attachments as $att) {
            echo "    - ID: {$att['id']}, File: {$att['filename']}, URL: {$att['url']}\n";
        }
        echo "\n";
    }

    // Check exam delivery settings for timer
    echo "=== CHECKING EXAM TIMER SETTINGS ===\n";
    $deliveryStmt = $pdo->prepare("
        SELECT d.id, d.name, d.duration, d.scheduled_at, d.ended_at, d.is_anytime, d.automatic_start,
               e.title as exam_title, e.duration as exam_duration
        FROM deliveries d
        LEFT JOIN exams e ON d.exam_id = e.id
        WHERE d.id = 21
    ");
    $deliveryStmt->execute();
    $delivery = $deliveryStmt->fetch(PDO::FETCH_ASSOC);

    if ($delivery) {
        echo "Delivery Settings:\n";
        echo "  ID: {$delivery['id']}\n";
        echo "  Name: {$delivery['name']}\n";
        echo "  Exam: {$delivery['exam_title']}\n";
        echo "  Delivery Duration: {$delivery['duration']} minutes\n";
        echo "  Exam Duration: {$delivery['exam_duration']} minutes\n";
        echo "  Scheduled: {$delivery['scheduled_at']}\n";
        echo "  Ended: {$delivery['ended_at']}\n";
        echo "  Is Anytime: " . ($delivery['is_anytime'] ? 'Yes' : 'No') . "\n";
        echo "  Auto Start: " . ($delivery['automatic_start'] ? 'Yes' : 'No') . "\n\n";

        // Calculate expected remaining time
        if ($delivery['scheduled_at']) {
            $scheduled = new DateTime($delivery['scheduled_at']);
            $now = new DateTime();
            $elapsed = $now->getTimestamp() - $scheduled->getTimestamp();
            $duration = ($delivery['duration'] ?: $delivery['exam_duration']) * 60; // Convert to seconds
            $remaining = max(0, $duration - $elapsed);

            echo "Time Calculation:\n";
            echo "  Scheduled: " . $scheduled->format('Y-m-d H:i:s') . "\n";
            echo "  Now: " . $now->format('Y-m-d H:i:s') . "\n";
            echo "  Elapsed: " . round($elapsed / 60, 1) . " minutes\n";
            echo "  Duration: " . ($delivery['duration'] ?: $delivery['exam_duration']) . " minutes\n";
            echo "  Remaining: " . round($remaining / 60, 1) . " minutes (" . $remaining . " seconds)\n\n";
        }
    }

    // Check specific question with images from logs
    echo "=== CHECKING SPECIFIC QUESTION WITH IMAGES ===\n";
    echo "Looking for question about 'femur radiograph and bone scan'...\n";

    $questionStmt = $pdo->prepare("
        SELECT q.id, q.question, q.item_id, i.title as item_title, i.hash as item_hash
        FROM questions q
        JOIN items i ON q.item_id = i.id
        WHERE q.question LIKE '%femur radiograph%' OR q.question LIKE '%bone scan%'
        LIMIT 5
    ");
    $questionStmt->execute();
    $questions = $questionStmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($questions) . " questions with femur/radiograph content:\n\n";

    foreach ($questions as $q) {
        echo "Question ID: {$q['id']}\n";
        echo "Item ID: {$q['item_id']}\n";
        echo "Item Title: {$q['item_title']}\n";
        echo "Item Hash: {$q['item_hash']}\n";
        echo "Question: " . substr($q['question'], 0, 100) . "...\n";

        // Check attachments for this item
        $attachStmt = $pdo->prepare("SELECT id, filename, url FROM attachments WHERE item_id = ?");
        $attachStmt->execute([$q['item_id']]);
        $attachments = $attachStmt->fetchAll(PDO::FETCH_ASSOC);

        echo "Attachments: " . count($attachments) . "\n";
        foreach ($attachments as $att) {
            echo "  - {$att['filename']} (URL: {$att['url']})\n";
        }
        echo "\n";
    }

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";