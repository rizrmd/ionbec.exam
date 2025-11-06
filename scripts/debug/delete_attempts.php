<?php

echo "=== DELETING EARLY ATTEMPTS TO FORCE NEW ONES ===\n";

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

// 1. Show current attempts
echo "1. CURRENT ATTEMPTS TO DELETE\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->prepare("
    SELECT a.*, t.name as taker_name
    FROM attempts a
    JOIN takers t ON a.attempted_by = t.id
    WHERE a.delivery_id = (SELECT id FROM deliveries WHERE hash = '9OGqXvLw')
    ORDER BY a.created_at
");
$stmt->execute();
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($attempts) . " attempts:\n";
foreach ($attempts as $attempt) {
    echo "  - ID: {$attempt['id']}, Hash: {$attempt['hash']}\n";
    echo "    → Taker: {$attempt['taker_name']}\n";
    echo "    → Started: " . ($attempt['started_at'] ?? 'NOT STARTED') . "\n";
    echo "    → Created: {$attempt['created_at']}\n\n";
}

// 2. Delete attempt_questions first (foreign key)
echo "2. DELETING ATTEMPT_QUESTIONS\n";
echo str_repeat("-", 50) . "\n";

$deletedQuestions = 0;
foreach ($attempts as $attempt) {
    try {
        $stmt = $pdo->prepare("DELETE FROM attempt_questions WHERE attempt_id = ?");
        $stmt->execute([$attempt['id']]);
        $deletedQuestions += $stmt->rowCount();
    } catch (PDOException $e) {
        echo "Warning: Could not delete questions for attempt {$attempt['id']}: " . $e->getMessage() . "\n";
    }
}
echo "Deleted $deletedQuestions attempt question records\n\n";

// 3. Delete the attempts
echo "3. DELETING ATTEMPTS\n";
echo str_repeat("-", 50) . "\n";

$deletedAttempts = 0;
foreach ($attempts as $attempt) {
    try {
        $stmt = $pdo->prepare("DELETE FROM attempts WHERE id = ?");
        $stmt->execute([$attempt['id']]);

        if ($stmt->rowCount() > 0) {
            echo "✓ Deleted attempt {$attempt['id']} - {$attempt['taker_name']}\n";
            $deletedAttempts++;
        }
    } catch (PDOException $e) {
        echo "❌ Error deleting attempt {$attempt['id']}: " . $e->getMessage() . "\n";
    }
}

echo "\nDeleted $deletedAttempts attempts\n\n";

// 4. Verify deletion
echo "4. VERIFICATION\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->prepare("
    SELECT COUNT(*) as remaining
    FROM attempts
    WHERE delivery_id = (SELECT id FROM deliveries WHERE hash = '9OGqXvLw')
");
$stmt->execute();
$remaining = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Remaining attempts: {$remaining['remaining']}\n";

echo "\n=== DELETION COMPLETE ===\n";
echo "\n📝 NEXT STEPS:\n";
echo "1. Test token 62Iqg - should go to WAITING ROOM now\n";
echo "2. Test token VSbXa - should go to WAITING ROOM now\n";
echo "3. All users should see waiting room until 18:30\n";
echo "4. New attempts will be created when tokens are used\n";