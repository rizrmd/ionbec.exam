<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Exams\Item;
use App\Models\Attachments\Attachment;

echo "=== DEBUG ATTACHMENT UNTUK YRgyGB68 ===\n\n";

try {
    // Find the item by hash
    $itemHash = 'YRgyGB68';
    $item = Item::where('hash', $itemHash)->first();

    if (!$item) {
        echo "Item dengan hash $itemHash tidak ditemukan!\n";
        exit(1);
    }

    echo "Item ditemukan:\n";
    echo "- ID: {$item->id}\n";
    echo "- Hash: {$item->hash}\n";
    echo "- Title: {$item->title}\n";
    echo "- Type: " . (is_object($item->type) ? get_class($item->type) : $item->type) . "\n\n";

    // Load attachments dengan relasi
    $item->load(['attachments']);
    $attachments = $item->attachments;

    echo "Jumlah attachments: " . $attachments->count() . "\n\n";

    if ($attachments->count() > 0) {
        foreach ($attachments as $attachment) {
            echo "Attachment Details:\n";
            echo "- ID: {$attachment->id}\n";
            echo "- Type: {$attachment->type}\n";
            echo "- Path: {$attachment->path}\n";
            echo "- MIME: {$attachment->mime}\n";
            echo "- Description: {$attachment->description}\n";
            echo "- URL: " . route('attachment.stream', $attachment->id) . "\n";

            // Check if file exists
            $fullPath = storage_path('app/' . $attachment->path);
            echo "- Full Path: $fullPath\n";
            echo "- File Exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "\n";

            if (file_exists($fullPath)) {
                echo "- File Size: " . filesize($fullPath) . " bytes\n";
                echo "- Is Readable: " . (is_readable($fullPath) ? 'YES' : 'NO') . "\n";
            }

            echo "\n";
        }
    } else {
        echo "Tidak ada attachments untuk item ini.\n";
    }

    // Check di database langsung
    echo "\n=== DATABASE ATTACHABLES TABLE ===\n";
    $attachables = \Illuminate\Support\Facades\DB::table('attachables')
        ->where('attachable_id', $item->id)
        ->where('attachable_type', 'App\\Models\\Exams\\Item')
        ->get();

    echo "Jumlah attachables records: " . $attachables->count() . "\n";

    foreach ($attachables as $attachable) {
        echo "- Attachment ID: {$attachable->attachment_id}\n";

        $attachment = Attachment::find($attachable->attachment_id);
        if ($attachment) {
            echo "- Attachment Path: {$attachment->path}\n";
            echo "- Attachment MIME: {$attachment->mime}\n";
        }
        echo "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== SELESAI ===\n";