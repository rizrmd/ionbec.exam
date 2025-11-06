<?php

echo "=== CHECKING GROUP_TAKER PIVOT TABLE ===\n";

// Direct PDO connection
try {
    $pdo = new PDO(
        "pgsql:host=107.155.75.50;port=5986;dbname=ionbec-new",
        "postgres",
        "6LP0Ojegy7IUU6kaX9lLkmZRUiAdAUNOltWyL3LegfYGR6rPQtB4DUSVqjdA78ES"
    );
    echo "✓ Database connected\n\n";
} catch (PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Check group_taker table structure
echo "1. GROUP_TAKER TABLE STRUCTURE\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->prepare("
    SELECT column_name, data_type, is_nullable, column_default
    FROM information_schema.columns
    WHERE table_name = 'group_taker'
    ORDER BY ordinal_position
");
$stmt->execute();
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($columns as $column) {
    echo "  - {$column['column_name']}: {$column['data_type']} (nullable: {$column['is_nullable']})\n";
}

echo "\n";

// Check data in group_taker for group 2
echo "2. GROUP_TAKER DATA FOR GROUP 2 (BE 051125)\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->prepare("
    SELECT gt.*, t.name as taker_name
    FROM group_taker gt
    JOIN takers t ON gt.taker_id = t.id
    WHERE gt.group_id = 2
    ORDER BY gt.taker_id
    LIMIT 10
");
$stmt->execute();
$groupTakers = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($groupTakers as $gt) {
    echo "  - Taker ID: {$gt['taker_id']}, Name: {$gt['taker_name']}\n";
    echo "    → Taker Code: " . ($gt['taker_code'] ?? 'NULL') . "\n";
    echo "    → Created: {$gt['created_at']}\n\n";
}

// Test the getFormattedTakerCode method for first taker
echo "3. TESTING getFormattedTakerCode METHOD\n";
echo str_repeat("-", 50) . "\n";

require_once 'app/Traits/HasTakerCode.php';

// Test with first taker
$firstTakerId = $groupTakers[0]['taker_id'];
$groupId = 2;
$groupCode = 'BE 051125';

// Simulate the method logic
$takerCodeGenerated = \App\Traits\HasTakerCode::getFormattedTakerCode($firstTakerId, $groupId, $groupCode);

echo "Test Result:\n";
echo "  - Taker ID: $firstTakerId\n";
echo "  - Group ID: $groupId\n";
echo "  - Group Code: $groupCode\n";
echo "  - Generated Code: '$takerCodeGenerated'\n\n";

// Check if the issue is in database values vs generation
echo "4. CHECKING FOR DUPLICATE PATTERNS IN DATABASE\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->prepare("
    SELECT gt.taker_code, COUNT(*) as count
    FROM group_taker gt
    JOIN groups g ON gt.group_id = g.id
    WHERE g.code = 'BE 051125'
    GROUP BY gt.taker_code
    ORDER BY count DESC
");
$stmt->execute();
$codeCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Taker code patterns in database:\n";
foreach ($codeCounts as $codeCount) {
    echo "  - '{$codeCount['taker_code']}': {$codeCount['count']} occurrences\n";
}

echo "\n=== INVESTIGATION COMPLETE ===\n";