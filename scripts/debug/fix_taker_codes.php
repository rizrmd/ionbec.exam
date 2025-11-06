<?php

echo "=== FIXING TAKER CODE DUPLICATION ISSUE ===\n";

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

// First, let's see all problematic taker codes
echo "1. CURRENT TAKER CODES THAT NEED FIXING\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->prepare("
    SELECT gt.group_id, gt.taker_id, gt.taker_code, g.code as group_code, g.name as group_name, t.name as taker_name
    FROM group_taker gt
    JOIN groups g ON gt.group_id = g.id
    JOIN takers t ON gt.taker_id = t.id
    WHERE g.hash = '5bzO5NvE'
    ORDER BY gt.taker_id
");
$stmt->execute();
$takers = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($takers) . " takers to fix:\n";
$fixes = [];
foreach ($takers as $taker) {
    $currentCode = $taker['taker_code'];
    $takerName = $taker['taker_name'];

    // Extract the numeric part from current code
    if (preg_match('/(\d+)$/', trim($currentCode), $matches)) {
        $numericPart = $matches[1];
        $paddedNumeric = str_pad($numericPart, 3, '0', STR_PAD_LEFT);
        $correctCode = $paddedNumeric; // Should only be the numeric part

        $fixes[] = [
            'group_id' => $taker['group_id'],
            'taker_id' => $taker['taker_id'],
            'current_code' => $currentCode,
            'correct_code' => $correctCode,
            'taker_name' => $takerName
        ];

        echo "  - Taker: {$takerName} (ID: {$taker['taker_id']})\n";
        echo "    → Current: '{$currentCode}'\n";
        echo "    → Should be: '{$correctCode}'\n\n";
    }
}

// Confirm before proceeding
echo "2. CONFIRMATION\n";
echo str_repeat("-", 50) . "\n";
echo "This will update " . count($fixes) . " records in the group_taker table.\n";
echo "The taker codes will be changed from 'BE 051125 - XX' to just 'XXX'.\n";
echo "\nType 'yes' to continue: ";

$handle = fopen("php://stdin", "r");
$line = fgets($handle);
if (trim($line) !== 'yes') {
    echo "Operation cancelled.\n";
    exit(0);
}

// Apply the fixes
echo "\n3. APPLYING FIXES\n";
echo str_repeat("-", 50) . "\n";

$successCount = 0;
$errorCount = 0;

foreach ($fixes as $fix) {
    try {
        $stmt = $pdo->prepare("
            UPDATE group_taker
            SET taker_code = :new_code
            WHERE group_id = :group_id AND taker_id = :taker_id
        ");

        $stmt->execute([
            ':new_code' => $fix['correct_code'],
            ':group_id' => $fix['group_id'],
            ':taker_id' => $fix['taker_id']
        ]);

        if ($stmt->rowCount() > 0) {
            echo "  ✓ Updated taker {$fix['taker_name']}: '{$fix['current_code']}' → '{$fix['correct_code']}'\n";
            $successCount++;
        } else {
            echo "  ⚠ No rows affected for taker {$fix['taker_name']}\n";
            $errorCount++;
        }
    } catch (PDOException $e) {
        echo "  ✗ Error updating taker {$fix['taker_name']}: " . $e->getMessage() . "\n";
        $errorCount++;
    }
}

echo "\n4. VERIFICATION\n";
echo str_repeat("-", 50) . "\n";

// Verify the fixes
$stmt = $pdo->prepare("
    SELECT gt.taker_code, t.name
    FROM group_taker gt
    JOIN groups g ON gt.group_id = g.id
    JOIN takers t ON gt.taker_id = t.id
    WHERE g.hash = '5bzO5NvE'
    ORDER BY gt.taker_id
    LIMIT 10
");
$stmt->execute();
$verification = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Sample of fixed taker codes:\n";
foreach ($verification as $verify) {
    echo "  - {$verify['name']}: '{$verify['taker_code']}'\n";
}

echo "\n=== SUMMARY ===\n";
echo "Successfully updated: {$successCount} records\n";
echo "Errors: {$errorCount} records\n";
echo "\nFix completed! The taker codes should now display correctly as 'BE 051125 - XXX' instead of 'BE 051125-BE 051125 - XXX'.\n";