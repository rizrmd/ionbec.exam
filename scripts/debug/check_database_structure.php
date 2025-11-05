<?php

/**
 * DATABASE STRUCTURE INVESTIGATION
 */

echo "=== DATABASE STRUCTURE INVESTIGATION ===\n\n";

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

    // Get all tables
    echo "=== AVAILABLE TABLES ===\n";
    $stmt = $pdo->prepare("
        SELECT table_name
        FROM information_schema.tables
        WHERE table_schema = 'public'
        ORDER BY table_name
    ");
    $stmt->execute();
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        echo "- $table\n";
    }

    echo "\n=== DELIVERY TABLES ===\n";
    $deliveryTables = array_filter($tables, function($table) {
        return strpos($table, 'delivery') !== false;
    });

    foreach ($deliveryTables as $table) {
        echo "- $table\n";
    }

    echo "\n=== EXAM TABLES ===\n";
    $examTables = array_filter($tables, function($table) {
        return strpos($table, 'exam') !== false;
    });

    foreach ($examTables as $table) {
        echo "- $table\n";
    }

    // Check specific table structures
    echo "\n=== DELIVERIES TABLE STRUCTURE ===\n";
    $stmt = $pdo->prepare("
        SELECT column_name, data_type, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'deliveries' AND table_schema = 'public'
        ORDER BY ordinal_position
    ");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $column) {
        echo "- {$column['column_name']}: {$column['data_type']} (nullable: {$column['is_nullable']})\n";
    }

    echo "\n=== DELIVERY SNAPSHOTS TABLE STRUCTURE ===\n";
    $stmt = $pdo->prepare("
        SELECT column_name, data_type, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'delivery_snapshots' AND table_schema = 'public'
        ORDER BY ordinal_position
    ");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $column) {
        echo "- {$column['column_name']}: {$column['data_type']} (nullable: {$column['is_nullable']})\n";
    }

    // Check if there's a pivot table for deliveries and items
    echo "\n=== LOOKING FOR ITEM-DELIVERY RELATIONSHIP ===\n";
    $possiblePivotTables = array_filter($tables, function($table) {
        return strpos($table, 'item') !== false && strpos($table, 'delivery') !== false;
    });

    if ($possiblePivotTables) {
        foreach ($possiblePivotTables as $table) {
            echo "Found pivot table: $table\n";
        }
    } else {
        echo "No item-delivery pivot table found\n";
        echo "Checking for other relationship patterns...\n";

        // Check if deliveries has items relationship through other means
        $stmt = $pdo->prepare("
            SELECT column_name, data_type
            FROM information_schema.columns
            WHERE table_name = 'deliveries'
            AND (column_name LIKE '%item%' OR column_name LIKE '%exam%')
        ");
        $stmt->execute();
        $deliveryColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($deliveryColumns as $column) {
            echo "- deliveries.{$column['column_name']}: {$column['data_type']}\n";
        }
    }

    // Check current delivery data
    echo "\n=== CURRENT DELIVERY (ID: 21) ===\n";
    $stmt = $pdo->prepare("SELECT * FROM deliveries WHERE id = 21");
    $stmt->execute();
    $delivery = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($delivery) {
        echo "Delivery found:\n";
        foreach ($delivery as $key => $value) {
            if ($key !== 'snapshot') { // Skip large BLOB data
                echo "- $key: $value\n";
            }
        }

        if (isset($delivery['snapshot'])) {
            $snapshotSize = strlen($delivery['snapshot']);
            echo "- snapshot: BLOB data ($snapshotSize bytes)\n";
        }
    } else {
        echo "Delivery 21 not found\n";
    }

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== INVESTIGATION COMPLETE ===\n";