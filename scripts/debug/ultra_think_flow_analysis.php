<?php

/**
 * ULTRA-THINK INVESTIGATION: Complete Answer Flow Analysis
 *
 * Investigating the complete flow:
 * 1. User clicks answer → Green indicator appears ✅
 * 2. Answer is stored → localStorage + Database ✅
 * 3. User navigates away → next/prev/navigation
 * 4. User returns → Green indicator disappears ❌ (PROBLEM)
 *
 * Key questions to investigate:
 * - Where exactly is the answer stored?
 * - What key is used for storage?
 * - Is the same key used for retrieval?
 * - What happens during navigation vs initial page load?
 */

echo "=== ULTRA-THINK FLOW ANALYSIS ===\n\n";

// Connect to database to verify storage
$host = '107.155.75.50';
$port = '5986';
$dbname = 'ionbec-new';
$username = 'postgres';
$password = '6LP0Ojegy7IUU6kaX9lLkmZRUiAdAUNOltWyL3LegfYGR6rPQtB4DUSVqjdA78ES';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✅ Database connected\n\n";

    echo "=== STEP 1: UNDERSTANDING CURRENT STATE ===\n";
    echo "From logs, we know:\n";
    echo "- Backend sends correct data with item_hash\n";
    echo "- answerVal should be populated on page load\n";
    echo "- Template uses: answerVal[question.item_hash || question.hash]\n\n";

    echo "=== STEP 2: THE FLOW INVESTIGATION ===\n\n";

    echo "A. WHEN USER CLICKS ANSWER:\n";
    echo "1. selectAnswer() function called\n";
    echo "2. answerVal[storageKey] = answerHash\n";
    echo "3. submitAnswer() sends to backend\n";
    echo "4. addToStateArray(doneQuests, hashForMatching)\n";
    echo "5. localStorage updated\n\n";

    echo "B. WHEN USER NAVIGATES AWAY:\n";
    echo "1. getQuestions(index) called\n";
    echo "2. New question loaded via AJAX\n";
    echo "3. attempt questions processed for new question\n";
    echo "4. answerVal populated for new question only\n\n";

    echo "C. WHEN USER RETURNS:\n";
    echo "1. getQuestions(index) called again\n";
    echo "2. Same AJAX process as step B\n";
    echo "3. BUT: answerVal might be RESET or not properly loaded\n\n";

    echo "=== STEP 3: CRITICAL INSIGHTS ===\n\n";

    echo "🔍 POTENTIAL ISSUE 1: answerVal RESET\n";
    echo "Line 152 in Main.vue: answerVal.value = []\n";
    echo "This might reset answerVal every time getQuestions() is called!\n\n";

    echo "🔍 POTENTIAL ISSUE 2: STORAGE KEY MISMATCH\n";
    echo "When storing: Uses item_hash from current question\n";
    echo "When retrieving: Uses item_hash from AJAX response\n";
    echo "These might be different hashes!\n\n";

    echo "🔍 POTENTIAL ISSUE 3: TIMING ISSUE\n";
    echo "Template renders before answerVal is populated from attempt data\n\n";

    echo "=== STEP 4: INVESTIGATION PLAN ===\n\n";
    echo "1. Check if answerVal is being reset in getQuestions()\n";
    echo "2. Verify storage keys are consistent\n";
    echo "3. Check localStorage persistence\n";
    echo "4. Analyze the exact timing of answerVal population\n\n";

    echo "=== STEP 5: DATABASE VERIFICATION ===\n";
    echo "Checking current attempt data to understand storage pattern...\n";

    // Check current attempt data
    $stmt = $pdo->prepare("
        SELECT dq.answer_hash, dq.answer, i.hash as item_hash, q.hash as question_hash, i.title
        FROM attempt_questions dq
        JOIN questions q ON dq.question_id = q.id
        JOIN items i ON q.item_id = i.id
        WHERE dq.attempt_id = 223
        ORDER BY i.id
        LIMIT 10
    ");
    $stmt->execute();
    $attemptData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($attemptData) . " answered questions:\n\n";

    foreach ($attemptData as $data) {
        echo "Item: {$data['title']}\n";
        echo "  Item Hash: {$data['item_hash']}\n";
        echo "  Question Hash: {$data['question_hash']}\n";
        echo "  Answer Hash: {$data['answer_hash']}\n\n";
    }

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== ULTRA-THINK ANALYSIS COMPLETE ===\n";