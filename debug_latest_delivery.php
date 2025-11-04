<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Deliveries\Delivery;
use App\Models\Exams\Exam;
use App\Models\Exams\Item;

echo "=== DEBUG LATEST DELIVERY & EXAM IMAGES ===\n\n";

try {
    // Find latest deliveries
    $latestDeliveries = Delivery::orderBy('created_at', 'desc')->take(3)->get();

    echo "Latest Deliveries:\n";
    foreach ($latestDeliveries as $delivery) {
        echo "- ID: {$delivery->id}, Name: {$delivery->name}, Created: {$delivery->created_at}\n";
    }
    echo "\n";

    // Get the latest delivery
    $latestDelivery = $latestDeliveries->first();
    if (!$latestDelivery) {
        echo "No deliveries found!\n";
        exit(1);
    }

    echo "=== Analyzing Latest Delivery ===\n";
    echo "Delivery ID: {$latestDelivery->id}\n";
    echo "Delivery Name: {$latestDelivery->name}\n";
    echo "Created: {$latestDelivery->created_at}\n\n";

    // Get the exam
    $exam = $latestDelivery->exam;
    if (!$exam) {
        echo "No exam found for this delivery!\n";
        exit(1);
    }

    echo "Exam ID: {$exam->id}\n";
    echo "Exam Name: {$exam->name}\n\n";

    // Get items with attachments
    $items = $exam->items()->with(['attachments'])->get();

    echo "Total Items: " . $items->count() . "\n\n";

    $itemsWithImages = $items->filter(function($item) {
        return $item->attachments->count() > 0;
    });

    echo "Items with Images: " . $itemsWithImages->count() . "\n\n";

    if ($itemsWithImages->count() > 0) {
        foreach ($itemsWithImages as $item) {
            echo "=== Item with Images ===\n";
            echo "Item ID: {$item->id}\n";
            echo "Item Hash: {$item->hash}\n";
            echo "Item Title: {$item->title}\n";

            foreach ($item->attachments as $attachment) {
                echo "  Attachment:\n";
                echo "  - ID: {$attachment->id}\n";
                echo "  - Path: {$attachment->path}\n";
                echo "  - MIME: {$attachment->mime}\n";
                echo "  - URL: " . route('attachment.stream', $attachment->id) . "\n";

                // Check if file exists
                $fullPath = storage_path('app/' . $attachment->path);
                echo "  - File Exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "\n";

                if (file_exists($fullPath)) {
                    echo "  - File Size: " . filesize($fullPath) . " bytes\n";
                }
                echo "\n";
            }
        }
    } else {
        echo "No items with images found in the latest delivery.\n";
    }

    // Test one attachment URL if exists
    if ($itemsWithImages->count() > 0) {
        $firstItem = $itemsWithImages->first();
        $firstAttachment = $firstItem->attachments->first();

        echo "=== Testing Image URL ===\n";
        $testUrl = route('attachment.stream', $firstAttachment->id);
        echo "Test URL: {$testUrl}\n";

        // Test with curl
        $ch = curl_init($testUrl);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $headers = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        echo "HTTP Status: {$httpCode}\n";

        if ($httpCode == 200) {
            echo "✅ Image is accessible!\n";
        } else {
            echo "❌ Image is not accessible (HTTP {$httpCode})\n";
            echo "Response headers:\n{$headers}\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== SELESAI ===\n";