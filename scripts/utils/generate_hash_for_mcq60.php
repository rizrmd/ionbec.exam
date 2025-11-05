<?php

require_once "vendor/autoload.php";

$app = require_once "bootstrap/app.php";
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Generating hashes for BE051125 - MCQ 60 only...\n";

// Get our specific question
$questionId = 1077;
$itemId = 734;

// Generate hash for the question
$question = DB::table('questions')->where('id', $questionId)->first();
if ($question && !$question->hash) {
    // Use the Laravel model to generate proper hash
    $questionModel = \App\Models\Exams\Question::find($questionId);
    if ($questionModel) {
        $hash = $questionModel->hash;
        DB::table('questions')->where('id', $questionId)->update(['hash' => $hash]);
        echo "✓ Generated hash for question $questionId: $hash\n";
    }
} else {
    echo "Question $questionId already has hash or not found\n";
}

// Generate hashes for the answers
$answerIds = [105, 106, 107, 108, 109];
foreach ($answerIds as $answerId) {
    $answer = DB::table('answers')->where('id', $answerId)->first();
    if ($answer && !$answer->hash) {
        $answerModel = \App\Models\Exams\Answer::find($answerId);
        if ($answerModel) {
            $hash = $answerModel->hash;
            DB::table('answers')->where('id', $answerId)->update(['hash' => $hash]);
            echo "✓ Generated hash for answer $answerId: $hash\n";
        }
    } else {
        echo "Answer $answerId already has hash or not found\n";
    }
}

// Generate hash for the item
$item = DB::table('items')->where('id', $itemId)->first();
if ($item && !$item->hash) {
    $itemModel = \App\Models\Exams\Item::find($itemId);
    if ($itemModel) {
        $hash = $itemModel->hash;
        DB::table('items')->where('id', $itemId)->update(['hash' => $hash]);
        echo "✓ Generated hash for item $itemId: $hash\n";
    }
} else {
    echo "Item $itemId already has hash or not found\n";
}

echo "\nHash generation completed for BE051125 - MCQ 60!\n";

// Verify
echo "\nVerification:\n";
$finalQuestion = DB::table('questions')->where('id', $questionId)->first();
$finalItem = DB::table('items')->where('id', $itemId)->first();
echo "Question hash: " . ($finalQuestion->hash ?? 'NULL') . "\n";
echo "Item hash: " . ($finalItem->hash ?? 'NULL') . "\n";

foreach ($answerIds as $answerId) {
    $answer = DB::table('answers')->where('id', $answerId)->first();
    echo "Answer $answerId hash: " . ($answer->hash ?? 'NULL') . "\n";
}