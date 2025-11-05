<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Exams\Item;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

echo "=== MENGHAPUS 13 QUESTION SETS ===\n\n";

// IDs to delete
$idsToDelete = [1769, 1771, 1772, 1804, 1805, 1806, 1807, 1808, 1809, 1810, 1811, 1812, 1813];

echo "IDs yang akan dihapus: " . implode(', ', $idsToDelete) . "\n";
echo "Total: " . count($idsToDelete) . " items\n\n";

// Get items to delete
$itemsToDelete = Item::whereIn('id', $idsToDelete)->orderBy('id')->get();

if ($itemsToDelete->isEmpty()) {
    echo "Tidak ada items yang ditemukan dengan IDs tersebut.\n";
    exit(0);
}

echo "Ditemukan " . $itemsToDelete->count() . " items untuk dihapus:\n\n";
foreach ($itemsToDelete as $index => $item) {
    echo sprintf("%2d. ID: %-5d - %s (%s)\n",
        $index + 1,
        $item->id,
        $item->title,
        $item->type->name ?? 'N/A'
    );
}

echo "\n" . str_repeat("=", 100) . "\n";
echo "MULAI PROSES PENGHAPUSAN...\n";
echo str_repeat("=", 100) . "\n\n";

// Start database transaction for safety
DB::beginTransaction();

try {
    $totalQuestions = 0;
    $totalAnswers = 0;
    $totalAttachments = 0;
    
    foreach ($itemsToDelete as $item) {
        echo "🗑️  Menghapus Item ID: {$item->id} - {$item->title}\n";

        // Get all questions for this item
        $questions = \App\Models\Exams\Question::where('item_id', $item->id)->get();
        $totalQuestions += $questions->count();

        foreach ($questions as $question) {
            // Delete answers for this question
            $answerCount = \App\Models\Exams\Answer::where('question_id', $question->id)->delete();
            $totalAnswers += $answerCount;
            if ($answerCount > 0) {
                echo "   - Deleted {$answerCount} answer(s) for question ID: {$question->id}\n";
            }

            // Detach categories
            $question->categories()->detach();

            // Delete the question
            $question->delete();
        }
        
        if ($questions->count() > 0) {
            echo "   - Deleted {$questions->count()} question(s)\n";
        }

        // Delete attachments (including physical files)
        $attachments = $item->attachments;
        if ($attachments->count() > 0) {
            foreach ($attachments as $attachment) {
                // Delete physical file if exists
                if (Storage::exists($attachment->path)) {
                    Storage::delete($attachment->path);
                    echo "   - Deleted attachment file: {$attachment->path}\n";
                }

                // Delete attachment record
                $attachment->delete();
                $totalAttachments++;
            }
        }

        // Detach from exams
        $item->exams()->detach();

        // Delete the item
        $item->delete();
        echo "   ✅ Item ID {$item->id} berhasil dihapus\n\n";
    }

    // Commit the transaction
    DB::commit();
    
    echo str_repeat("=", 100) . "\n";
    echo "✅ PENGHAPUSAN BERHASIL!\n";
    echo str_repeat("=", 100) . "\n\n";
    
    echo "Ringkasan Penghapusan:\n";
    echo "- Items dihapus: " . $itemsToDelete->count() . "\n";
    echo "- Questions dihapus: $totalQuestions\n";
    echo "- Answers dihapus: $totalAnswers\n";
    echo "- Attachments dihapus: $totalAttachments\n";
    echo "- IDs yang dihapus: " . implode(', ', $idsToDelete) . "\n";

} catch (\Exception $e) {
    // Rollback on error
    DB::rollBack();
    echo "\n" . str_repeat("=", 100) . "\n";
    echo "❌ ERROR TERJADI!\n";
    echo str_repeat("=", 100) . "\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nTransaction rolled back. Tidak ada data yang dihapus.\n";
    exit(1);
}

echo "\n🎉 Operasi selesai!\n";
