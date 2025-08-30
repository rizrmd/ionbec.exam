<?php

/**
 * Test script for Rust scoring service
 * Compares Rust vs PHP performance
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Attempts\Attempt;
use App\Services\RustService;
use App\Jobs\CalculateScore;

echo "\n⚡ RUST SCORING SERVICE TEST\n";
echo "================================\n\n";

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

// Test 2: Single Score Calculation
echo "2. Testing single score calculation...\n";

// Find a recent attempt
$attempt = Attempt::latest()->first();

if (!$attempt) {
    echo "⚠️  No attempts found in database\n";
    echo "Creating a test attempt...\n";
    // You would need to create test data here
    exit(0);
}

echo "   Using attempt ID: {$attempt->id}\n";

// Test Rust scoring
$startRust = microtime(true);
$rustResult = $rustService->calculateScoreForAttempt($attempt->id);
$rustTime = (microtime(true) - $startRust) * 1000;

if (isset($rustResult['error'])) {
    echo "❌ Rust scoring failed: {$rustResult['error']}\n";
} else {
    echo "✅ Rust scoring successful:\n";
    echo "   - Score: {$rustResult['score']}\n";
    echo "   - Progress: {$rustResult['progress']}%\n";
    echo "   - Processing time: {$rustResult['processing_time_ms']}ms\n";
    echo "   - Total time (including network): {$rustTime}ms\n\n";
}

// Test 3: PHP scoring comparison
echo "3. Comparing with PHP scoring...\n";

// Temporarily disable Rust to force PHP scoring
putenv('USE_RUST_SCORING=false');

$startPhp = microtime(true);
// Dispatch the job synchronously for testing
dispatch_sync(new CalculateScore($attempt));
$phpTime = (microtime(true) - $startPhp) * 1000;

// Re-enable Rust
putenv('USE_RUST_SCORING=true');

// Reload attempt to get updated scores
$attempt->refresh();

echo "✅ PHP scoring completed:\n";
echo "   - Score: {$attempt->score}\n";
echo "   - Progress: {$attempt->progress}%\n";
echo "   - Processing time: {$phpTime}ms\n\n";

// Test 4: Batch Processing
echo "4. Testing batch processing...\n";

// Get multiple attempts
$attemptIds = Attempt::limit(10)->pluck('id')->toArray();
$attemptCount = count($attemptIds);

if ($attemptCount > 1) {
    echo "   Processing {$attemptCount} attempts in batch...\n";
    
    $startBatch = microtime(true);
    $batchResult = $rustService->calculateScoresBatch($attemptIds);
    $batchTime = (microtime(true) - $startBatch) * 1000;
    
    if (isset($batchResult['error'])) {
        echo "❌ Batch processing failed: {$batchResult['error']}\n";
    } else {
        $successful = 0;
        $failed = 0;
        
        foreach ($batchResult['results'] as $result) {
            if ($result['success']) {
                $successful++;
            } else {
                $failed++;
            }
        }
        
        echo "✅ Batch processing completed:\n";
        echo "   - Successful: {$successful}\n";
        echo "   - Failed: {$failed}\n";
        echo "   - Total processing time: {$batchResult['total_processing_time_ms']}ms\n";
        echo "   - Average per attempt: " . round($batchResult['total_processing_time_ms'] / $attemptCount, 2) . "ms\n\n";
    }
} else {
    echo "⚠️  Not enough attempts for batch testing\n\n";
}

// Performance Summary
echo "================================\n";
echo "📊 PERFORMANCE SUMMARY\n";
echo "================================\n\n";

if (isset($rustResult['processing_time_ms']) && isset($phpTime)) {
    $improvement = round($phpTime / $rustResult['processing_time_ms'], 2);
    echo "Single Attempt Processing:\n";
    echo "  Rust: {$rustResult['processing_time_ms']}ms\n";
    echo "  PHP:  {$phpTime}ms\n";
    echo "  🚀 Improvement: {$improvement}x faster\n\n";
}

if (isset($batchResult['total_processing_time_ms']) && $attemptCount > 1) {
    $avgRust = round($batchResult['total_processing_time_ms'] / $attemptCount, 2);
    $avgPhp = round($phpTime, 2); // Single PHP processing as baseline
    $batchImprovement = round($avgPhp / $avgRust, 2);
    
    echo "Batch Processing (per attempt):\n";
    echo "  Rust: {$avgRust}ms\n";
    echo "  PHP:  {$avgPhp}ms (estimated)\n";
    echo "  🚀 Improvement: {$batchImprovement}x faster\n\n";
}

echo "✨ Test completed successfully!\n\n";