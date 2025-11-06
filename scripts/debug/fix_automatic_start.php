<?php

echo "=== FIXING AUTOMATIC_START FOR DELIVERY 9OGqXvLw ===\n";

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

// 1. Show current configuration
echo "1. CURRENT CONFIGURATION\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->prepare("SELECT * FROM deliveries WHERE hash = '9OGqXvLw'");
$stmt->execute();
$delivery = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Delivery: {$delivery['name']} ({$delivery['hash']})\n";
echo "Current automatic_start: " . ($delivery['automatic_start'] ? 'TRUE' : 'FALSE') . "\n";
echo "Scheduled at: {$delivery['scheduled_at']}\n";
echo "Current time: " . (new DateTime('now', new DateTimeZone('Asia/Jakarta')))->format('Y-m-d H:i:s') . "\n\n";

// 2. Fix the automatic_start setting
echo "2. FIXING automatic_start SETTING\n";
echo str_repeat("-", 50) . "\n";

echo "Changing automatic_start from TRUE to FALSE...\n";

$stmt = $pdo->prepare("
    UPDATE deliveries
    SET automatic_start = FALSE,
        updated_at = NOW()
    WHERE hash = '9OGqXvLw'
");

$result = $stmt->execute();

if ($result && $stmt->rowCount() > 0) {
    echo "✅ Successfully updated automatic_start to FALSE\n";
    echo "   - Waiting room will now be enforced\n";
    echo "   - Users cannot start exam before 18:30\n";
    echo "   - All tokens will go to waiting room first\n\n";
} else {
    echo "❌ Failed to update automatic_start\n";
    exit(1);
}

// 3. Verify the change
echo "3. VERIFICATION\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->prepare("SELECT automatic_start FROM deliveries WHERE hash = '9OGqXvLw'");
$stmt->execute();
$updated = $stmt->fetch(PDO::FETCH_ASSOC);

echo "New automatic_start value: " . ($updated['automatic_start'] ? 'TRUE' : 'FALSE') . "\n";

// 4. Check existing attempts
echo "\n4. EXISTING ATTEMPTS ANALYSIS\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->prepare("
    SELECT COUNT(*) as total_attempts,
           COUNT(CASE WHEN started_at IS NOT NULL THEN 1 END) as started_attempts
    FROM attempts
    WHERE delivery_id = ?
");
$stmt->execute([$delivery['id']]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Total attempts: {$stats['total_attempts']}\n";
echo "Already started: {$stats['started_attempts']}\n";

if ($stats['started_attempts'] > 0) {
    echo "\n⚠️  WARNING: Some attempts have already started early!\n";
    echo "   Consider resetting these attempts or they will have unfair advantage.\n";
}

echo "\n=== FIX COMPLETE ===\n";
echo "\n📝 NEXT STEPS:\n";
echo "1. Test token 62Iqg - should go to waiting room now\n";
echo "2. Test token VSbXa - should go to waiting room if no attempt exists\n";
echo "3. All users should see waiting room until 18:30\n";
echo "4. Consider resetting early attempts for fairness\n";