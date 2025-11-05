<?php

require_once "vendor/autoload.php";

$app = require_once "bootstrap/app.php";
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Exams\Item;
use App\Models\Exams\Question;
use App\Models\Exams\Answer;
use App\Knowledge\Exam\Item\ItemType;

echo "=== Fix OSCE Questions Script ===\n";
echo "This script will fix all OSCE questions with complete vignettes and model answers\n\n";

// Load the OSCE JSON file
$jsonFile = __DIR__ . '/import_osce_question.json';
if (!file_exists($jsonFile)) {
    echo "ERROR: import_osce_question.json file not found!\n";
    exit(1);
}

$jsonData = json_decode(file_get_contents($jsonFile), true);
if (!$jsonData) {
    echo "ERROR: Failed to parse JSON file!\n";
    exit(1);
}

$osceStations = $jsonData['exam']['stations'];
echo "Loaded OSCE JSON with " . count($osceStations) . " stations\n\n";

// Get existing OSCE items
$existingOsceItems = DB::table('items')
    ->where('title', 'LIKE', 'BE051125 - OSCE%')
    ->orderBy('title')
    ->get();

echo "Found " . $existingOsceItems->count() . " existing OSCE items\n";

$updateCount = 0;
$fixCount = 0;

foreach ($osceStations as $station) {
    $stationNumber = $station['question_number'];
    $title = "BE051125 - OSCE " . $stationNumber;

    echo "=== Processing OSCE Station {$stationNumber}: {$title} ===\n";

    // Find existing OSCE item
    $existingItem = $existingOsceItems->firstWhere('title', $title);

    if ($existingItem) {
        echo "Found existing OSCE item (ID: {$existingItem->id})\n";

        // Get current questions
        $currentQuestions = DB::table('questions')
            ->where('item_id', $existingItem->id)
            ->orderBy('order')
            ->get();

        echo "Current questions: " . $currentQuestions->count() . "\n";

        // Build vignette content
        $vignetteContent = "";

        // Add main vignette
        if (isset($station['vignette'])) {
            $vignetteContent .= "<p style=\"text-align: justify\"><strong>Vignette:</strong> " . $station['vignette'] . "</p>\n\n";
        }

        // Add images if present
        if (isset($station['images']) && !empty($station['images'])) {
            $vignetteContent .= "<p style=\"text-align: justify\"><strong>Images:</strong></p>\n<ul style=\"text-align: justify\">\n";
            foreach ($station['images'] as $image) {
                $vignetteContent .= "<li>" . $image . "</li>\n";
            }
            $vignetteContent .= "</ul>\n\n";
        }

        // Add lab results if present
        if (isset($station['lab_results']) && $station['lab_results'] !== null) {
            $vignetteContent .= "<p style=\"text-align: justify\"><strong>Lab Results:</strong></p>\n<ul style=\"text-align: justify\">\n";
            foreach ($station['lab_results'] as $key => $value) {
                $vignetteContent .= "<li><strong>" . $key . ":</strong> " . $value . "</li>\n";
            }
            $vignetteContent .= "</ul>\n\n";
        }

        // Process each sub-question
        $subQuestions = $station['sub_questions'];
        $expectedQuestions = count($subQuestions);

        echo "Expected sub-questions: {$expectedQuestions}\n";

        foreach ($subQuestions as $index => $subQuestion) {
            $part = $subQuestion['part'];
            $questionText = $subQuestion['question_text'];
            $answerText = $subQuestion['answer'] ?? '';

            // Find corresponding question in database
            $dbQuestion = $currentQuestions->get($index);

            if ($dbQuestion) {
                // Build complete question text with vignette
                $completeQuestionText = $vignetteContent .
                    "<p style=\"text-align: justify\"><strong>Question " . strtoupper($part) . ":</strong> " . $questionText . "</p>";

                // Update question text
                DB::table('questions')
                    ->where('id', $dbQuestion->id)
                    ->update(['question' => $completeQuestionText]);

                echo "  Updated question {$dbQuestion->id} (Part {$part})\n";

                // Update or create answer
                $existingAnswer = DB::table('answers')
                    ->where('question_id', $dbQuestion->id)
                    ->first();

                $formattedAnswer = "<p style=\"text-align: justify\">" . nl2br($answerText) . "</p>";

                if ($existingAnswer) {
                    if (empty($existingAnswer->answer)) {
                        DB::table('answers')
                            ->where('id', $existingAnswer->id)
                            ->update(['answer' => $formattedAnswer]);
                        echo "    Updated empty answer for question {$dbQuestion->id}\n";
                        $fixCount++;
                    } else {
                        echo "    Answer already exists for question {$dbQuestion->id}\n";
                    }
                } else {
                    // Create new answer
                    $answerId = DB::table('answers')->insertGetId([
                        'question_id' => $dbQuestion->id,
                        'answer' => $formattedAnswer,
                        'is_correct_answer' => true, // Essay questions typically have model answers
                        'created_at' => $existingItem->created_at,
                        'updated_at' => $existingItem->updated_at
                    ]);
                    echo "    Created new answer {$answerId} for question {$dbQuestion->id}\n";
                    $fixCount++;
                }

                $updateCount++;
            } else {
                echo "  ERROR: Question not found for Part {$part}\n";
            }
        }

        // Update item to ensure it's marked as vignette and essay type
        DB::table('items')
            ->where('id', $existingItem->id)
            ->update([
                'type' => ItemType::ESSAY,
                'is_vignette' => true
            ]);

        echo "  Updated item type to essay and is_vignette = true\n";

    } else {
        echo "OSCE item not found: {$title}\n";
    }

    echo "\n";
}

// Also create missing OSCE stations that don't exist yet
echo "=== Checking for missing OSCE stations ===\n";

$existingStationNumbers = $existingOsceItems->map(function($item) {
    if (preg_match('/BE051125 - OSCE (\d+)/', $item->title, $matches)) {
        return (int)$matches[1];
    }
    return null;
})->filter()->sort()->values();

echo "Existing station numbers: " . implode(', ', $existingStationNumbers->toArray()) . "\n";

$missingStations = [];
for ($i = 1; $i <= 25; $i++) {
    if (!$existingStationNumbers->contains($i)) {
        $missingStations[] = $i;
    }
}

if (!empty($missingStations)) {
    echo "Missing stations: " . implode(', ', $missingStations) . "\n";

    foreach ($missingStations as $stationNumber) {
        $stationData = collect($osceStations)->firstWhere('question_number', $stationNumber);
        if ($stationData) {
            echo "Creating missing OSCE station {$stationNumber}\n";

            // Create new OSCE item
            $baseDate = '2025-11-04 22:00:00';
            $creationDate = date('Y-m-d H:i:s', strtotime($baseDate . ' + ' . ($stationNumber - 1) . ' minutes'));

            $title = "BE051125 - OSCE " . $stationNumber;

            try {
                // Create item
                $item = new Item();
                $item->title = $title;
                $item->type = ItemType::ESSAY;
                $item->client_id = 3;
                $item->is_vignette = true;
                $item->is_random = false;
                $item->score = 0;
                $item->created_at = $creationDate;
                $item->updated_at = $creationDate;
                $item->save();

                echo "  Created item with ID: " . $item->id . "\n";

                // Build vignette content
                $vignetteContent = "";

                if (isset($stationData['vignette'])) {
                    $vignetteContent .= "<p style=\"text-align: justify\"><strong>Vignette:</strong> " . $stationData['vignette'] . "</p>\n\n";
                }

                if (isset($stationData['images']) && !empty($stationData['images'])) {
                    $vignetteContent .= "<p style=\"text-align: justify\"><strong>Images:</strong></p>\n<ul style=\"text-align: justify\">\n";
                    foreach ($stationData['images'] as $image) {
                        $vignetteContent .= "<li>" . $image . "</li>\n";
                    }
                    $vignetteContent .= "</ul>\n\n";
                }

                if (isset($stationData['lab_results']) && $stationData['lab_results'] !== null) {
                    $vignetteContent .= "<p style=\"text-align: justify\"><strong>Lab Results:</strong></p>\n<ul style=\"text-align: justify\">\n";
                    foreach ($stationData['lab_results'] as $key => $value) {
                        $vignetteContent .= "<li><strong>" . $key . ":</strong> " . $value . "</li>\n";
                    }
                    $vignetteContent .= "</ul>\n\n";
                }

                // Create sub-questions
                foreach ($stationData['sub_questions'] as $index => $subQuestion) {
                    $part = $subQuestion['part'];
                    $questionText = $subQuestion['question_text'];
                    $answerText = $subQuestion['answer'] ?? '';

                    $completeQuestionText = $vignetteContent .
                        "<p style=\"text-align: justify\"><strong>Question " . strtoupper($part) . ":</strong> " . $questionText . "</p>";

                    // Create question
                    $question = new Question();
                    $question->item_id = $item->id;
                    $question->type = 'essay';
                    $question->question = $completeQuestionText;
                    $question->score = 100;
                    $question->order = $index + 1;
                    $question->is_random = false;
                    $question->client_id = 3;
                    $question->created_at = $creationDate;
                    $question->updated_at = $creationDate;
                    $question->save();

                    echo "    Created question with ID: " . $question->id . "\n";

                    // Create answer
                    $answer = new Answer();
                    $answer->question_id = $question->id;
                    $answer->answer = "<p style=\"text-align: justify\">" . nl2br($answerText) . "</p>";
                    $answer->is_correct_answer = true;
                    $answer->created_at = $creationDate;
                    $answer->updated_at = $creationDate;
                    $answer->save();

                    echo "    Created answer for question " . $question->id . "\n";
                    $fixCount++;
                }

                echo "  SUCCESS: OSCE Station {$stationNumber} created\n";

            } catch (Exception $e) {
                echo "  ERROR creating station {$stationNumber}: " . $e->getMessage() . "\n";
            }
        }
    }
} else {
    echo "All OSCE stations already exist\n";
}

// Clear caches
echo "Clearing Laravel caches...\n";
shell_exec('php artisan cache:clear');
shell_exec('php artisan config:clear');
shell_exec('php artisan view:clear');
echo "Caches cleared.\n\n";

echo "=== OSCE Fix Summary ===\n";
echo "Questions updated: {$updateCount}\n";
echo "Answers fixed/created: {$fixCount}\n";
echo "OSCE question fix completed!\n";