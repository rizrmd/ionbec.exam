<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "🔍 CHECKING DELIVERY STRUCTURE\n";
echo "===========================\n\n";

$delivery = \App\Models\Deliveries\Delivery::first();

if (!$delivery) {
    echo "❌ No delivery found\n";
    exit;
}

echo "✅ Found Delivery: " . $delivery->name . " (ID: " . $delivery->id . ")\n";

$exam = $delivery->exam;
if ($exam) {
    echo "✅ Found Exam: " . $exam->name . " (ID: " . $exam->id . ")\n";
}

$items = $exam->items()->with('questions')->get();
echo "✅ Found " . $items->count() . " items\n\n";

foreach ($items as $item) {
    echo "📄 Item: " . $item->title . " (ID: " . $item->id . ")\n";
    echo "   Questions: " . $item->questions->count() . "\n";

    foreach ($item->questions as $question) {
        echo "   🔸 Q: " . substr($question->question, 0, 40) . "... (ID: " . $question->id . ")\n";

        $answerCount = $question->answers()->count();
        if ($answerCount > 0) {
            echo "   ✅ Has " . $answerCount . " answers\n";

            $correctAnswer = $question->answers()->where('is_correct_answer', true)->first();
            if ($correctAnswer) {
                echo "   🎯 Correct answer: " . substr($correctAnswer->answer, 0, 40) . "... (Score: " . $question->score . ")\n";
            }
        } else {
            echo "   ❌ No answers for this question!\n";
        }
    }
    echo "\n";
}

echo "🎯 STRUCTURE ANALYSIS COMPLETE\n";