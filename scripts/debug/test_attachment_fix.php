<?php

require_once 'vendor/autoload.php';

use App\Services\RustService;

// Initialize the Rust service
$rustService = new RustService();

echo "Testing Rust Service Attachment Loading Fix\n";
echo "===============================================\n\n";

try {
    // Test loading exam data for BE051125 - OSCE exam
    $examData = $rustService->loadExamData(46, 155, 1);

    echo "Rust Service Response:\n";
    echo "- Success: " . ($examData['success'] ? 'YES' : 'NO') . "\n";

    if (isset($examData['items'])) {
        echo "- Total Items: " . count($examData['items']) . "\n";

        // Look for BE051125 - OSCE 1 (should be first item with hash j0gxy8KM)
        $osce1Item = null;
        foreach ($examData['items'] as $item) {
            if ($item['name'] === 'BE051125 - OSCE 1') {
                $osce1Item = $item;
                break;
            }
        }

        if ($osce1Item) {
            echo "\n✅ Found BE051125 - OSCE 1 item:\n";
            echo "- Hash: " . $osce1Item['hash'] . "\n";
            echo "- Questions: " . count($osce1Item['questions']) . "\n";
            echo "- Attachments: " . count($osce1Item['attachments']) . "\n";

            if (!empty($osce1Item['attachments'])) {
                echo "\n🎉 SUCCESS! Attachments are now loading:\n";
                foreach ($osce1Item['attachments'] as $attachment) {
                    echo "- ID: " . $attachment['id'] . "\n";
                    echo "- URL: " . $attachment['url'] . "\n";
                    echo "- Description: " . ($attachment['description'] ?? 'N/A') . "\n\n";
                }
            } else {
                echo "\n❌ ISSUE: No attachments found for BE051125 - OSCE 1\n";
                echo "This means the fix didn't work as expected.\n";
            }
        } else {
            echo "\n❌ ERROR: BE051125 - OSCE 1 not found in exam data\n";
        }
    } else {
        echo "- No items found in exam data\n";
    }

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n===============================================\n";
echo "Test completed at: " . date('Y-m-d H:i:s') . "\n";