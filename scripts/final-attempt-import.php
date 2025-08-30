<?php

echo "=== FINAL ATTEMPT IMPORT WITH PROPER CORRELATIONS ===\n\n";

// Database connections
$pg = pg_connect("host=107.155.75.50 port=5986 dbname=ionbec-new user=postgres password=6LP0Ojegy7IUU6kaX9lLkmZRUiAdAUNOltWyL3LegfYGR6rPQtB4DUSVqjdA78ES connect_timeout=5");
$mysql = new mysqli('107.155.75.50', 'mysql', 'S8Tz8c5ogcy6ZaSsXaoomwVTuDlLDBiIyWhdFGCLgH0nU3wDFEGUo3J9q5HnfiuK', 'default', 5654);

if (!$pg || $mysql->connect_error) {
    die("Database connection failed\n");
}

$clientId = 16; // IONBEC client

echo "Step 1: Building taker correlations...\n";
$takerMap = [];

// Get all imported takers
$result = pg_query($pg, "SELECT id, name, email FROM takers WHERE client_id = $clientId");
$importedTakers = [];
while ($row = pg_fetch_assoc($result)) {
    $importedTakers[] = $row;
}

// Get all legacy takers and match
$legacyTakers = $mysql->query("SELECT id, name, email FROM takers");
while ($lt = $legacyTakers->fetch_assoc()) {
    foreach ($importedTakers as $it) {
        // Match by email first (if not null)
        if ($lt['email'] && $it['email']) {
            if (strcasecmp($lt['email'], $it['email']) == 0 || 
                strpos($it['email'], $lt['email']) === 0) {
                $takerMap[$lt['id']] = $it['id'];
                break;
            }
        }
        // Then match by exact name
        if ($lt['name'] == $it['name']) {
            $takerMap[$lt['id']] = $it['id'];
            break;
        }
    }
}

echo "Taker mappings created: " . count($takerMap) . "\n";

echo "\nStep 2: Building delivery correlations...\n";
$deliveryMap = [];

// Get all legacy deliveries with exam and group details
$legacyDeliveries = $mysql->query("
    SELECT 
        d.id as delivery_id,
        e.code as exam_code,
        g.name as group_name
    FROM deliveries d
    JOIN exams e ON d.exam_id = e.id
    JOIN `groups` g ON d.group_id = g.id
");

while ($ld = $legacyDeliveries->fetch_assoc()) {
    // Find matching imported delivery
    $examCode = pg_escape_string($pg, $ld['exam_code']);
    $groupName = pg_escape_string($pg, $ld['group_name']);
    
    $query = "
        SELECT d.id, d.exam_id
        FROM deliveries d
        JOIN exams e ON d.exam_id = e.id
        JOIN groups g ON d.group_id = g.id
        WHERE d.client_id = $clientId
        AND e.code = '$examCode'
        AND g.name = '$groupName'
        LIMIT 1
    ";
    
    $result = pg_query($pg, $query);
    if ($row = pg_fetch_assoc($result)) {
        $deliveryMap[$ld['delivery_id']] = [
            'delivery_id' => $row['id'],
            'exam_id' => $row['exam_id']
        ];
    }
}

echo "Delivery mappings created: " . count($deliveryMap) . "\n";

echo "\nStep 3: Importing attempts with correlations...\n";
$attempts = $mysql->query("SELECT * FROM attempts ORDER BY id");
$total = $attempts->num_rows;
$imported = 0;
$skipped = 0;
$errors = 0;

while ($attempt = $attempts->fetch_assoc()) {
    if (($imported + $skipped) % 200 == 0) {
        echo "Progress: " . ($imported + $skipped) . "/$total (imported: $imported, skipped: $skipped)\n";
    }
    
    // Check if we have both correlations
    if (!isset($takerMap[$attempt['taker_id']])) {
        $skipped++;
        continue; // No taker correlation
    }
    
    if (!isset($deliveryMap[$attempt['delivery_id']])) {
        $skipped++;
        continue; // No delivery correlation
    }
    
    // We have both correlations!
    $takerId = $takerMap[$attempt['taker_id']];
    $deliveryId = $deliveryMap[$attempt['delivery_id']]['delivery_id'];
    $examId = $deliveryMap[$attempt['delivery_id']]['exam_id'];
    
    // Prepare values
    $startedAt = $attempt['started_at'] ? "'{$attempt['started_at']}'" : 'NULL';
    $endedAt = $attempt['ended_at'] ? "'{$attempt['ended_at']}'" : 'NULL';
    $finishedAt = $attempt['finished_at'] ? "'{$attempt['finished_at']}'" : 'NULL';
    $score = $attempt['score'] ?: 0;
    $createdAt = $attempt['created_at'] ?: date('Y-m-d H:i:s');
    $updatedAt = $attempt['updated_at'] ?: date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO attempts (
        attempted_by, exam_id, delivery_id, ip_address,
        started_at, ended_at, finished_at, extra_minute,
        score, progress, penalty, finish_scoring,
        client_id, created_at, updated_at
    ) VALUES (
        $takerId, $examId, $deliveryId, '127.0.0.1',
        $startedAt, $endedAt, $finishedAt, 0,
        $score, 0, 0, false,
        $clientId, '$createdAt', '$updatedAt'
    )";
    
    if (@pg_query($pg, $sql)) {
        $imported++;
    } else {
        $errors++;
        // Uncomment to debug: echo "Error: " . pg_last_error($pg) . "\n";
    }
}

echo "\n=== IMPORT COMPLETE ===\n";
echo "Total legacy attempts: $total\n";
echo "Successfully imported: $imported (" . round(($imported/$total)*100, 1) . "%)\n";
echo "Skipped (no correlation): $skipped (" . round(($skipped/$total)*100, 1) . "%)\n";
echo "Errors: $errors\n";

// Verify final count
$result = pg_query($pg, "SELECT COUNT(*) as count FROM attempts WHERE client_id = $clientId");
$row = pg_fetch_assoc($result);
echo "\nFinal attempts in database: " . $row['count'] . "\n";

echo "\n=== CORRELATION ANALYSIS ===\n";
echo "Taker correlation rate: " . round((count($takerMap)/897)*100, 1) . "%\n";
echo "Delivery correlation rate: " . round((count($deliveryMap)/136)*100, 1) . "%\n";
echo "Expected max importable attempts: ~" . round($total * (count($takerMap)/897) * (count($deliveryMap)/136)) . "\n";

pg_close($pg);
$mysql->close();