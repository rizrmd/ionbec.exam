<?php

/**
 * CHECK ATTEMPTS TABLE STRUCTURE
 */

echo "=== ATTEMPTS TABLE STRUCTURE ANALYSIS ===\n\n";

// Connect to database
$host = '107.155.75.50';
$port = '5986';
$dbname = 'ionbec-new';
$username = 'postgres';
$password = '6LP0Ojegy7IUU6kaX9lLkmZRUiAdAUNOltWyL3LegfYGR6rPQtB4DUSVqjdA78ES';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✅ Database connected\n\n";

    // Check attempts table structure
    echo "=== ATTEMPTS TABLE COLUMNS ===\n";
    $stmt = $pdo->prepare("
        SELECT column_name, data_type, is_nullable, column_default
        FROM information_schema.columns
        WHERE table_name = 'attempts' AND table_schema = 'public'
        ORDER BY ordinal_position
    ");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($columns) {
        foreach ($columns as $column) {
            echo "- {$column['column_name']}: {$column['data_type']} (nullable: {$column['is_nullable']})";
            if ($column['column_default']) {
                echo " (default: {$column['column_default']})";
            }
            echo "\n";
        }
    } else {
        echo "❌ Attempts table not found\n";
    }

    // Check if taker_id column exists
    echo "\n=== TAKER_ID COLUMN CHECK ===\n";
    $takerIdExists = false;
    foreach ($columns as $column) {
        if ($column['column_name'] === 'taker_id') {
            $takerIdExists = true;
            echo "✅ taker_id column exists\n";
            break;
        }
    }

    if (!$takerIdExists) {
        echo "❌ taker_id column does NOT exist\n";

        // Check what columns might be the taker identifier
        echo "\nPossible taker identifier columns:\n";
        foreach ($columns as $column) {
            if (strpos($column['column_name'], 'taker') !== false ||
                strpos($column['column_name'], 'user') !== false ||
                strpos($column['column_name'], 'student') !== false) {
                echo "- {$column['column_name']}: {$column['data_type']}\n";
            }
        }
    }

    // Check current attempts data
    echo "\n=== CURRENT ATTEMPTS DATA ===\n";
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_attempts FROM attempts");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total attempts: {$result['total_attempts']}\n";

    // Get sample attempts data
    $stmt = $pdo->prepare("SELECT * FROM attempts LIMIT 5");
    $stmt->execute();
    $attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($attempts) {
        echo "\nSample attempts data:\n";
        foreach ($attempts as $index => $attempt) {
            echo "\n--- Attempt " . ($index + 1) . " ---\n";
            foreach ($attempt as $key => $value) {
                if ($key !== 'snapshot') { // Skip large BLOB data
                    echo "$key: $value\n";
                }
            }
        }
    }

    // Check for recent attempts related to delivery 22
    echo "\n=== DELIVERY 22 ATTEMPTS ===\n";
    $stmt = $pdo->prepare("SELECT COUNT(*) as delivery_attempts FROM attempts WHERE delivery_id = 22");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Attempts for delivery 22: {$result['delivery_attempts']}\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== ANALYSIS COMPLETE ===\n";