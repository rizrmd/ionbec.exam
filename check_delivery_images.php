<?php

echo "=== CHECK LATEST DELIVERY & EXAM IMAGES ===\n";

// Get latest delivery
$delivery = DB::table('deliveries')
    ->select('id', 'name', 'exam_id', 'created_at')
    ->orderBy('created_at', 'desc')
    ->first();

if (!$delivery) {
    echo "No deliveries found!\n";
    exit(1);
}

echo "Latest Delivery:\n";
echo "- ID: {$delivery->id}\n";
echo "- Name: {$delivery->name}\n";
echo "- Exam ID: {$delivery->exam_id}\n";
echo "- Created: {$delivery->created_at}\n\n";

// Get exam info
$exam = DB::table('exams')
    ->select('id', 'name')
    ->where('id', $delivery->exam_id)
    ->first();

if (!$exam) {
    echo "No exam found!\n";
    exit(1);
}

echo "Exam:\n";
echo "- ID: {$exam->id}\n";
echo "- Name: {$exam->name}\n\n";

// Get items with attachments
$itemsWithAttachments = DB::table('items')
    ->select('items.id', 'items.title', 'items.hash')
    ->join('exam_items', 'items.id', '=', 'exam_items.item_id')
    ->where('exam_items.exam_id', $delivery->exam_id)
    ->join('attachables', function($join) {
        $join->on('items.id', '=', 'attachables.attachable_id')
             ->where('attachables.attachable_type', '=', 'App\\Models\\Exams\\Item');
    })
    ->distinct()
    ->get();

echo "Items with images: " . $itemsWithAttachments->count() . "\n\n";

if ($itemsWithAttachments->count() > 0) {
    foreach ($itemsWithAttachments as $item) {
        echo "=== Item ===\n";
        echo "ID: {$item->id}\n";
        echo "Hash: {$item->hash}\n";
        echo "Title: {$item->title}\n";

        // Get attachments for this item
        $attachments = DB::table('attachments')
            ->select('id', 'path', 'mime', 'description')
            ->join('attachables', 'attachments.id', '=', 'attachables.attachment_id')
            ->where('attachables.attachable_id', $item->id)
            ->where('attachables.attachable_type', '=', 'App\\Models\\Exams\\Item')
            ->get();

        foreach ($attachments as $attachment) {
            echo "  Attachment:\n";
            echo "  - ID: {$attachment->id}\n";
            echo "  - Path: {$attachment->path}\n";
            echo "  - MIME: {$attachment->mime}\n";
            echo "  - URL: /attachment/stream/{$attachment->id}\n";

            // Check if file exists
            $fullPath = storage_path('app/' . $attachment->path);
            echo "  - Local file exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "\n";
        }
        echo "\n";
    }

    // Test one image URL
    $firstItem = $itemsWithAttachments->first();
    $firstAttachment = DB::table('attachments')
        ->select('id')
        ->join('attachables', 'attachments.id', '=', 'attachables.attachment_id')
        ->where('attachables.attachable_id', $firstItem->id)
        ->where('attachables.attachable_type', '=', 'App\\Models\\Exams\\Item')
        ->first();

    if ($firstAttachment) {
        echo "=== Testing Image URL ===\n";
        $testUrl = url("/attachment/stream/{$firstAttachment->id}");
        echo "URL: {$testUrl}\n";

        // Initialize cURL session
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $testUrl);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $headers = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        echo "HTTP Status: {$httpCode}\n";

        if ($httpCode == 200) {
            echo "✅ SUCCESS: Image is accessible!\n";
        } else {
            echo "❌ FAILED: Image not accessible (HTTP {$httpCode})\n";
        }
    }
} else {
    echo "No items with images found in the latest delivery.\n";
}

echo "\n=== DONE ===\n";