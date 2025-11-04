<?php

/**
 * ANALYZE MIXED EXAM ISSUE
 * Investigasi mengapa production logs menunjukkan Exam 73 & 74
 */

echo "=== ANALYZING MIXED EXAM ISSUE ===\n\n";

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

    // Check which delivery the user is actually using
    echo "=== DELIVERY 22 ACTUAL DETAILS ===\n";
    $stmt = $pdo->prepare("
        SELECT d.id, d.name, d.exam_id, d.duration,
               e.name as exam_name
        FROM deliveries d
        JOIN exams e ON d.exam_id = e.id
        WHERE d.id = 22
    ");
    $stmt->execute();
    $delivery = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($delivery) {
        echo "✅ Delivery 22 Details:\n";
        echo "  Name: {$delivery['name']}\n";
        echo "  Exam ID: {$delivery['exam_id']}\n";
        echo "  Exam Name: {$delivery['exam_name']}\n\n";
    } else {
        echo "❌ Delivery 22 not found\n\n";
        exit;
    }

    // Check current session/delivery being used in production
    echo "=== PRODUCTION DELIVERY IN USE ===\n";

    // Check which delivery is actually being used in recent logs
    $stmt = $pdo->prepare("
        SELECT d.id, d.name, d.exam_id, d.duration,
               e.name as exam_name
        FROM deliveries d
        JOIN exams e ON d.exam_id = e.id
        WHERE d.id = 20
    ");
    $stmt->execute();
    $prodDelivery = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($prodDelivery) {
        echo "🔍 Production Active Delivery:\n";
        echo "  Delivery ID: {$prodDelivery['id']}\n";
        echo "  Name: {$prodDelivery['name']}\n";
        echo "  Exam ID: {$prodDelivery['exam_id']}\n";
        echo "  Exam Name: {$prodDelivery['exam_name']}\n\n";

        echo "⚠️  ISSUE DETECTED:\n";
        echo "   - Delivery 22 uses Exam {$delivery['exam_id']} ({$delivery['exam_name']})\n";
        echo "   - Production logs show Exam {$prodDelivery['exam_id']} ({$prodDelivery['exam_name']})\n";
        echo "   - User may be accessing wrong delivery!\n\n";
    }

    // Check both exams' content
    $examIds = [73, 74];
    foreach ($examIds as $examId) {
        echo "=== EXAM $examId ANALYSIS ===\n";

        $stmt = $pdo->prepare("
            SELECT ei.order, ei.item_id,
                   i.id, i.title, i.hash, i.is_vignette, i.type,
                   COUNT(q.id) as question_count
            FROM exam_item ei
            JOIN items i ON ei.item_id = i.id
            LEFT JOIN questions q ON i.id = q.item_id
            WHERE ei.exam_id = ?
            GROUP BY ei.order, ei.item_id, i.id, i.title, i.hash, i.is_vignette, i.type
            ORDER BY ei.order
        ");
        $stmt->execute([$examId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalItems = count($items);
        $totalQuestions = 0;
        $vignetteCount = 0;
        $regularCount = 0;

        foreach ($items as $item) {
            $totalQuestions += $item['question_count'];
            if ($item['is_vignette']) {
                $vignetteCount++;
            } else {
                $regularCount++;
            }
        }

        echo "Exam $examId Summary:\n";
        echo "  Total Items: $totalItems\n";
        echo "  Total Questions: $totalQuestions\n";
        echo "  Vignette Items: $vignetteCount\n";
        echo "  Regular Items: $regularCount\n";

        // Show first 5 items as sample
        echo "  Sample Items:\n";
        foreach (array_slice($items, 0, 5) as $index => $item) {
            $itemType = $item['is_vignette'] ? 'VIGNETTE' : 'REGULAR';
            echo sprintf("    %d. %s - %s questions\n",
                ($index + 1),
                substr($item['title'], 0, 30),
                $item['question_count']
            );
        }
        echo "\n";
    }

    // Check hash "53gDGMky" ownership in detail
    echo "=== HASH '53gDGMky' OWNERSHIP ANALYSIS ===\n";
    $stmt = $pdo->prepare("
        SELECT ei.exam_id, ei.order, ei.item_id,
               e.name as exam_name,
               i.id, i.title, i.hash, i.is_vignette,
               COUNT(q.id) as question_count
        FROM exam_item ei
        JOIN items i ON ei.item_id = i.id
        JOIN exams e ON ei.exam_id = e.id
        LEFT JOIN questions q ON i.id = q.item_id
        WHERE i.hash = '53gDGMky'
        GROUP BY ei.exam_id, ei.order, ei.item_id, e.name, i.id, i.title, i.hash, i.is_vignette
        ORDER BY ei.exam_id, ei.order
    ");
    $stmt->execute();
    $hashOwnership = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($hashOwnership as $row) {
        echo "Hash 53gDGMky found in:\n";
        echo "  Exam {$row['exam_id']}: {$row['exam_name']}\n";
        echo "  Order: {$row['order']}\n";
        echo "  Item: {$row['title']}\n";
        echo "  Questions: {$row['question_count']}\n";
        echo "  Vignette: " . ($row['is_vignette'] ? 'YES' : 'NO') . "\n\n";
    }

    // Check current production session state
    echo "=== CURRENT PRODUCTION SESSION ANALYSIS ===\n";

    // Check what delivery is currently active in production
    $stmt = $pdo->prepare("
        SELECT d.id, d.name, d.exam_id, d.scheduled_at, d.ended_at,
               e.name as exam_name
        FROM deliveries d
        JOIN exams e ON d.exam_id = e.id
        WHERE d.id = 20
        ORDER BY d.id DESC
        LIMIT 5
    ");
    $stmt->execute();
    $activeDeliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($activeDeliveries as $del) {
        echo "Delivery {$del['id']}:\n";
        echo "  Name: {$del['name']}\n";
        echo "  Exam: {$del['exam_name']} (ID: {$del['exam_id']})\n";
        echo "  Scheduled: {$del['scheduled_at']}\n";
        echo "  Ended: " . ($del['ended_at'] ? $del['ended_at'] : 'Still Active') . "\n\n";
    }

    // Root Cause Analysis
    echo "=== ROOT CAUSE ANALYSIS ===\n";
    echo "Based on the investigation:\n\n";

    echo "1. DATABASE SITUATION:\n";
    echo "   - Delivery 22: Exam 74 (COBA MCQ) - 52 items, 55 questions\n";
    echo "   - Production logs show: Exam 73 (BE 051125 - MCQ) and Exam 74\n\n";

    echo "2. HASH OWNERSHIP:\n";
    echo "   - Hash '53gDGMky' (Vignette MCQ 9&10) exists in BOTH Exam 73 AND Exam 74\n";
    echo "   - This suggests duplicate items across exams\n\n";

    echo "3. FRONTEND API CALLS:\n";
    echo "   - Frontend is calling getQuestions API with correct hashes\n";
    "   - Backend returns valid data with questions\n";
    echo "   - But frontend shows blank\n\n";

    echo "4. LIKELY ISSUES:\n";
    echo "   a) User is accessing wrong delivery (maybe Delivery 20, not 22)\n";
    echo "   b) Frontend Vue.js state management issue\n";
    echo "   c) Template rendering condition problem\n";
    echo "   d) Loading state management interfering\n\n";

    echo "5. NEXT STEPS:\n";
    echo "   1. Verify which delivery the user is actually accessing\n";
    echo "   2. Check browser localStorage/exam state\n";
    echo "   3. Verify Vue.js reactive data flow\n";
    echo "   4. Check template rendering conditions\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== ANALYSIS COMPLETE ===\n";