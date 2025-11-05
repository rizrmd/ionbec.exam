<?php

require_once "vendor/autoload.php";

$app = require_once "bootstrap/app.php";
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Exams\Item;
use App\Models\Exams\Question;
use App\Models\Exams\Answer;
use App\Knowledge\Exam\Item\ItemType;

echo "=== Orthopedic Exam Questions Import Script ===\n";
echo "This script will import questions from orthopedic_exam_questions.json\n";
echo "that don't already exist in the database.\n\n";

// Load the JSON file
$jsonFile = __DIR__ . '/orthopedic_exam_questions.json';
if (!file_exists($jsonFile)) {
    echo "ERROR: orthopedic_exam_questions.json file not found!\n";
    exit(1);
}

$jsonData = json_decode(file_get_contents($jsonFile), true);
if (!$jsonData) {
    echo "ERROR: Failed to parse JSON file!\n";
    exit(1);
}

echo "Loaded JSON file with " . count($jsonData['questions']) . " questions\n\n";

// Sort questions by question_number to ensure proper chronological order
usort($jsonData['questions'], function($a, $b) {
    return $a['question_number'] - $b['question_number'];
});

echo "Questions sorted by number for proper chronological order\n\n";

// Get existing BE051125 questions from database
$existingQuestions = DB::table('items')
    ->where('title', 'LIKE', 'BE051125 - MCQ%')
    ->pluck('title')
    ->toArray();

echo "Found " . count($existingQuestions) . " existing BE051125 questions in database\n";
echo "Existing: " . implode(', ', $existingQuestions) . "\n\n";

// Update existing questions to maintain chronological order
echo "=== Updating existing questions for proper chronological order ===\n";
$existingItems = DB::table('items')
    ->where('title', 'LIKE', 'BE051125 - MCQ%')
    ->get();

$updateCount = 0;
foreach ($existingItems as $existingItem) {
    // Extract question number from title (e.g., "BE051125 - MCQ 60" -> 60)
    if (preg_match('/BE051125 - MCQ (\d+(?: & \d+)?)/', $existingItem->title, $matches)) {
        $questionNumStr = $matches[1];

        // Handle ranges like "1 & 2" -> use 1 for sorting
        if (strpos($questionNumStr, '&') !== false) {
            $questionNumber = (int)trim(explode('&', $questionNumStr)[0]);
        } else {
            $questionNumber = (int)$questionNumStr;
        }

        // Calculate proper creation date
        $baseDate = '2025-11-04 20:00:00';
        $creationDate = date('Y-m-d H:i:s', strtotime($baseDate . ' + ' . ($questionNumber - 1) . ' minutes'));

        // Update item timestamps
        DB::table('items')
            ->where('id', $existingItem->id)
            ->update([
                'created_at' => $creationDate,
                'updated_at' => $creationDate
            ]);

        // Update associated question timestamps
        DB::table('questions')
            ->where('item_id', $existingItem->id)
            ->update([
                'created_at' => $creationDate,
                'updated_at' => $creationDate
            ]);

        echo "Updated: " . $existingItem->title . " -> " . $creationDate . "\n";
        $updateCount++;
    }
}

echo "Updated {$updateCount} existing questions for proper ordering\n\n";

// Process each question
$importCount = 0;
$skipCount = 0;
$errorCount = 0;

foreach ($jsonData['questions'] as $questionData) {
    $questionNumber = $questionData['question_number'];
    $title = "BE051125 - MCQ " . $questionNumber;

    echo "Processing Question {$questionNumber}: {$title}\n";

    // Check if question already exists
    if (in_array($title, $existingQuestions)) {
        echo "  SKIPPING: Already exists in database\n";
        $skipCount++;
        continue;
    }

    try {
        // Start database transaction
        DB::beginTransaction();

        // Create the item/question set with proper chronological date
        $baseDate = '2025-11-04 20:00:00'; // Base date for MCQ 1
        $creationDate = date('Y-m-d H:i:s', strtotime($baseDate . ' + ' . ($questionNumber - 1) . ' minutes'));

        $item = new Item();
        $item->title = $title;
        $item->type = ItemType::MULTIPLE_CHOICE;
        $item->client_id = 3; // National Orthopaedic and Traumatology Board Examination
        $item->is_vignette = false;
        $item->is_random = false;
        $item->score = 0;
        $item->created_at = $creationDate;
        $item->updated_at = $creationDate;
        $item->save();

        echo "  Created item with ID: " . $item->id . "\n";

        // Prepare question text
        $questionText = $questionData['question'];
        if (isset($questionData['vignette'])) {
            $questionText = "<p style=\"text-align: justify\"><strong>Vignette:</strong> " . $questionData['vignette'] . "</p>\n\n<p style=\"text-align: justify\"><strong>Question:</strong> " . $questionData['question'] . "</p>";
        } else {
            $questionText = "<p style=\"text-align: justify\">" . $questionData['question'] . "</p>";
        }

        // Create the question with same creation date
        $question = new Question();
        $question->item_id = $item->id;
        $question->type = 'multiple-choice';
        $question->question = $questionText;
        $question->score = 100;
        $question->order = 1;
        $question->is_random = false;
        $question->client_id = 3;
        $question->created_at = $creationDate;
        $question->updated_at = $creationDate;
        $question->save();

        echo "  Created question with ID: " . $question->id . "\n";

        // Create answer options
        $options = $questionData['options'];
        $correctAnswer = $questionData['correct_answer'];
        $answerLabels = ['a', 'b', 'c', 'd', 'e'];
        $answerIndex = 0;

        foreach ($options as $label => $optionText) {
            $isCorrect = (strtolower($label) === strtolower($correctAnswer));

            $answer = new Answer();
            $answer->question_id = $question->id;
            $answer->answer = "<p style=\"text-align: justify\">" . $optionText . "</p>";
            $answer->is_correct_answer = $isCorrect;
            $answer->save();

            echo "  Created answer " . ($answerIndex + 1) . ": " . ($isCorrect ? "CORRECT" : "incorrect") . "\n";
            $answerIndex++;
        }

        // Commit the transaction
        DB::commit();

        echo "  SUCCESS: Question {$questionNumber} imported successfully\n";
        $importCount++;

    } catch (Exception $e) {
        DB::rollBack();
        echo "  ERROR: " . $e->getMessage() . "\n";
        $errorCount++;
    }

    echo "\n";
}

// Summary
echo "=== IMPORT SUMMARY ===\n";
echo "Total questions processed: " . count($jsonData['questions']) . "\n";
echo "Existing questions updated: {$updateCount}\n";
echo "Successfully imported: {$importCount}\n";
echo "Skipped (already exist): {$skipCount}\n";
echo "Errors: {$errorCount}\n";
echo "\n";

// Clear caches
echo "Clearing Laravel caches...\n";
shell_exec('php artisan cache:clear');
shell_exec('php artisan config:clear');
shell_exec('php artisan view:clear');
echo "Caches cleared.\n";

echo "Import script completed!\n";