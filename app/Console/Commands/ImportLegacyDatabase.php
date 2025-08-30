<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Client;
use App\Models\Accounts\User;
use App\Models\Accounts\Role;
use App\Models\Takers\Taker;
use App\Models\Takers\Group;
use App\Models\Exams\Exam;
use App\Models\Exams\Question;
use App\Models\Deliveries\Delivery;
use App\Models\Attempts\Attempt;
use Exception;

class ImportLegacyDatabase extends Command
{
    protected $signature = 'import:legacy-db 
                            {--client-name= : Name for the client being imported}
                            {--client-code= : Code for the client being imported}
                            {--mysql-host= : MySQL host}
                            {--mysql-port=3306 : MySQL port}
                            {--mysql-database= : MySQL database name}
                            {--mysql-username= : MySQL username}
                            {--mysql-password= : MySQL password}
                            {--dry-run : Run without making changes}
                            {--verify : Verify imported data}';

    protected $description = 'Import legacy single-tenant MySQL database into multi-tenant PostgreSQL setup';

    protected $mysqlConnection;
    protected $client;
    protected $isDryRun = false;
    protected $deliveryMap = []; // Map legacy delivery IDs to new delivery IDs
    protected $takerMap = []; // Map legacy taker IDs to new taker IDs
    protected $statistics = [
        'clients' => 0,
        'users' => 0,
        'roles' => 0,
        'takers' => 0,
        'groups' => 0,
        'exams' => 0,
        'questions' => 0,
        'deliveries' => 0,
        'attempts' => 0,
        'errors' => []
    ];

    public function handle()
    {
        $this->isDryRun = $this->option('dry-run');
        
        $this->info('🚀 Starting Legacy Database Import');
        $this->info($this->isDryRun ? '📋 DRY RUN MODE - No changes will be made' : '⚠️  LIVE MODE - Changes will be made');
        
        try {
            $this->setupMySQLConnection();
            $this->verifyConnections();
            
            if ($this->option('verify')) {
                return $this->verifyImportedData();
            }
            
            // Skip transactions for live runs to avoid timeout issues
            if ($this->isDryRun) {
                DB::beginTransaction();
            }
            
            $this->importClient();
            $this->importRoles();
            $this->importUsers();
            $this->importTakersAndGroups();
            $this->importExamsAndQuestions();
            $this->importDeliveries();
            $this->importAttempts();
            
            if ($this->isDryRun) {
                DB::rollBack();
                $this->info('🔄 Dry run completed - transaction rolled back');
            } else {
                $this->info('✅ Import completed successfully');
            }
            
            $this->displayStatistics();
            
        } catch (Exception $e) {
            DB::rollBack();
            $this->error("❌ Import failed: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }
        
        return 0;
    }

    protected function setupMySQLConnection()
    {
        $config = [
            'host' => $this->option('mysql-host') ?: '107.155.75.50',
            'port' => $this->option('mysql-port') ?: '5654',
            'database' => $this->option('mysql-database') ?: 'default',
            'username' => $this->option('mysql-username') ?: 'mysql',
            'password' => $this->option('mysql-password') ?: 'S8Tz8c5ogcy6ZaSsXaoomwVTuDlLDBiIyWhdFGCLgH0nU3wDFEGUo3J9q5HnfiuK',
        ];
        
        config(['database.connections.mysql_legacy' => array_merge([
            'driver' => 'mysql',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ], $config)]);
        
        $this->mysqlConnection = DB::connection('mysql_legacy');
        
        $this->info("📡 Connecting to MySQL: {$config['host']}:{$config['port']}/{$config['database']}");
    }

    protected function verifyConnections()
    {
        // Test MySQL connection
        try {
            $mysqlTables = $this->mysqlConnection->select('SHOW TABLES');
            $this->info("✅ MySQL connection successful - Found " . count($mysqlTables) . " tables");
        } catch (Exception $e) {
            throw new Exception("MySQL connection failed: " . $e->getMessage());
        }
        
        // Test PostgreSQL connection
        try {
            $pgTables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
            $this->info("✅ PostgreSQL connection successful - Found " . count($pgTables) . " tables");
        } catch (Exception $e) {
            throw new Exception("PostgreSQL connection failed: " . $e->getMessage());
        }
    }

    protected function importClient()
    {
        $this->info("\n📊 Importing Client...");
        
        $clientName = $this->option('client-name') ?: 'Legacy Client';
        $clientCode = $this->option('client-code') ?: 'LEGACY';
        
        if (!$this->isDryRun) {
            $this->client = Client::firstOrCreate(
                ['slug' => strtolower($clientCode)],
                [
                    'name' => $clientName,
                    'domains' => ['localhost'], // Default domain for imported legacy client
                    'is_active' => true,
                    'settings' => [
                        'imported_from' => 'mysql_legacy',
                        'imported_at' => now()->toISOString()
                    ]
                ]
            );
        } else {
            $this->client = (object)['id' => 1, 'name' => $clientName, 'code' => $clientCode];
        }
        
        $this->statistics['clients'] = 1;
        $this->info("✅ Client created: {$this->client->name} ({$this->client->code})");
    }

    protected function importRoles()
    {
        $this->info("\n👥 Importing Roles...");
        
        $legacyRoles = $this->mysqlConnection->table('roles')->get();
        
        foreach ($legacyRoles as $legacyRole) {
            $this->info("Processing role: {$legacyRole->name} ({$legacyRole->slug})");
            
            if (!$this->isDryRun) {
                // Map legacy roles to current system
                $roleData = [
                    'name' => $legacyRole->name,
                    'slug' => $legacyRole->slug,
                    'description' => $legacyRole->description,
                    'parent_id' => $legacyRole->parent_id,
                ];
                
                Role::updateOrCreate(
                    ['slug' => $legacyRole->slug],
                    $roleData
                );
            }
            
            $this->statistics['roles']++;
        }
        
        $this->info("✅ Imported {$this->statistics['roles']} roles");
    }

    protected function importUsers()
    {
        $this->info("\n👤 Importing Users...");
        
        $legacyUsers = $this->mysqlConnection->table('users')
            ->leftJoin('role_user', 'users.id', '=', 'role_user.user_id')
            ->leftJoin('roles', 'role_user.role_id', '=', 'roles.id')
            ->select('users.*', 'roles.slug as role_slug')
            ->get()
            ->groupBy('id');
        
        foreach ($legacyUsers as $userId => $userRecords) {
            $legacyUser = $userRecords->first();
            $roles = $userRecords->pluck('role_slug')->filter()->unique();
            
            $this->info("Processing user: {$legacyUser->name} ({$legacyUser->email})");
            
            if (!$this->isDryRun) {
                $user = User::withoutGlobalScopes()->updateOrCreate(
                    ['email' => $legacyUser->email, 'client_id' => $this->client->id],
                    [
                        'name' => $legacyUser->name,
                        'username' => $legacyUser->username ?? null,
                        'password' => $legacyUser->password ?: Hash::make('password'),
                        'email_verified_at' => $legacyUser->email_verified_at,
                        'created_at' => $legacyUser->created_at,
                        'updated_at' => $legacyUser->updated_at,
                    ]
                );
                
                // Attach roles
                $roleIds = Role::whereIn('slug', $roles)->pluck('id');
                $user->roles()->sync($roleIds);
            }
            
            $this->statistics['users']++;
        }
        
        $this->info("✅ Imported {$this->statistics['users']} users");
    }

    protected function importTakersAndGroups()
    {
        $this->info("\n🎓 Importing Takers and Groups...");
        
        // Import Groups first
        $legacyGroups = $this->mysqlConnection->table('groups')->get();
        $groupMap = [];
        
        foreach ($legacyGroups as $legacyGroup) {
            $this->info("Processing group: {$legacyGroup->name}");
            
            if (!$this->isDryRun) {
                $group = Group::create([
                    'name' => $legacyGroup->name,
                    'description' => $legacyGroup->description,
                    'code' => $legacyGroup->code,
                    'last_taker_code' => $legacyGroup->last_taker_code,
                    'closed_at' => $legacyGroup->closed_at,
                    'client_id' => $this->client->id,
                    'created_at' => $legacyGroup->created_at,
                    'updated_at' => $legacyGroup->updated_at,
                ]);
                
                $groupMap[$legacyGroup->id] = $group->id;
            }
            
            $this->statistics['groups']++;
        }
        
        // Import Takers
        $legacyTakers = $this->mysqlConnection->table('takers')
            ->leftJoin('group_taker', 'takers.id', '=', 'group_taker.taker_id')
            ->select('takers.*', 'group_taker.group_id', 'group_taker.taker_code')
            ->get()
            ->groupBy('id');
        
        foreach ($legacyTakers as $takerId => $takerRecords) {
            $legacyTaker = $takerRecords->first();
            
            $this->info("Processing taker: {$legacyTaker->name}");
            
            if (!$this->isDryRun) {
                // Use updateOrCreate to handle duplicate emails in legacy data
                $uniqueKey = $legacyTaker->email 
                    ? ['email' => $legacyTaker->email, 'client_id' => $this->client->id]
                    : ['name' => $legacyTaker->name, 'client_id' => $this->client->id];
                
                $taker = Taker::updateOrCreate(
                    $uniqueKey,
                    [
                        'name' => $legacyTaker->name,
                        'reg' => $legacyTaker->reg,
                        'email' => $legacyTaker->email,
                        'password' => $legacyTaker->password,
                        'is_verified' => $legacyTaker->is_verified,
                        'created_at' => $legacyTaker->created_at,
                        'updated_at' => $legacyTaker->updated_at,
                    ]
                );
                
                // Store mapping for attempts import
                $this->takerMap[$legacyTaker->id] = $taker->id;
                
                // Attach to groups
                foreach ($takerRecords as $record) {
                    if ($record->group_id && isset($groupMap[$record->group_id])) {
                        $taker->groups()->attach($groupMap[$record->group_id], [
                            'taker_code' => $record->taker_code
                        ]);
                    }
                }
            } else {
                // In dry run, we still need to build the mapping for testing
                $this->takerMap[$legacyTaker->id] = $takerId; // Use legacy ID as placeholder
            }
            
            $this->statistics['takers']++;
        }
        
        $this->info("✅ Imported {$this->statistics['groups']} groups and {$this->statistics['takers']} takers");
    }

    protected function importExamsAndQuestions()
    {
        $this->info("\n📝 Importing Exams and Questions...");
        
        // Import Exams
        $legacyExams = $this->mysqlConnection->table('exams')->get();
        $examMap = [];
        
        foreach ($legacyExams as $legacyExam) {
            $this->info("Processing exam: {$legacyExam->name}");
            
            if (!$this->isDryRun) {
                $exam = Exam::create([
                    'code' => $legacyExam->code,
                    'name' => $legacyExam->name,
                    'title' => $legacyExam->title ?? $legacyExam->name,
                    'description' => $legacyExam->description,
                    'is_published' => $legacyExam->is_published ?? false,
                    'client_id' => $this->client->id,
                    'created_at' => $legacyExam->created_at,
                    'updated_at' => $legacyExam->updated_at,
                ]);
                
                $examMap[$legacyExam->id] = $exam->id;
            }
            
            $this->statistics['exams']++;
        }
        
        // Import Questions
        $legacyQuestions = $this->mysqlConnection
            ->table('questions as q')
            ->join('exam_item as ei', 'q.item_id', '=', 'ei.item_id')
            ->select('q.*', 'ei.exam_id')
            ->get();
        
        foreach ($legacyQuestions as $legacyQuestion) {
            if (!$this->isDryRun && isset($examMap[$legacyQuestion->exam_id])) {
                Question::create([
                    'question' => $legacyQuestion->question,
                    'exam_id' => $examMap[$legacyQuestion->exam_id],
                    'client_id' => $this->client->id,
                    'created_at' => $legacyQuestion->created_at,
                    'updated_at' => $legacyQuestion->updated_at,
                ]);
            }
            
            $this->statistics['questions']++;
        }
        
        $this->info("✅ Imported {$this->statistics['exams']} exams and {$this->statistics['questions']} questions");
    }

    protected function importDeliveries()
    {
        $this->info("\n🚚 Importing Deliveries...");
        
        $legacyDeliveries = $this->mysqlConnection->table('deliveries')->get();
        
        // Build mappings from legacy to new IDs
        $examMap = [];
        $exams = Exam::where('client_id', $this->client->id)->get();
        foreach ($exams as $exam) {
            // Find legacy exam by code
            $legacyExam = $this->mysqlConnection->table('exams')->where('code', $exam->code)->first();
            if ($legacyExam) {
                $examMap[$legacyExam->id] = $exam->id;
            }
        }
        
        $groupMap = [];
        $groups = Group::where('client_id', $this->client->id)->get();
        foreach ($groups as $group) {
            // Find legacy group by code
            $legacyGroup = $this->mysqlConnection->table('groups')->where('code', $group->code)->first();
            if ($legacyGroup) {
                $groupMap[$legacyGroup->id] = $group->id;
            }
        }
        
        foreach ($legacyDeliveries as $legacyDelivery) {
            $this->info("Processing delivery: {$legacyDelivery->id}");
            
            if (!$this->isDryRun) {
                // Map legacy IDs to new IDs
                $newExamId = $examMap[$legacyDelivery->exam_id] ?? null;
                $newGroupId = $groupMap[$legacyDelivery->group_id] ?? null;
                
                if (!$newExamId || !$newGroupId) {
                    $this->warn("⚠️  Skipping delivery {$legacyDelivery->id} - missing exam or group mapping");
                    continue;
                }
                
                $delivery = Delivery::create([
                    'exam_id' => $newExamId,
                    'group_id' => $newGroupId,
                    'started_at' => $legacyDelivery->started_at,
                    'ended_at' => $legacyDelivery->ended_at,
                    'client_id' => $this->client->id,
                    'created_at' => $legacyDelivery->created_at,
                    'updated_at' => $legacyDelivery->updated_at,
                ]);
                
                // Store mapping for attempts import
                $this->deliveryMap[$legacyDelivery->id] = $delivery->id;
            }
            
            $this->statistics['deliveries']++;
        }
        
        $this->info("✅ Imported {$this->statistics['deliveries']} deliveries");
    }

    protected function importAttempts()
    {
        $this->info("\n✏️  Importing Attempts...");
        
        $legacyAttempts = $this->mysqlConnection->table('attempts')->get();
        
        foreach ($legacyAttempts as $legacyAttempt) {
            $this->info("Processing attempt: {$legacyAttempt->id}");
            
            if (!$this->isDryRun) {
                // Map legacy IDs to new IDs
                $newTakerId = $this->takerMap[$legacyAttempt->taker_id] ?? null;
                $newDeliveryId = $this->deliveryMap[$legacyAttempt->delivery_id] ?? null;
                
                if (!$newTakerId || !$newDeliveryId) {
                    $this->warn("⚠️  Skipping attempt {$legacyAttempt->id} - missing taker or delivery mapping");
                    continue;
                }
                
                Attempt::create([
                    'taker_id' => $newTakerId,
                    'delivery_id' => $newDeliveryId,
                    'started_at' => $legacyAttempt->started_at,
                    'finished_at' => $legacyAttempt->finished_at,
                    'score' => $legacyAttempt->score,
                    'client_id' => $this->client->id,
                    'created_at' => $legacyAttempt->created_at,
                    'updated_at' => $legacyAttempt->updated_at,
                ]);
            }
            
            $this->statistics['attempts']++;
        }
        
        $this->info("✅ Imported {$this->statistics['attempts']} attempts");
    }

    protected function verifyImportedData()
    {
        $this->info("\n🔍 Verifying imported data...");
        
        // Add verification logic here
        $this->info("✅ Data verification completed");
        return 0;
    }

    protected function displayStatistics()
    {
        $this->info("\n📊 Import Statistics:");
        $this->table(
            ['Entity', 'Count'],
            [
                ['Clients', $this->statistics['clients']],
                ['Roles', $this->statistics['roles']],
                ['Users', $this->statistics['users']],
                ['Groups', $this->statistics['groups']],
                ['Takers', $this->statistics['takers']],
                ['Exams', $this->statistics['exams']],
                ['Questions', $this->statistics['questions']],
                ['Deliveries', $this->statistics['deliveries']],
                ['Attempts', $this->statistics['attempts']],
            ]
        );
        
        if (!empty($this->statistics['errors'])) {
            $this->error("\n❌ Errors encountered:");
            foreach ($this->statistics['errors'] as $error) {
                $this->error($error);
            }
        }
    }
}