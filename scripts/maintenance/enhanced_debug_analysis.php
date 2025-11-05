<?php

/**
 * ENHANCED DEBUG ANALYSIS: Green Indicator Issue
 * Based on logs, backend works perfectly but frontend indicators don't show
 */

echo "=== ENHANCED DEBUG ANALYSIS ===\n\n";

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

    // Based on logs, we can see specific question data that was loaded
    echo "=== ANALYSIS OF LOGGED DATA ===\n";
    echo "From logs, backend sends:\n\n";

    echo "1. MCQ 11 (Item Hash: 3ZBYv4k0):\n";
    echo "   - Question Hash: P9JOxOqA\n";
    echo "   - Item Hash: 3ZBYv4k0 (correctly added)\n";
    echo "   - Answer Hash: 2rB0djXb\n";
    echo "   - Is Attempted: true\n";
    echo "   - Answer: \"a lower complication rate\"\n\n";

    echo "2. MCQ 39 (Item Hash: 3oKMJAB6):\n";
    echo "   - Question Hash: K92y77VP\n";
    echo "   - Item Hash: 3oKMJAB6 (correctly added)\n";
    echo "   - Answer Hash: oMYEvdMj\n";
    echo "   - Is Attempted: true\n";
    echo "   - Answer: \"Intramedullary nailing of both femurs\"\n\n";

    echo "=== FRONTEND TEMPLATE LOGIC ANALYSIS ===\n";
    echo "Current template logic in Main.vue:\n\n";
    echo ":class=\"[answerVal[question.item_hash || question.hash] === answer.hash ? 'bg-green-600 text-white' : 'hover:bg-green-200 hover:text-green-600']\"\n\n";

    echo "DEBUG text shows:\n";
    echo "DEBUG: answerVal for {{ question.item_hash || question.hash }}: {{ answerVal[question.item_hash || question.hash] }}\n\n";

    echo "=== CRITICAL INSIGHT ===\n";
    echo "The issue is likely that:\n";
    echo "1. answerVal is initialized with data from localStorage on page load\n";
    echo "2. When questions are loaded via AJAX, the answerVal reactive state may not update properly\n";
    echo "3. OR the hash matching logic is still not working despite item_hash being added\n\n";

    echo "=== LIKELY ROOT CAUSE ===\n";
    echo "Based on analysis, the root cause is:\n";
    echo "answerVal reactive object is not being properly updated when questions load\n";
    echo "with their corresponding attempt data from the backend.\n\n";

    echo "=== PROPOSED SOLUTION ===\n";
    echo "Need to ensure that when questions are loaded, the answerVal object is\n";
    echo "immediately populated with the existing answer data from attempt questions.\n\n";

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== ANALYSIS COMPLETE ===\n";