<?php

/**
 * TEST VIGNETTE QUESTION RETRIEVAL
 * Script untuk menguji apakah backend bisa mengambil data vignette questions
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST VIGNETTE QUESTION RETRIEVAL ===\n\n";

use App\Models\Deliveries\Delivery;
use App\Models\Exams\Exam;
use App\Models\Exams\Item;
use App\Models\Exams\Question;

try {
    // 1. Cari delivery 22
    echo "1. Mencari Delivery 22...\n";
    $delivery = Delivery::find(22);
    if (!$delivery) {
        echo "❌ Delivery 22 tidak ditemukan\n";
        exit;
    }
    echo "✅ Delivery 22 ditemukan: {$delivery->name}\n";
    echo "   Exam ID: {$delivery->exam_id}\n\n";

    // 2. Cari exam
    echo "2. Mencari Exam {$delivery->exam_id}...\n";
    $exam = $delivery->exam;
    if (!$exam) {
        echo "❌ Exam tidak ditemukan\n";
        exit;
    }
    echo "✅ Exam ditemukan: {$exam->name}\n\n";

    // 3. Cari semua items untuk exam ini
    echo "3. Mencari semua items di exam {$exam->id}...\n";
    $items = $exam->items()->withPivot('order')->orderBy('order')->get();
    echo "✅ Ditemukan {$items->count()} items\n\n";

    // 4. Cari items yang adalah vignette
    echo "4. Mencari items yang adalah vignette...\n";
    $vignetteItems = $items->filter(function($item) {
        return $item->is_vignette;
    });

    echo "✅ Ditemukan {$vignetteItems->count()} vignette items\n\n";

    if ($vignetteItems->count() === 0) {
        echo "❌ Tidak ada vignette items di exam ini\n";
        exit;
    }

    // 5. Untuk setiap vignette item, test retrieval
    foreach ($vignetteItems as $index => $vignetteItem) {
        echo "=== VIGNETTE ITEM #" . ($index + 1) . " ===\n";
        echo "Item ID: {$vignetteItem->id}\n";
        echo "Item Hash: {$vignetteItem->hash}\n";
        echo "Title: " . substr($vignetteItem->title, 0, 50) . "...\n";
        echo "Is Vignette: " . ($vignetteItem->is_vignette ? 'YES' : 'NO') . "\n";

        // 5a. Test query langsung ke database
        echo "\n5a. Test query langsung ke database:\n";
        $directQuestions = Question::withoutGlobalScope(\App\Scopes\ClientScope::class)
            ->where('item_id', $vignetteItem->id)
            ->with(['answers'])
            ->get();

        echo "   - Total questions di database: {$directQuestions->count()}\n";

        if ($directQuestions->count() > 0) {
            echo "   - Sample questions:\n";
            foreach ($directQuestions->take(3) as $qIndex => $question) {
                echo "     " . ($qIndex + 1) . ". ID: {$question->id}, Hash: {$question->hash}\n";
                echo "        Preview: " . substr(strip_tags($question->question), 0, 80) . "...\n";
                echo "        Answers: " . $question->answers->count() . "\n";
                echo "        Item Hash: " . ($question->item_hash ?: 'NULL') . "\n";
            }
        }

        // 5b. Test via getQuestions method (simulation seperti frontend)
        echo "\n5b. Test via getQuestions method (simulasi frontend):\n";

        // Simulasi session data
        $sessionData = [
            'exam' => $exam,
            'delivery' => $delivery,
            'taker' => (object) ['id' => 114] // Dummy taker
        ];

        // Cari item berdasarkan hash (seperti di getQuestions)
        $itemByHash = Item::withoutGlobalScope(\App\Scopes\ClientScope::class)
            ->where('hash', $vignetteItem->hash)
            ->first();

        if ($itemByHash) {
            echo "   - Item found by hash: {$itemByHash->id}\n";

            // Load questions dengan cara yang sama seperti getQuestions
            $questions = Question::withoutGlobalScope(\App\Scopes\ClientScope::class)
                ->where('item_id', $itemByHash->id)
                ->with(['answers'])
                ->get();

            echo "   - Questions loaded via hash lookup: {$questions->count()}\n";

            // Compare results
            if ($questions->count() !== $directQuestions->count()) {
                echo "   ⚠️  WARNING: Count mismatch! Direct: {$directQuestions->count()}, Hash lookup: {$questions->count()}\n";
            } else {
                echo "   ✅ Query results match\n";
            }

            // Test Rust API fallback simulation
            echo "\n5c. Test Rust API data structure (snapshot simulation):\n";
            if ($delivery->snapshot) {
                $snapshot = json_decode($delivery->snapshot, true);
                if ($snapshot && isset($snapshot['exam_structure']['items'])) {
                    $snapshotFound = false;
                    $snapshotQuestions = 0;

                    foreach ($snapshot['exam_structure']['items'] as $snapshotItem) {
                        if (($snapshotItem['hash'] ?? '') === $vignetteItem->hash) {
                            $snapshotFound = true;
                            $snapshotQuestions = count($snapshotItem['questions'] ?? []);
                            break;
                        }
                    }

                    if ($snapshotFound) {
                        echo "   - Found in snapshot with {$snapshotQuestions} questions\n";
                        if ($snapshotQuestions !== $questions->count()) {
                            echo "   ⚠️  WARNING: Snapshot mismatch! Snapshot: {$snapshotQuestions}, DB: {$questions->count()}\n";
                        } else {
                            echo "   ✅ Snapshot matches database\n";
                        }
                    } else {
                        echo "   ❌ Item not found in snapshot\n";
                    }
                }
            } else {
                echo "   - No snapshot available\n";
            }
        } else {
            echo "   ❌ Item not found by hash lookup\n";
        }

        echo "\n" . str_repeat("-", 60) . "\n\n";
    }

    // 6. Test complete API endpoint simulation
    echo "6. Test complete API endpoint simulation:\n";

    // Pilih vignette item pertama untuk test
    $testVignette = $vignetteItems->first();
    if ($testVignette) {
        echo "Testing with vignette item: {$testVignette->hash}\n";

        // Simulasi request ke endpoint /exam/questions/{item_hash}
        try {
            // Load item by hash
            $item = Item::withoutGlobalScope(\App\Scopes\ClientScope::class)
                ->where('hash', $testVignette->hash)
                ->first();

            if ($item) {
                // Load questions
                $questions = Question::withoutGlobalScope(\App\Scopes\ClientScope::class)
                    ->where('item_id', $item->id)
                    ->with(['answers'])
                    ->get();

                // Hide correct answers (seperti di production)
                $questions->each(function ($question, $questionKey) {
                    $questions[$questionKey]->answers->each(function ($answer, $answerKey) use ($questions, $questionKey) {
                        unset($questions[$questionKey]->answers[$answerKey]->is_correct_answer);
                    });
                });

                echo "✅ API simulation successful:\n";
                echo "   - Item: {$item->title}\n";
                echo "   - Questions returned: {$questions->count()}\n";
                echo "   - Response structure valid: " . ($questions->count() > 0 ? 'YES' : 'NO') . "\n";

                // Build sample response like API
                $apiResponse = [
                    'questions' => $questions->toArray(),
                    'attempt' => null
                ];

                echo "   - Sample API response size: " . strlen(json_encode($apiResponse)) . " bytes\n";

                // Verify each question has required fields
                $validQuestions = 0;
                foreach ($questions as $question) {
                    if (isset($question['question']) && isset($question['answers'])) {
                        $validQuestions++;
                    }
                }
                echo "   - Valid questions with complete data: {$validQuestions}/{$questions->count()}\n";

            } else {
                echo "❌ Item not found by hash in API simulation\n";
            }
        } catch (Exception $e) {
            echo "❌ API simulation failed: " . $e->getMessage() . "\n";
        }
    }

    echo "\n=== TEST COMPLETE ===\n";

} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}