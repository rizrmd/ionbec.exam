<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Exams\Item;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

echo "⚠️  WARNING: This script will delete question sets with IDs 1-12\n";
echo "This action is IRREVERSIBLE!\n\n";

// Confirm before proceeding
echo "Type 'DELETE' to continue: ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
if (trim($line) !== 'DELETE') {
    echo "Operation cancelled.\n";
    exit(0);
}

try {
    // Get items with specific IDs 1-12
    $itemsToDelete = Item::whereIn('id', range(1, 12))->get();

    if ($itemsToDelete->isEmpty()) {
        echo "No question sets found with IDs 1-12.\n";
        exit(0);
    }

    echo "\nFound " . $itemsToDelete->count() . " question sets to delete:\n";

    foreach ($itemsToDelete as $item) {
        echo "- ID: {$item->id}, Title: {$item->title}\n";
    }

    echo "\nProceeding with deletion...\n";

    // Start database transaction for safety
    DB::beginTransaction();

    try {
        foreach ($itemsToDelete as $item) {
            echo "\n🗑️  Deleting item ID: {$item->id} - {$item->title}\n";

            // Get all questions for this item
            $questions = \App\Models\Exams\Question::where('item_id', $item->id)->get();

            foreach ($questions as $question) {
                // Delete answers for this question
                $answerCount = \App\Models\Exams\Answer::where('question_id', $question->id)->delete();
                echo "  - Deleted {$answerCount} answers for question ID: {$question->id}\n";

                // Detach categories
                $question->categories()->detach();

                // Delete the question
                $question->delete();
                echo "  - Deleted question ID: {$question->id}\n";
            }

            // Delete attachments (including physical files)
            $attachments = $item->attachments;
            foreach ($attachments as $attachment) {
                // Delete physical file if exists
                if (Storage::exists($attachment->path)) {
                    Storage::delete($attachment->path);
                    echo "  - Deleted attachment file: {$attachment->path}\n";
                }

                // Delete attachment record
                $attachment->delete();
                echo "  - Deleted attachment record: {$attachment->id}\n";
            }

            // Detach from exams
            $item->exams()->detach();

            // Delete the item
            $item->delete();
            echo "  ✅ Deleted item ID: {$item->id}\n";
        }

        // Commit the transaction
        DB::commit();
        echo "\n✅ Successfully deleted question sets 1-12!\n";

    } catch (\Exception $e) {
        // Rollback on error
        DB::rollBack();
        echo "\n❌ Error occurred: " . $e->getMessage() . "\n";
        echo "Transaction rolled back. No data was deleted.\n";
        exit(1);
    }

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n🎉 Operation completed successfully!\n";
