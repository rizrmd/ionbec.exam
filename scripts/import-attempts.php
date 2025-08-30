<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Client;
use App\Models\Takers\Taker;
use App\Models\Deliveries\Delivery;
use App\Models\Exams\Exam;
use App\Models\Attempts\Attempt;
use Illuminate\Support\Facades\DB;

echo "=== CORRELATING AND IMPORTING ATTEMPTS ===\n\n";

// Get client
$client = Client::where('slug', 'ionbec')->first();
if (!$client) {
    die("Client not found\n");
}

// Setup MySQL connection
config(['database.connections.mysql_legacy' => [
    'driver' => 'mysql',
    'host' => '107.155.75.50',
    'port' => '5654',
    'database' => 'default',
    'username' => 'mysql',
    'password' => 'S8Tz8c5ogcy6ZaSsXaoomwVTuDlLDBiIyWhdFGCLgH0nU3wDFEGUo3J9q5HnfiuK',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'strict' => true,
    'engine' => null,
]]);

$mysql = DB::connection('mysql_legacy');

// Build correlations
echo "Building correlations...\n";

// 1. Taker correlations
$takerMap = [];
$legacyTakers = $mysql->table('takers')->get();
$importedTakers = Taker::where('client_id', $client->id)->get();

foreach ($legacyTakers as $lt) {
    // Try multiple matching strategies
    foreach ($importedTakers as $it) {
        if ($lt->email && $it->email && 
            (strcasecmp($lt->email, $it->email) == 0 || 
             strpos($it->email, $lt->email) === 0)) {
            $takerMap[$lt->id] = $it->id;
            break;
        } elseif ($lt->name == $it->name) {
            $takerMap[$lt->id] = $it->id;
            break;
        }
    }
}

echo "Taker mappings: " . count($takerMap) . "/" . count($legacyTakers) . "\n";

// 2. Delivery correlations
$deliveryMap = [];
$legacyDeliveries = $mysql->table('deliveries')->get();
$importedDeliveries = Delivery::where('client_id', $client->id)->get();

foreach ($legacyDeliveries as $ld) {
    $legacyExam = $mysql->table('exams')->find($ld->exam_id);
    $legacyGroup = $mysql->table('groups')->find($ld->group_id);
    
    if ($legacyExam && $legacyGroup) {
        foreach ($importedDeliveries as $id) {
            $importedExam = Exam::find($id->exam_id);
            $importedGroup = \App\Models\Takers\Group::find($id->group_id);
            
            if ($importedExam && $importedGroup &&
                $importedExam->code == $legacyExam->code &&
                $importedGroup->name == $legacyGroup->name) {
                $deliveryMap[$ld->id] = $id->id;
                break;
            }
        }
    }
}

echo "Delivery mappings: " . count($deliveryMap) . "/" . count($legacyDeliveries) . "\n\n";

// 3. Import attempts
echo "Importing attempts...\n";
$legacyAttempts = $mysql->table('attempts')->get();
$total = count($legacyAttempts);
$imported = 0;
$skipped = 0;

foreach ($legacyAttempts as $i => $attempt) {
    if ($i % 100 == 0) {
        echo "Progress: $i/$total\n";
    }
    
    // Check if we have both mappings
    if (!isset($takerMap[$attempt->taker_id]) || 
        !isset($deliveryMap[$attempt->delivery_id])) {
        $skipped++;
        continue;
    }
    
    $takerId = $takerMap[$attempt->taker_id];
    $deliveryId = $deliveryMap[$attempt->delivery_id];
    
    // Get exam from delivery
    $delivery = Delivery::find($deliveryId);
    if (!$delivery) {
        $skipped++;
        continue;
    }
    
    try {
        Attempt::create([
            'attempted_by' => $takerId,
            'exam_id' => $delivery->exam_id,
            'delivery_id' => $deliveryId,
            'ip_address' => '127.0.0.1',
            'started_at' => $attempt->started_at,
            'ended_at' => $attempt->ended_at ?? null,
            'finished_at' => $attempt->finished_at ?? null,
            'extra_minute' => 0,
            'score' => $attempt->score ?? 0,
            'progress' => 0,
            'penalty' => 0,
            'finish_scoring' => false,
            'client_id' => $client->id,
            'created_at' => $attempt->created_at,
            'updated_at' => $attempt->updated_at,
        ]);
        $imported++;
    } catch (\Exception $e) {
        $skipped++;
        // echo "Error: " . $e->getMessage() . "\n";
    }
}

echo "\n=== IMPORT COMPLETE ===\n";
echo "Total legacy attempts: $total\n";
echo "Successfully imported: $imported\n";
echo "Skipped (no correlation): $skipped\n";
echo "Import rate: " . round(($imported/$total)*100, 1) . "%\n";

// Final counts
echo "\n=== FINAL DATABASE COUNTS ===\n";
echo "Users: " . \App\Models\Accounts\User::where('client_id', $client->id)->count() . "\n";
echo "Groups: " . \App\Models\Takers\Group::where('client_id', $client->id)->count() . "\n";
echo "Takers: " . Taker::where('client_id', $client->id)->count() . "\n";
echo "Exams: " . Exam::where('client_id', $client->id)->count() . "\n";
echo "Questions: " . \App\Models\Exams\Question::where('client_id', $client->id)->count() . "\n";
echo "Deliveries: " . Delivery::where('client_id', $client->id)->count() . "\n";
echo "Attempts: " . Attempt::where('client_id', $client->id)->count() . "\n";