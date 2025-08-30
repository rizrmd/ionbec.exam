<?php

echo "=== SIMPLE ATTEMPT CORRELATION IMPORT ===\n\n";

// Direct database connection without Laravel
$pgConn = pg_connect("host=107.155.75.50 port=5986 dbname=ionbec-new user=postgres password=6LP0Ojegy7IUU6kaX9lLkmZRUiAdAUNOltWyL3LegfYGR6rPQtB4DUSVqjdA78ES");
if (!$pgConn) {
    die("PostgreSQL connection failed\n");
}

$mysqlConn = new mysqli('107.155.75.50', 'mysql', 'S8Tz8c5ogcy6ZaSsXaoomwVTuDlLDBiIyWhdFGCLgH0nU3wDFEGUo3J9q5HnfiuK', 'default', 5654);
if ($mysqlConn->connect_error) {
    die("MySQL connection failed: " . $mysqlConn->connect_error . "\n");
}

// Get client ID
$result = pg_query($pgConn, "SELECT id FROM clients WHERE slug = 'ionbec' LIMIT 1");
$client = pg_fetch_assoc($result);
$clientId = $client['id'];
echo "Client ID: $clientId\n\n";

// Build taker map
echo "Building taker correlations...\n";
$takerMap = [];

// Get all legacy takers
$legacyTakers = $mysqlConn->query("SELECT id, name, email FROM takers");
while ($lt = $legacyTakers->fetch_assoc()) {
    // Try to find in imported
    $name = pg_escape_string($pgConn, $lt['name']);
    $result = pg_query($pgConn, "SELECT id FROM takers WHERE client_id = $clientId AND name = '$name' LIMIT 1");
    if ($row = pg_fetch_assoc($result)) {
        $takerMap[$lt['id']] = $row['id'];
    }
}
echo "Taker mappings: " . count($takerMap) . "\n";

// Build delivery map
echo "Building delivery correlations...\n";
$deliveryMap = [];

// Get all imported deliveries
$result = pg_query($pgConn, "SELECT id, exam_id, group_id FROM deliveries WHERE client_id = $clientId");
$importedDeliveries = [];
while ($row = pg_fetch_assoc($result)) {
    $importedDeliveries[] = $row;
}

// Get legacy deliveries and match
$legacyDeliveries = $mysqlConn->query("
    SELECT d.id, d.exam_id, d.group_id, e.code as exam_code, g.name as group_name
    FROM deliveries d
    JOIN exams e ON d.exam_id = e.id
    JOIN `groups` g ON d.group_id = g.id
");

while ($ld = $legacyDeliveries->fetch_assoc()) {
    // Find matching imported delivery
    $examCode = pg_escape_string($pgConn, $ld['exam_code']);
    $groupName = pg_escape_string($pgConn, $ld['group_name']);
    
    $result = pg_query($pgConn, "
        SELECT d.id 
        FROM deliveries d
        JOIN exams e ON d.exam_id = e.id
        JOIN groups g ON d.group_id = g.id
        WHERE d.client_id = $clientId 
        AND e.code = '$examCode'
        AND g.name = '$groupName'
        LIMIT 1
    ");
    
    if ($row = pg_fetch_assoc($result)) {
        $deliveryMap[$ld['id']] = $row['id'];
    }
}
echo "Delivery mappings: " . count($deliveryMap) . "\n\n";

// Import attempts
echo "Importing attempts...\n";
$legacyAttempts = $mysqlConn->query("SELECT * FROM attempts");
$total = $legacyAttempts->num_rows;
$imported = 0;
$skipped = 0;
$i = 0;

while ($attempt = $legacyAttempts->fetch_assoc()) {
    $i++;
    if ($i % 100 == 0) {
        echo "Progress: $i/$total (imported: $imported, skipped: $skipped)\n";
    }
    
    // Check mappings
    if (!isset($takerMap[$attempt['taker_id']]) || !isset($deliveryMap[$attempt['delivery_id']])) {
        $skipped++;
        continue;
    }
    
    $takerId = $takerMap[$attempt['taker_id']];
    $deliveryId = $deliveryMap[$attempt['delivery_id']];
    
    // Get exam_id from delivery
    $result = pg_query($pgConn, "SELECT exam_id FROM deliveries WHERE id = $deliveryId");
    $delivery = pg_fetch_assoc($result);
    $examId = $delivery['exam_id'];
    
    // Insert attempt
    $startedAt = $attempt['started_at'] ?: 'NULL';
    $endedAt = $attempt['ended_at'] ? "'" . $attempt['ended_at'] . "'" : 'NULL';
    $finishedAt = $attempt['finished_at'] ? "'" . $attempt['finished_at'] . "'" : 'NULL';
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
        '$startedAt', $endedAt, $finishedAt, 0,
        $score, 0, 0, false,
        $clientId, '$createdAt', '$updatedAt'
    )";
    
    if (pg_query($pgConn, $sql)) {
        $imported++;
    } else {
        $skipped++;
    }
}

echo "\n=== IMPORT COMPLETE ===\n";
echo "Total legacy attempts: $total\n";
echo "Successfully imported: $imported\n";
echo "Skipped: $skipped\n";
echo "Import rate: " . round(($imported/$total)*100, 1) . "%\n";

// Final count
$result = pg_query($pgConn, "SELECT COUNT(*) as count FROM attempts WHERE client_id = $clientId");
$row = pg_fetch_assoc($result);
echo "\nTotal attempts in database: " . $row['count'] . "\n";

pg_close($pgConn);
$mysqlConn->close();