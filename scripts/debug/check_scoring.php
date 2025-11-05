<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "🔍 INVESTIGATING SCORING SYSTEM\n";
echo "===============================\n\n";

// Test 1: Check attempts
echo "✅ Test 1: Attempt Records\n";
$attempts = \App\Models\Attempts\Attempt::all();
echo "   Found: " . $attempts->count() . " attempts\n";

foreach ($attempts as $attempt) {
    echo "   - Attempt ID: " . $attempt->id . ", Score: " . $attempt->score . ", Progress: " . $attempt->progress . "%\n";
}

echo "\n";

// Test 2: Check attempt questions
echo "✅ Test 2: Attempt Question Records\n";
$attemptQuestions = \App\Models\Attempts\AttemptQuestion::all();
echo "   Found: " . $attemptQuestions->count() . " answer records\n";

foreach ($attemptQuestions->take(5) as $answer) {
    echo "   - Attempt: " . $answer->attempt_id . ", Question: " . $answer->question_id .
         ", Score: " . $answer->score . ", Is Correct: " . ($answer->is_correct ? 'Yes' : 'No') . "\n";
}

echo "\n";

// Test 3: Check if queue worker is running
echo "✅ Test 3: Queue System Status\n";
try {
    $failedJobs = \DB::table('failed_jobs')->count();
    echo "   Failed jobs: " . $failedJobs . "\n";

    // Check recent job activity
    $recentJobs = \DB::table('jobs')->orderBy('id', 'desc')->take(3)->get();
    echo "   Recent jobs in queue: " . $recentJobs->count() . "\n";

    foreach ($recentJobs as $job) {
        echo "   - Job: " . substr($job->queue, 0, 50) . "...\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error checking queue: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: CalculateScore job test
echo "✅ Test 4: Test CalculateScore Job\n";
if ($attempts->count() > 0) {
    $attempt = $attempts->first();
    echo "   Testing with Attempt ID: " . $attempt->id . "\n";

    try {
        $job = new \App\Jobs\CalculateScore($attempt);
        echo "   CalculateScore job created successfully\n";

        // Manually run the job
        echo "   Running CalculateScore job...\n";
        $job->handle();

        // Refresh attempt to see updated score
        $attempt->refresh();
        echo "   Updated Score: " . $attempt->score . "\n";
        echo "   Updated Progress: " . $attempt->progress . "%\n";

    } catch (Exception $e) {
        echo "   ❌ CalculateScore job error: " . $e->getMessage() . "\n";
        echo "   Stack trace: " . $e->getTraceAsString() . "\n";
    }
} else {
    echo "   No attempts found to test\n";
}

echo "\n";

// Test 5: Check scoring events
echo "✅ Test 5: Scoring Events\n";
try {
    $events = \DB::table('scoring_events')->orderBy('created_at', 'desc')->take(3)->get();
    echo "   Recent scoring events: " . $events->count() . "\n";

    foreach ($events as $event) {
        echo "   - Event at: " . $event->created_at . "\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error checking events: " . $e->getMessage() . "\n";
}

echo "\n🎯 INVESTIGATION COMPLETE\n";
echo "========================\n";

if ($attemptQuestions->count() > 0) {
    echo "📊 FINDINGS:\n";
    echo "   - ✅ Answers are being stored in database\n";
    echo "   - ✅ AttemptQuestion records exist\n";

    if ($attempts->count() > 0) {
        $totalScore = $attempts->sum('score');
        $avgScore = $attempts->avg('score');
        echo "   - Total score across attempts: " . $totalScore . "\n";
        echo "   - Average score: " . number_format($avgScore, 2) . "\n";

        if ($totalScore == 0) {
            echo "   - ❌ ISSUE: All scores are 0 - CalculateScore job may not be running\n";
        }
    }
} else {
    echo "   - ❌ ISSUE: No answers found in database\n";
    echo "   - Answer submission may be failing\n";
}

echo "\n🔧 NEXT STEPS:\n";
echo "   1. Check if queue worker is running\n";
echo "   2. Verify CalculateScore job execution\n";
echo "   3. Test manual score calculation\n";
echo "   4. Check scoring event broadcasting\n";