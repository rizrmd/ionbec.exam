<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

echo "=== MENGHAPUS 13 QUESTION SETS (BATCH 2) ===\n\n";

// IDs to delete
$idsToDelete = [1770, 1773, 1774, 1776, 1777, 1779, 1781, 1782, 1814, 1815, 1816, 1817, 1818];

echo "IDs yang akan dihapus: " . implode(', ', $idsToDelete) . "\n";
echo "Total: " . count($idsToDelete) . " items\n\n";

echo str_repeat("=", 100) . "\n";
echo "MULAI PROSES PENGHAPUSAN...\n";
echo str_repeat("=", 100) . "\n\n";

// Start database transaction
DB::beginTransaction();

try {
    $totalQuestions = 0;
    $totalAnswers = 0;
    $totalAttachments = 0;
    
    foreach ($idsToDelete as $itemId) {
        $item = DB::table('items')->where('id', $itemId)->first();
        
        if (!$item) {
            echo "⚠️  Item ID $itemId tidak ditemukan, skip...\n\n";
            continue;
        }
        
        echo "🗑️  Menghapus Item ID: {$item->id} - {$item->title}\n";

        // Get all questions for this item
        $questions = DB::table('questions')->where('item_id', $itemId)->get();
        $totalQuestions += $questions->count();

        foreach ($questions as $question) {
            // Delete answers for this question
            $answerCount = DB::table('answers')->where('question_id', $question->id)->delete();
            $totalAnswers += $answerCount;
            if ($answerCount > 0) {
                echo "   - Deleted {$answerCount} answer(s) for question ID: {$question->id}\n";
            }

            // Delete category relationships
            DB::table('category_question')->where('question_id', $question->id)->delete();
        }
        
        // Delete all questions for this item
        if ($questions->count() > 0) {
            DB::table('questions')->where('item_id', $itemId)->delete();
            echo "   - Deleted {$questions->count()} question(s)\n";
        }

        // Get attachments
        $attachments = DB::table('attachables')
            ->where('attachable_id', $itemId)
            ->where('attachable_type', 'App\Models\Exams\Item')
            ->get();
        
        if ($attachments->count() > 0) {
            foreach ($attachments as $attachable) {
                $attachment = DB::table('attachments')->where('id', $attachable->attachment_id)->first();
                
                if ($attachment) {
                    // Delete physical file if exists
                    if (Storage::exists($attachment->path)) {
                        Storage::delete($attachment->path);
                        echo "   - Deleted attachment file: {$attachment->path}\n";
                    }

                    // Delete attachment record
                    DB::table('attachments')->where('id', $attachment->id)->delete();
                    $totalAttachments++;
                }
            }
            
            // Delete attachables relationships
            DB::table('attachables')
                ->where('attachable_id', $itemId)
                ->where('attachable_type', 'App\Models\Exams\Item')
                ->delete();
        }

        // Delete from exam_item pivot table
        DB::table('exam_item')->where('item_id', $itemId)->delete();

        // Delete the item
        DB::table('items')->where('id', $itemId)->delete();
        echo "   ✅ Item ID {$itemId} berhasil dihapus\n\n";
    }

    // Commit the transaction
    DB::commit();
    
    echo str_repeat("=", 100) . "\n";
    echo "✅ PENGHAPUSAN BERHASIL!\n";
    echo str_repeat("=", 100) . "\n\n";
    
    echo "Ringkasan Penghapusan:\n";
    echo "- Items dihapus: " . count($idsToDelete) . "\n";
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
