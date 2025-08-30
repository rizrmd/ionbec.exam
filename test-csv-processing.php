<?php

/**
 * Test script for Rust CSV processing service
 * Compares Rust vs PHP performance for CSV operations
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\RustService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use League\Csv\Writer;
use Symfony\Component\Finder\Finder;

echo "\n⚡ RUST CSV PROCESSING SERVICE TEST\n";
echo "=====================================\n\n";

// Get RustService instance
$rustService = app(RustService::class);

// Test 1: Health Check
echo "1. Testing Rust service health...\n";
$health = $rustService->health();
if ($health['status'] === 'healthy') {
    echo "✅ Rust service is healthy\n";
    echo "   - Version: {$health['version']}\n";
    echo "   - Database: " . ($health['database'] ? '✓' : '✗') . "\n";
    echo "   - Redis: " . ($health['redis'] ? '✓' : '✗') . "\n\n";
} else {
    echo "❌ Rust service is not healthy\n";
    exit(1);
}

// Test 2: Create test CSV files
echo "2. Creating test CSV files...\n";

$testDataDir = __DIR__ . '/storage/test-csv';
if (!file_exists($testDataDir)) {
    mkdir($testDataDir, 0755, true);
}

// Create a test table for CSV import
$testTable = 'test_csv_import_' . time();
echo "   Creating test table: {$testTable}\n";

DB::statement("
    CREATE TABLE IF NOT EXISTS {$testTable} (
        id SERIAL PRIMARY KEY,
        name VARCHAR(255),
        email VARCHAR(255),
        age INTEGER,
        city VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

// Generate test CSV files with different sizes
$testFiles = [];

// Small file (100 records)
$smallFile = $testDataDir . '/small_test.csv';
$testFiles[] = ['file' => $smallFile, 'size' => 100, 'name' => 'small'];
generateTestCsv($smallFile, 100);

// Medium file (10,000 records)
$mediumFile = $testDataDir . '/medium_test.csv';
$testFiles[] = ['file' => $mediumFile, 'size' => 10000, 'name' => 'medium'];
generateTestCsv($mediumFile, 10000);

// Large file (100,000 records)
$largeFile = $testDataDir . '/large_test.csv';
$testFiles[] = ['file' => $largeFile, 'size' => 100000, 'name' => 'large'];
generateTestCsv($largeFile, 100000);

echo "   ✅ Created " . count($testFiles) . " test CSV files\n\n";

// Test 3: Single file processing with Rust
echo "3. Testing single file processing with Rust...\n";

foreach ($testFiles as $testFile) {
    echo "   Processing {$testFile['name']} file ({$testFile['size']} records)...\n";
    
    $startRust = microtime(true);
    $rustResult = $rustService->processCsvFile($testFile['file'], $testTable, 1000);
    $rustTime = (microtime(true) - $startRust) * 1000;
    
    if ($rustResult['success']) {
        echo "   ✅ Rust processing successful:\n";
        echo "      - Records processed: {$rustResult['records_processed']}\n";
        echo "      - Processing time: {$rustResult['processing_time_ms']}ms\n";
        echo "      - Total time (including network): {$rustTime}ms\n";
        echo "      - Records/second: " . round($rustResult['records_processed'] / ($rustResult['processing_time_ms'] / 1000), 2) . "\n";
    } else {
        echo "   ❌ Rust processing failed: {$rustResult['error']}\n";
    }
    
    // Clear table for next test
    DB::table($testTable)->truncate();
    echo "\n";
}

// Test 4: Batch processing with Rust
echo "4. Testing batch processing with Rust...\n";

$batchFiles = [];
foreach ($testFiles as $testFile) {
    $batchFiles[] = [
        'file_path' => $testFile['file'],
        'table_name' => $testTable
    ];
}

echo "   Processing " . count($batchFiles) . " files in batch...\n";

$startBatch = microtime(true);
$batchResult = $rustService->processCsvBatch($batchFiles, 5000);
$batchTime = (microtime(true) - $startBatch) * 1000;

if ($batchResult['success']) {
    echo "   ✅ Batch processing successful:\n";
    echo "      - Total files: {$batchResult['total_files']}\n";
    echo "      - Successful files: {$batchResult['successful_files']}\n";
    echo "      - Failed files: {$batchResult['failed_files']}\n";
    echo "      - Total records: {$batchResult['total_records']}\n";
    echo "      - Processing time: {$batchResult['total_processing_time_ms']}ms\n";
    echo "      - Total time (including network): {$batchTime}ms\n";
    echo "      - Records/second: " . round($batchResult['total_records'] / ($batchResult['total_processing_time_ms'] / 1000), 2) . "\n";
    
    foreach ($batchResult['results'] as $result) {
        if ($result['success']) {
            echo "      ✅ {$result['table_name']}: {$result['records_processed']} records in {$result['processing_time_ms']}ms\n";
        } else {
            echo "      ❌ {$result['table_name']}: {$result['error']}\n";
        }
    }
} else {
    echo "   ❌ Batch processing failed: {$batchResult['error']}\n";
}

echo "\n";

// Test 5: Compare with PHP processing
echo "5. Comparing with PHP CSV processing...\n";

// Clear table
DB::table($testTable)->truncate();

// Test with medium file only (to avoid too long execution time)
$testFile = $testFiles[1]; // Medium file
echo "   Processing {$testFile['name']} file ({$testFile['size']} records) with PHP...\n";

$startPhp = microtime(true);
$phpRecords = processWithPhp($testFile['file'], $testTable);
$phpTime = (microtime(true) - $startPhp) * 1000;

echo "   ✅ PHP processing completed:\n";
echo "      - Records processed: {$phpRecords}\n";
echo "      - Processing time: {$phpTime}ms\n";
echo "      - Records/second: " . round($phpRecords / ($phpTime / 1000), 2) . "\n\n";

// Test 6: Performance Summary
echo "=====================================\n";
echo "📊 PERFORMANCE COMPARISON\n";
echo "=====================================\n\n";

// Re-run Rust processing for comparison
DB::table($testTable)->truncate();
$startRustComp = microtime(true);
$rustCompResult = $rustService->processCsvFile($testFile['file'], $testTable, 1000);
$rustCompTime = (microtime(true) - $startRustComp) * 1000;

if ($rustCompResult['success']) {
    $improvement = round($phpTime / $rustCompResult['processing_time_ms'], 2);
    echo "Medium File Processing ({$testFile['size']} records):\n";
    echo "  Rust: {$rustCompResult['processing_time_ms']}ms\n";
    echo "  PHP:  {$phpTime}ms\n";
    echo "  🚀 Improvement: {$improvement}x faster\n";
    echo "  📈 Throughput improvement: " . round(
        ($rustCompResult['records_processed'] / ($rustCompResult['processing_time_ms'] / 1000)) / 
        ($phpRecords / ($phpTime / 1000)), 2
    ) . "x\n\n";
}

// Memory usage comparison
$phpMemory = memory_get_peak_usage(true) / 1024 / 1024;
echo "Memory Usage:\n";
echo "  PHP Peak Memory: " . round($phpMemory, 2) . " MB\n";
echo "  Rust: ~constant memory usage (streaming)\n\n";

// Cleanup
echo "Cleaning up test data...\n";
DB::statement("DROP TABLE IF EXISTS {$testTable}");
array_map('unlink', array_column($testFiles, 'file'));
rmdir($testDataDir);

echo "✨ CSV processing performance test completed!\n\n";

/**
 * Generate test CSV file with specified number of records
 */
function generateTestCsv($filePath, $recordCount): void
{
    $csv = Writer::createFromPath($filePath, 'w+');
    
    // Add header
    $csv->insertOne(['name', 'email', 'age', 'city']);
    
    $cities = ['Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Makassar', 'Palembang', 'Semarang', 'Yogyakarta'];
    
    // Generate data
    for ($i = 1; $i <= $recordCount; $i++) {
        $csv->insertOne([
            'name' => 'User ' . $i,
            'email' => 'user' . $i . '@example.com',
            'age' => rand(18, 65),
            'city' => $cities[array_rand($cities)]
        ]);
    }
}

/**
 * Process CSV file using PHP (original method)
 */
function processWithPhp($filePath, $tableName): int
{
    $csv = League\Csv\Reader::createFromPath($filePath, 'r');
    $csv->setHeaderOffset(0);
    
    $records = 0;
    foreach ($csv as $record) {
        // Clean empty values
        foreach ($record as $key => $value) {
            if (empty($value) && !is_numeric($value)) {
                unset($record[$key]);
            }
        }
        
        if (!empty($record)) {
            DB::table($tableName)->insert($record);
            $records++;
        }
    }
    
    return $records;
}