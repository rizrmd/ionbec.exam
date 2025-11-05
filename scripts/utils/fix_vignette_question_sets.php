<?php

require_once "vendor/autoload.php";

$app = require_once "bootstrap/app.php";
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Exams\Item;
use App\Models\Exams\Question;
use App\Models\Exams\Answer;
use App\Knowledge\Exam\Item\ItemType;

echo "=== Fix Vignette Question Sets Script ===\n";
echo "This script will properly combine vignette question sets (1&2, 9&10, 19&20)\n\n";

// Define the vignette pairs that should be combined
$vignettePairs = [
    ['questions' => [1, 2], 'title' => 'BE051125 - MCQ 1 & 2'],
    ['questions' => [9, 10], 'title' => 'BE051125 - MCQ 9 & 10'],
    ['questions' => [19, 20], 'title' => 'BE051125 - MCQ 19 & 20']
];

// Load the JSON file to get question data
$jsonFile = __DIR__ . '/orthopedic_exam_questions.json';
$jsonData = json_decode(file_get_contents($jsonFile), true);

foreach ($vignettePairs as $pair) {
    $questionNumbers = $pair['questions'];
    $correctTitle = $pair['title'];

    echo "=== Processing Vignette Set: " . implode(' & ', $questionNumbers) . " ===\n";

    // Find existing combined item (if it exists)
    $existingItem = DB::table('items')
        ->where('title', $correctTitle)
        ->first();

    if ($existingItem) {
        echo "Found existing combined item: {$correctTitle} (ID: {$existingItem->id})\n";

        // Get current questions in the combined item
        $existingQuestions = DB::table('questions')
            ->where('item_id', $existingItem->id)
            ->orderBy('order')
            ->get();

        echo "Current questions in combined item: " . $existingQuestions->count() . "\n";

        // Find and delete incorrectly created separate items
        $deletedItems = 0;
        foreach ($questionNumbers as $qNum) {
            $wrongItem = DB::table('items')
                ->where('title', 'BE051125 - MCQ ' . $qNum)
                ->first();

            if ($wrongItem) {
                echo "Deleting incorrectly created separate item: BE051125 - MCQ {$qNum} (ID: {$wrongItem->id})\n";

                // Delete the wrong item and all its data (cascade delete)
                DB::table('items')->where('id', $wrongItem->id)->delete();
                $deletedItems++;
            }
        }

        echo "Deleted {$deletedItems} incorrect separate items\n";

        // Check if we need to add missing questions to the combined item
        $currentQuestionNumbers = [];
        foreach ($existingQuestions as $q) {
            // Extract question number from existing question text (approximate)
            $questionText = strtolower($q->question);
            foreach ($questionNumbers as $qNum) {
                $questionData = collect($jsonData['questions'])->firstWhere('question_number', $qNum);
                if ($questionData) {
                    $jsonQuestionText = strtolower($questionData['question']);
                    if (strpos($questionText, substr($jsonQuestionText, 0, 50)) !== false) {
                        $currentQuestionNumbers[] = $qNum;
                        break;
                    }
                }
            }
        }

        echo "Currently has questions: " . implode(', ', $currentQuestionNumbers) . "\n";

        // Add missing questions to the combined item
        foreach ($questionNumbers as $qNum) {
            if (!in_array($qNum, $currentQuestionNumbers)) {
                echo "Adding missing Question {$qNum} to combined item\n";

                $questionData = collect($jsonData['questions'])->firstWhere('question_number', $qNum);
                if ($questionData) {
                    try {
                        // Prepare question text
                        $questionText = $questionData['question'];
                        if (isset($questionData['vignette'])) {
                            if ($questionData['vignette'] === 'Same as question 1') {
                                $baseQNum = 1;
                            } elseif ($questionData['vignette'] === 'Same as question 9') {
                                $baseQNum = 9;
                            } elseif ($questionData['vignette'] === 'Same as question 19') {
                                $baseQNum = 19;
                            } else {
                                $baseQNum = $qNum;
                            }

                            $baseQuestion = collect($jsonData['questions'])->firstWhere('question_number', $baseQNum);
                            $vignetteText = $baseQuestion['vignette'];
                            $questionText = "<p style=\"text-align: justify\"><strong>Vignette:</strong> " . $vignetteText . "</p>\n\n<p style=\"text-align: justify\"><strong>Question:</strong> " . $questionData['question'] . "</p>";
                        } else {
                            $questionText = "<p style=\"text-align: justify\">" . $questionData['question'] . "</p>";
                        }

                        // Create question
                        $newQuestion = [
                            'item_id' => $existingItem->id,
                            'type' => 'multiple-choice',
                            'question' => $questionText,
                            'score' => 100,
                            'order' => $qNum,
                            'is_random' => false,
                            'client_id' => 3,
                            'created_at' => $existingItem->created_at,
                            'updated_at' => $existingItem->updated_at
                        ];

                        $questionId = DB::table('questions')->insertGetId($newQuestion);
                        echo "  Created question with ID: {$questionId}\n";

                        // Create answers
                        $options = $questionData['options'];
                        $correctAnswer = $questionData['correct_answer'];
                        $answerIndex = 0;

                        foreach ($options as $label => $optionText) {
                            $isCorrect = (strtolower($label) === strtolower($correctAnswer));

                            $answer = [
                                'question_id' => $questionId,
                                'answer' => "<p style=\"text-align: justify\">" . $optionText . "</p>",
                                'is_correct_answer' => $isCorrect,
                                'created_at' => $existingItem->created_at,
                                'updated_at' => $existingItem->updated_at
                            ];

                            DB::table('answers')->insert($answer);
                            echo "  Created answer " . ($answerIndex + 1) . ": " . ($isCorrect ? "CORRECT" : "incorrect") . "\n";
                            $answerIndex++;
                        }

                        echo "  SUCCESS: Question {$qNum} added to combined item\n";

                    } catch (Exception $e) {
                        echo "  ERROR adding Question {$qNum}: " . $e->getMessage() . "\n";
                    }
                }
            }
        }

    } else {
        echo "Creating new combined item: {$correctTitle}\n";

        // Create new combined item
        $baseDate = '2025-11-04 20:00:00';
        $baseQNum = min($questionNumbers);
        $creationDate = date('Y-m-d H:i:s', strtotime($baseDate . ' + ' . ($baseQNum - 1) . ' minutes'));

        try {
            $item = new Item();
            $item->title = $correctTitle;
            $item->type = ItemType::MULTIPLE_CHOICE;
            $item->client_id = 3;
            $item->is_vignette = false;
            $item->is_random = false;
            $item->score = 0;
            $item->created_at = $creationDate;
            $item->updated_at = $creationDate;
            $item->save();

            echo "Created combined item with ID: " . $item->id . "\n";

            // Add all questions to the combined item
            foreach ($questionNumbers as $index => $qNum) {
                $questionData = collect($jsonData['questions'])->firstWhere('question_number', $qNum);
                if ($questionData) {
                    echo "Adding Question {$qNum} to combined item\n";

                    // Prepare question text
                    $questionText = $questionData['question'];
                    if (isset($questionData['vignette'])) {
                        if ($questionData['vignette'] === 'Same as question 1') {
                            $baseQNum = 1;
                        } elseif ($questionData['vignette'] === 'Same as question 9') {
                            $baseQNum = 9;
                        } elseif ($questionData['vignette'] === 'Same as question 19') {
                            $baseQNum = 19;
                        } else {
                            $baseQNum = $qNum;
                        }

                        $baseQuestion = collect($jsonData['questions'])->firstWhere('question_number', $baseQNum);
                        $vignetteText = $baseQuestion['vignette'];
                        $questionText = "<p style=\"text-align: justify\"><strong>Vignette:</strong> " . $vignetteText . "</p>\n\n<p style=\"text-align: justify\"><strong>Question:</strong> " . $questionData['question'] . "</p>";
                    } else {
                        $questionText = "<p style=\"text-align: justify\">" . $questionData['question'] . "</p>";
                    }

                    // Create question
                    $question = new Question();
                    $question->item_id = $item->id;
                    $question->type = 'multiple-choice';
                    $question->question = $questionText;
                    $question->score = 100;
                    $question->order = $index + 1;
                    $question->is_random = false;
                    $question->client_id = 3;
                    $question->created_at = $creationDate;
                    $question->updated_at = $creationDate;
                    $question->save();

                    echo "  Created question with ID: " . $question->id . "\n";

                    // Create answers
                    $options = $questionData['options'];
                    $correctAnswer = $questionData['correct_answer'];

                    foreach ($options as $label => $optionText) {
                        $isCorrect = (strtolower($label) === strtolower($correctAnswer));

                        $answer = new Answer();
                        $answer->question_id = $question->id;
                        $answer->answer = "<p style=\"text-align: justify\">" . $optionText . "</p>";
                        $answer->is_correct_answer = $isCorrect;
                        $answer->created_at = $creationDate;
                        $answer->updated_at = $creationDate;
                        $answer->save();
                    }
                }
            }

            echo "SUCCESS: Combined item created with all questions\n";

            // Delete separate items
            foreach ($questionNumbers as $qNum) {
                $wrongItem = DB::table('items')
                    ->where('title', 'BE051125 - MCQ ' . $qNum)
                    ->first();

                if ($wrongItem) {
                    DB::table('items')->where('id', $wrongItem->id)->delete();
                    echo "Deleted separate item: BE051125 - MCQ {$qNum}\n";
                }
            }

        } catch (Exception $e) {
            echo "ERROR creating combined item: " . $e->getMessage() . "\n";
        }
    }

    echo "\n";
}

// Clear caches
echo "Clearing Laravel caches...\n";
shell_exec('php artisan cache:clear');
shell_exec('php artisan config:clear');
shell_exec('php artisan view:clear');
echo "Caches cleared.\n\n";

echo "=== Vignette fix completed! ===\n";