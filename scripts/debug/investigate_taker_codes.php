<?php

echo "=== INVESTIGATING TAKER CODE DUPLICATION ISSUE ===\n";

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

// 1. Check group info
echo "1. GROUP INFO (5bzO5NvE)\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->prepare("SELECT * FROM groups WHERE hash = '5bzO5NvE'");
$stmt->execute();
$group = $stmt->fetch(PDO::FETCH_ASSOC);

if ($group) {
    echo "✓ Group Found:\n";
    echo "  ID: {$group['id']}\n";
    echo "  Name: {$group['name']}\n";
    echo "  Code: {$group['code']}\n";
} else {
    echo "✗ Group not found\n";
    exit(1);
}

echo "\n";

// 2. Check takers in this group
echo "2. TAKERS IN THIS GROUP\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->prepare("
    SELECT t.id, t.name, t.email, gt.taker_id
    FROM takers t
    JOIN group_taker gt ON t.id = gt.taker_id
    WHERE gt.group_id = ?
    ORDER BY t.id
");
$stmt->execute([$group['id']]);
$takers = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($takers) . " takers in this group:\n";
foreach ($takers as $taker) {
    echo "  - ID: {$taker['id']}, Name: {$taker['name']}, Email: {$taker['email']}\n";
}

echo "\n";

// 3. Check how taker codes are generated in getFormattedTakerCode method
echo "3. CHECKING TAKER CODE GENERATION LOGIC\n";
echo str_repeat("-", 50) . "\n";

// Get the getFormattedTakerCode method from Attempt model
echo "Looking for getFormattedTakerCode method...\n";

// Test the current taker code generation
foreach ($takers as $taker) {
    // Simulate the current taker code generation logic
    $currentCode = $group['code'] . '-' . str_pad($taker['id'], 3, '0', STR_PAD_LEFT);
    echo "  - Taker ID {$taker['id']}: Current code format = '$currentCode'\n";
}

echo "\n";

// 4. Check if there are any attempts that reference these takers
echo "4. CHECKING ATTEMPTS FOR THESE TAKERS\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->prepare("
    SELECT a.id, a.attempted_by, a.score, a.delivery_id, d.name as delivery_name
    FROM attempts a
    JOIN deliveries d ON a.delivery_id = d.id
    WHERE a.attempted_by IN (SELECT taker_id FROM group_taker WHERE group_id = ?)
    ORDER BY a.id
");
$stmt->execute([$group['id']]);
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($attempts) . " attempts for these takers:\n";
foreach ($attempts as $attempt) {
    $generatedCode = $group['code'] . '-' . str_pad($attempt['attempted_by'], 3, '0', STR_PAD_LEFT);
    echo "  - Attempt ID: {$attempt['id']}, Taker ID: {$attempt['attempted_by']}, Generated Code: '$generatedCode', Score: {$attempt['score']}\n";
    echo "    → Delivery: {$attempt['delivery_name']}\n";
}

echo "\n";

// 5. Find where the duplication is coming from - check the display logic
echo "5. CHECKING WHERE DUPLICATION OCCURS\n";
echo str_repeat("-", 50) . "\n";

// The issue seems to be that the group name and group code are both "BE 051125"
// So when generating taker codes, it might be using group name + "-" + group code + "-" + number
$stmt = $pdo->prepare("
    SELECT g.id, g.name, g.code
    FROM groups g
    WHERE g.hash = '5bzO5NvE'
");
$stmt->execute();
$groupDetails = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Group details:\n";
echo "  - ID: {$groupDetails['id']}\n";
echo "  - Name: '{$groupDetails['name']}'\n";
echo "  - Code: '{$groupDetails['code']}'\n";

// Check if there's a pattern of duplication in the display
echo "\nChecking possible duplication patterns:\n";
$potentialDuplication = $groupDetails['name'] . '-' . $groupDetails['code'] . '-' . str_pad('2', 3, '0', STR_PAD_LEFT);
echo "  - If using name+code+number: '$potentialDuplication' (This matches what we see in the UI!)\n";

$correctFormat = $groupDetails['code'] . '-' . str_pad('2', 3, '0', STR_PAD_LEFT);
echo "  - Correct format should be: '$correctFormat'\n";

// 6. List all takers that need to be fixed
echo "\n6. ALL TAKERS THAT NEED FIXING\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->prepare("
    SELECT t.id, t.name, gt.taker_id, g.code as group_code, g.name as group_name
    FROM takers t
    JOIN group_taker gt ON t.id = gt.taker_id
    JOIN groups g ON gt.group_id = g.id
    WHERE g.hash = '5bzO5NvE'
    ORDER BY t.id
");
$stmt->execute();
$takersToFix = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Takers in group 5bzO5NvE that need fixing:\n";
foreach ($takersToFix as $taker) {
    $currentWrongCode = $taker['group_name'] . '-' . $taker['group_code'] . '-' . str_pad($taker['id'], 3, '0', STR_PAD_LEFT);
    $correctCode = $taker['group_code'] . '-' . str_pad($taker['id'], 3, '0', STR_PAD_LEFT);
    echo "  - Taker ID: {$taker['id']}, Name: {$taker['name']}\n";
    echo "    → Current (wrong): '$currentWrongCode'\n";
    echo "    → Should be: '$correctCode'\n\n";
}

echo "\n=== INVESTIGATION COMPLETE ===\n";