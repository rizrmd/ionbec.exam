<?php

/**
 * CHECK DATABASE SCHEMA
 */

echo "=== CHECKING DATABASE SCHEMA ===\n\n";

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

    // Check tables
    echo "=== CHECKING TABLES ===\n";
    $tables = ['items', 'questions', 'attachments', 'deliveries', 'exams'];

    foreach ($tables as $table) {
        echo "Table: $table\n";
        $stmt = $pdo->prepare("
            SELECT column_name, data_type, is_nullable, column_default
            FROM information_schema.columns
            WHERE table_name = ?
            ORDER BY ordinal_position
        ");
        $stmt->execute([$table]);
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($columns as $col) {
            echo "  - {$col['column_name']}: {$col['data_type']} " . ($col['is_nullable'] === 'YES' ? 'NULL' : 'NOT NULL') . "\n";
        }
        echo "\n";
    }

    // Check timer settings
    echo "=== CHECKING DELIVERY TIMER ===\n";
    $stmt = $pdo->prepare("SELECT * FROM deliveries WHERE id = 21");
    $stmt->execute();
    $delivery = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($delivery) {
        echo "Delivery ID 21:\n";
        foreach ($delivery as $key => $value) {
            echo "  $key: $value\n";
        }
    }

    echo "\n";

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== SCHEMA CHECK COMPLETE ===\n";