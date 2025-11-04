<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Deliveries\Delivery;
use App\Models\Exams\Exam;
use App\Models\Exams\Item;
use App\Models\Exams\Question;

class DebugVignetteQuestions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:vignette-questions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test vignette question retrieval from backend';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("=== DEBUG VIGNETTE QUESTION RETRIEVAL ===\n");

        try {
            // 1. Cari delivery 22
            $this->info("1. Mencari Delivery 22...");
            $delivery = Delivery::find(22);
            if (!$delivery) {
                $this->error("❌ Delivery 22 tidak ditemukan");
                return 1;
            }
            $this->info("✅ Delivery 22 ditemukan: {$delivery->name}");
            $this->info("   Exam ID: {$delivery->exam_id}\n");

            // 2. Cari exam
            $this->info("2. Mencari Exam {$delivery->exam_id}...");
            $exam = $delivery->exam;
            if (!$exam) {
                $this->error("❌ Exam tidak ditemukan");
                return 1;
            }
            $this->info("✅ Exam ditemukan: {$exam->name}\n");

            // 3. Cari semua items untuk exam ini
            $this->info("3. Mencari semua items di exam {$exam->id}...");
            $items = $exam->items()->withPivot('order')->orderBy('order')->get();
            $this->info("✅ Ditemukan {$items->count()} items\n");

            // 4. Cari items yang adalah vignette
            $this->info("4. Mencari items yang adalah vignette...");
            $vignetteItems = $items->filter(function($item) {
                return $item->is_vignette;
            });

            $this->info("✅ Ditemukan {$vignetteItems->count()} vignette items\n");

            if ($vignetteItems->count() === 0) {
                $this->error("❌ Tidak ada vignette items di exam ini");
                return 1;
            }

            // 5. Untuk setiap vignette item, test retrieval
            foreach ($vignetteItems as $index => $vignetteItem) {
                $this->info("=== VIGNETTE ITEM #" . ($index + 1) . " ===");
                $this->info("Item ID: {$vignetteItem->id}");
                $this->info("Item Hash: {$vignetteItem->hash}");
                $this->info("Title: " . substr($vignetteItem->title, 0, 50) . "...");
                $this->info("Is Vignette: " . ($vignetteItem->is_vignette ? 'YES' : 'NO'));

                // 5a. Test query langsung ke database
                $this->info("\n5a. Test query langsung ke database:");
                $directQuestions = Question::withoutGlobalScope(\App\Scopes\ClientScope::class)
                    ->where('item_id', $vignetteItem->id)
                    ->with(['answers'])
                    ->get();

                $this->info("   - Total questions di database: {$directQuestions->count()}");

                if ($directQuestions->count() > 0) {
                    $this->info("   - Sample questions:");
                    foreach ($directQuestions->take(3) as $qIndex => $question) {
                        $this->info("     " . ($qIndex + 1) . ". ID: {$question->id}, Hash: {$question->hash}");
                        $this->info("        Preview: " . substr(strip_tags($question->question), 0, 80) . "...");
                        $this->info("        Answers: " . $question->answers->count());
                        $this->info("        Item Hash: " . ($question->item_hash ?: 'NULL'));
                    }
                }

                // 5b. Test via getQuestions method (simulation seperti frontend)
                $this->info("\n5b. Test via getQuestions method (simulasi frontend):");

                // Cari item berdasarkan hash (seperti di getQuestions)
                $itemByHash = Item::withoutGlobalScope(\App\Scopes\ClientScope::class)
                    ->where('hash', $vignetteItem->hash)
                    ->first();

                if ($itemByHash) {
                    $this->info("   - Item found by hash: {$itemByHash->id}");

                    // Load questions dengan cara yang sama seperti getQuestions
                    $questions = Question::withoutGlobalScope(\App\Scopes\ClientScope::class)
                        ->where('item_id', $itemByHash->id)
                        ->with(['answers'])
                        ->get();

                    $this->info("   - Questions loaded via hash lookup: {$questions->count()}");

                    // Compare results
                    if ($questions->count() !== $directQuestions->count()) {
                        $this->warn("   ⚠️  WARNING: Count mismatch! Direct: {$directQuestions->count()}, Hash lookup: {$questions->count()}");
                    } else {
                        $this->info("   ✅ Query results match");
                    }
                } else {
                    $this->error("   ❌ Item not found by hash lookup");
                }

                $this->info("\n" . str_repeat("-", 60) . "\n");
            }

            // 6. Test complete API endpoint simulation
            $this->info("6. Test complete API endpoint simulation:");

            // Pilih vignette item pertama untuk test
            $testVignette = $vignetteItems->first();
            if ($testVignette) {
                $this->info("Testing with vignette item: {$testVignette->hash}");

                // Simulasi request ke endpoint /exam/questions/{item_hash}
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

                    $this->info("✅ API simulation successful:");
                    $this->info("   - Item: {$item->title}");
                    $this->info("   - Questions returned: {$questions->count()}");
                    $this->info("   - Response structure valid: " . ($questions->count() > 0 ? 'YES' : 'NO'));

                    // Build sample response like API
                    $apiResponse = [
                        'questions' => $questions->toArray(),
                        'attempt' => null
                    ];

                    $this->info("   - Sample API response size: " . strlen(json_encode($apiResponse)) . " bytes");

                    // Verify each question has required fields
                    $validQuestions = 0;
                    foreach ($questions as $question) {
                        if (isset($question['question']) && isset($question['answers'])) {
                            $validQuestions++;
                        }
                    }
                    $this->info("   - Valid questions with complete data: {$validQuestions}/{$questions->count()}");

                } else {
                    $this->error("❌ Item not found by hash in API simulation");
                }
            }

            $this->info("\n=== TEST COMPLETE ===");

        } catch (Exception $e) {
            $this->error("❌ Test failed: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
