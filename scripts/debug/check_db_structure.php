<?php

/**
 * CHECK DATABASE TABLE STRUCTURE
 */

echo "=== CHECKING DATABASE TABLE STRUCTURE ===\n\n";

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

    // Check all tables
    echo "=== ALL TABLES IN DATABASE ===\n";
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
    echo "\n";

    // Look for exam-related tables
    echo "=== EXAM-RELATED TABLES ===\n";
    foreach ($tables as $table) {
        if (strpos($table, 'exam') !== false) {
            echo "- $table\n";

            // Show table structure
            $stmt = $pdo->prepare("
                SELECT column_name, data_type, is_nullable
                FROM information_schema.columns
                WHERE table_name = ?
                ORDER BY ordinal_position
            ");
            $stmt->execute([$table]);
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo "  Columns:\n";
            foreach ($columns as $column) {
                echo "    - {$column['column_name']}: {$column['data_type']}" .
                     ($column['is_nullable'] === 'YES' ? ' (nullable)' : '') . "\n";
            }
            echo "\n";
        }
    }

    // Check for delivery 22 and exam 74
    echo "=== DELIVERY 22 AND EXAM 74 ===\n";
    $stmt = $pdo->prepare("
        SELECT id, name, exam_id
        FROM deliveries
        WHERE id = 22
    ");
    $stmt->execute();
    $delivery = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($delivery) {
        echo "✅ Delivery 22 found:\n";
        echo "  Name: {$delivery['name']}\n";
        echo "  Exam ID: {$delivery['exam_id']}\n\n";

        // Check exam items for this exam
        echo "=== CHECKING HOW TO GET ITEMS FOR EXAM {$delivery['exam_id']} ===\n";

        // Try different approaches
        $approaches = [
            "SELECT * FROM exam_item WHERE exam_id = {$delivery['exam_id']} LIMIT 5",
            "SELECT * FROM exam_items WHERE exam_id = {$delivery['exam_id']} LIMIT 5",
            "SELECT * FROM exam_item ORDER BY id LIMIT 5",
            "SELECT * FROM exam_items ORDER BY id LIMIT 5"
        ];

        foreach ($approaches as $index => $query) {
            echo "Approach " . ($index + 1) . ": $query\n";
            try {
                $stmt = $pdo->prepare($query);
                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo "  ✅ Success: Found " . count($results) . " rows\n";
                if ($results) {
                    echo "  Sample columns: " . implode(', ', array_keys($results[0])) . "\n";
                }
            } catch (Exception $e) {
                echo "  ❌ Failed: " . $e->getMessage() . "\n";
            }
            echo "\n";
        }
    } else {
        echo "❌ Delivery 22 not found\n\n";
    }

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== CHECK COMPLETE ===\n";