<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Client;
use App\Models\Takers\Taker;
use App\Models\Takers\Group;
use App\Models\Exams\Exam;
use App\Models\Deliveries\Delivery;
use App\Models\Attempts\Attempt;

class ImportRemainingAttempts extends Command
{
    protected $signature = 'import:remaining-attempts {--limit=100 : Number of attempts to import per batch}';
    
    protected $description = 'Import remaining attempts with proper correlations';
    
    private $mysqlConnection;
    private $client;
    private $takerMap = [];
    private $deliveryMap = [];
    private $statistics = [
        'total' => 0,
        'imported' => 0,
        'skipped' => 0,
        'errors' => 0
    ];
    
    public function handle()
    {
        $this->info('🚀 Starting Remaining Attempts Import');
        
        // Get client
        $this->client = Client::where('slug', 'ionbec')->first();
        if (!$this->client) {
            $this->error('Client not found');
            return 1;
        }
        
        // Setup MySQL connection
        $this->setupMySQLConnection();
        
        // Build correlations
        $this->info("\n📊 Building correlations...");
        $this->buildTakerCorrelations();
        $this->buildDeliveryCorrelations();
        
        // Import attempts in batches
        $this->info("\n✏️ Importing attempts...");
        $this->importAttempts();
        
        // Display results
        $this->displayResults();
        
        return 0;
    }
    
    private function setupMySQLConnection()
    {
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
            'strict' => false,
        ]]);
        
        $this->mysqlConnection = DB::connection('mysql_legacy');
    }
    
    private function buildTakerCorrelations()
    {
        $this->info('Building taker correlations...');
        
        // Get imported takers
        $importedTakers = Taker::where('client_id', $this->client->id)
            ->select('id', 'name', 'email')
            ->get();
        
        // Get legacy takers
        $legacyTakers = $this->mysqlConnection->table('takers')
            ->select('id', 'name', 'email')
            ->get();
        
        foreach ($legacyTakers as $legacyTaker) {
            // Try to find match
            $match = null;
            
            // Method 1: Email match
            if ($legacyTaker->email) {
                $match = $importedTakers->first(function ($taker) use ($legacyTaker) {
                    return $taker->email && 
                           (strcasecmp($taker->email, $legacyTaker->email) == 0 ||
                            strpos($taker->email, $legacyTaker->email) === 0);
                });
            }
            
            // Method 2: Name match
            if (!$match) {
                $match = $importedTakers->firstWhere('name', $legacyTaker->name);
            }
            
            if ($match) {
                $this->takerMap[$legacyTaker->id] = $match->id;
            }
        }
        
        $this->info('Taker mappings: ' . count($this->takerMap) . '/' . $legacyTakers->count());
    }
    
    private function buildDeliveryCorrelations()
    {
        $this->info('Building delivery correlations...');
        
        // Get legacy deliveries with exam and group info
        $legacyDeliveries = $this->mysqlConnection->table('deliveries as d')
            ->join('exams as e', 'd.exam_id', '=', 'e.id')
            ->join('groups as g', 'd.group_id', '=', 'g.id')
            ->select('d.id', 'e.code as exam_code', 'g.name as group_name')
            ->get();
        
        foreach ($legacyDeliveries as $legacyDelivery) {
            // Find matching imported delivery
            $exam = Exam::where('client_id', $this->client->id)
                ->where('code', $legacyDelivery->exam_code)
                ->first();
                
            $group = Group::where('client_id', $this->client->id)
                ->where('name', $legacyDelivery->group_name)
                ->first();
                
            if ($exam && $group) {
                $delivery = Delivery::where('client_id', $this->client->id)
                    ->where('exam_id', $exam->id)
                    ->where('group_id', $group->id)
                    ->first();
                    
                if ($delivery) {
                    $this->deliveryMap[$legacyDelivery->id] = [
                        'delivery_id' => $delivery->id,
                        'exam_id' => $exam->id
                    ];
                }
            }
        }
        
        $this->info('Delivery mappings: ' . count($this->deliveryMap) . '/' . $legacyDeliveries->count());
    }
    
    private function importAttempts()
    {
        $limit = $this->option('limit');
        $offset = 0;
        
        // Get total count
        $this->statistics['total'] = $this->mysqlConnection->table('attempts')->count();
        $this->info("Total attempts to process: {$this->statistics['total']}");
        
        while (true) {
            // Get batch of attempts
            $attempts = $this->mysqlConnection->table('attempts')
                ->offset($offset)
                ->limit($limit)
                ->get();
                
            if ($attempts->isEmpty()) {
                break;
            }
            
            $this->info("Processing batch: " . ($offset + 1) . "-" . ($offset + $attempts->count()));
            
            foreach ($attempts as $attempt) {
                $this->processAttempt($attempt);
            }
            
            $offset += $limit;
            
            // Show progress
            $this->info("Progress: {$this->statistics['imported']} imported, {$this->statistics['skipped']} skipped");
        }
    }
    
    private function processAttempt($legacyAttempt)
    {
        // Check correlations
        if (!isset($this->takerMap[$legacyAttempt->attempted_by])) {
            $this->statistics['skipped']++;
            return;
        }
        
        if (!isset($this->deliveryMap[$legacyAttempt->delivery_id])) {
            $this->statistics['skipped']++;
            return;
        }
        
        // Get mapped IDs
        $takerId = $this->takerMap[$legacyAttempt->attempted_by];
        $deliveryInfo = $this->deliveryMap[$legacyAttempt->delivery_id];
        
        try {
            Attempt::create([
                'attempted_by' => $takerId,
                'exam_id' => $deliveryInfo['exam_id'],
                'delivery_id' => $deliveryInfo['delivery_id'],
                'ip_address' => '127.0.0.1',
                'started_at' => $legacyAttempt->started_at,
                'ended_at' => $legacyAttempt->ended_at,
                'finished_at' => $legacyAttempt->finished_at ?? null,
                'extra_minute' => 0,
                'score' => $legacyAttempt->score ?? 0,
                'progress' => 0,
                'penalty' => 0,
                'finish_scoring' => false,
                'client_id' => $this->client->id,
                'created_at' => $legacyAttempt->created_at,
                'updated_at' => $legacyAttempt->updated_at,
            ]);
            
            $this->statistics['imported']++;
        } catch (\Exception $e) {
            $this->statistics['errors']++;
            // $this->warn("Error importing attempt: " . $e->getMessage());
        }
    }
    
    private function displayResults()
    {
        $this->info("\n📊 Import Results:");
        $this->table(
            ['Metric', 'Count', 'Percentage'],
            [
                ['Total Legacy Attempts', $this->statistics['total'], '100%'],
                ['Successfully Imported', $this->statistics['imported'], round(($this->statistics['imported']/$this->statistics['total'])*100, 1) . '%'],
                ['Skipped (No Correlation)', $this->statistics['skipped'], round(($this->statistics['skipped']/$this->statistics['total'])*100, 1) . '%'],
                ['Errors', $this->statistics['errors'], round(($this->statistics['errors']/$this->statistics['total'])*100, 1) . '%'],
            ]
        );
        
        $finalCount = Attempt::where('client_id', $this->client->id)->count();
        $this->info("\n✅ Final attempts in database: $finalCount");
        
        $this->info("\n📈 Correlation Analysis:");
        $this->info("Taker correlation rate: " . round((count($this->takerMap)/897)*100, 1) . "%");
        $this->info("Delivery correlation rate: " . round((count($this->deliveryMap)/136)*100, 1) . "%");
    }
}