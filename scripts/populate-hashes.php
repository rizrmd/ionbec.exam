<?php

require_once "vendor/autoload.php";

$app = require_once "bootstrap/app.php";
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Populating hash values for all HashableId models...\n";

// Models that have hash columns and use HashableId trait
$models = [
    'App\Models\Attempts\Attempt' => 'attempts',
    'App\Models\Categories\Category' => 'categories', 
    'App\Models\Deliveries\Delivery' => 'deliveries',
    'App\Models\Exams\Answer' => 'answers',
    'App\Models\Exams\Exam' => 'exams',
    'App\Models\Exams\Item' => 'items',
    'App\Models\Exams\Question' => 'questions',
    'App\Models\Takers\Group' => 'groups',
    'App\Models\Takers\Taker' => 'takers'
];

foreach ($models as $modelClass => $tableName) {
    echo "\nProcessing $modelClass...\n";
    
    try {
        // Get all records without hashes
        $records = DB::table($tableName)->whereNull('hash')->orWhere('hash', '')->get(['id']);
        $count = $records->count();
        
        if ($count === 0) {
            echo "  All records already have hashes.\n";
            continue;
        }
        
        echo "  Found $count records without hashes.\n";
        
        $updated = 0;
        foreach ($records as $record) {
            // Get the model instance to generate the hash
            $model = $modelClass::find($record->id);
            if ($model) {
                $hash = $model->hash; // This generates the hash from ID
                
                // Update the database with the generated hash
                DB::table($tableName)->where('id', $record->id)->update(['hash' => $hash]);
                $updated++;
                
                if ($updated % 100 == 0) {
                    echo "  Updated $updated/$count records...\n";
                }
            }
        }
        
        echo "  Successfully updated $updated records.\n";
        
    } catch (Exception $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
    }
}

echo "\nHash population completed!\n";

// Verify a few samples
echo "\nVerification samples:\n";
foreach ($models as $modelClass => $tableName) {
    $sample = $modelClass::first();
    if ($sample) {
        $storedHash = DB::table($tableName)->where('id', $sample->id)->value('hash');
        $generatedHash = $sample->hash;
        $match = $storedHash === $generatedHash ? '✓' : '✗';
        echo "$tableName: stored=[$storedHash] generated=[$generatedHash] $match\n";
    }
}