<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CompleteDataSync extends Command
{
    protected $signature = 'data:complete-sync {--phase=all : Which phase to run (backup|cleanup|import|verify|all)}';
    protected $description = 'Complete synchronization of MySQL data to PostgreSQL with proper client_id assignment';

    private $mysqlConnection;
    private $targetClientId = 16; // National Demo Board Examination

    public function handle()
    {
        $phase = $this->option('phase');
        
        $this->info("Starting Complete Data Synchronization - Phase: {$phase}");
        
        // Connect to MySQL
        $this->connectToMysql();
        
        switch ($phase) {
            case 'backup':
                $this->runPhase1Backup();
                break;
            case 'cleanup':
                $this->runPhase2Cleanup();
                break;
            case 'import':
                $this->runPhase3Import();
                break;
            case 'verify':
                $this->runPhase5Verification();
                break;
            case 'all':
                $this->runAllPhases();
                break;
            default:
                $this->error("Invalid phase. Use: backup, cleanup, import, verify, or all");
                return 1;
        }
        
        $this->info('Complete Data Synchronization finished!');
        return 0;
    }

    private function connectToMysql()
    {
        config(['database.connections.mysql_ref' => [
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

        $this->mysqlConnection = DB::connection('mysql_ref');
        
        try {
            $this->mysqlConnection->getPdo();
            $this->info('Connected to MySQL reference database');
        } catch (\Exception $e) {
            $this->error('Failed to connect to MySQL: ' . $e->getMessage());
            exit(1);
        }
    }

    private function runAllPhases()
    {
        $this->info("\n=== RUNNING ALL PHASES ===");
        
        if (!$this->confirm('This will completely replace your data. Are you sure?')) {
            $this->info('Operation cancelled.');
            return;
        }
        
        $this->runPhase1Backup();
        $this->runPhase2Cleanup();
        $this->runPhase3Import();
        $this->runPhase5Verification();
    }

    private function runPhase1Backup()
    {
        $this->info("\n=== PHASE 1: BACKUP & PREPARATION ===");
        
        // Show current data counts before backup
        $this->showCurrentCounts('BEFORE BACKUP');
        
        // Note: Actual backup would be done at system level
        $this->warn('REMINDER: Ensure you have created a database backup before proceeding!');
        $this->warn('Use: pg_dump to create a backup of your PostgreSQL database');
        
        if (!$this->confirm('Have you created a backup?')) {
            $this->error('Please create a backup before proceeding.');
            exit(1);
        }
    }

    private function runPhase2Cleanup()
    {
        $this->info("\n=== PHASE 2: DATA CLEANUP ===");
        
        if (!$this->confirm('This will delete all exam data. Continue?')) {
            return;
        }
        
        // Disable foreign key checks temporarily (PostgreSQL)
        DB::statement('SET session_replication_role = replica');
        
        $tablesToClean = [
            'attempt_answers',
            'attempt_questions', 
            'attempts',
            'delivery_taker',
            'deliveries',
            'exam_item',
            'exams',
            'group_taker',
            'takers',
            'groups',
            'category_question',
            'category_item',
            'answers',
            'questions',
            'items',
            'categories',
        ];
        
        foreach ($tablesToClean as $table) {
            try {
                if (Schema::hasTable($table)) {
                    $count = DB::table($table)->count();
                    // Use DELETE instead of TRUNCATE to avoid CASCADE issues
                    DB::table($table)->delete();
                    $this->info("Cleaned {$table}: {$count} records removed");
                }
            } catch (\Exception $e) {
                $this->warn("Could not clean {$table}: " . $e->getMessage());
                // Try alternative cleanup method
                try {
                    DB::statement("DELETE FROM {$table}");
                    $this->info("Alternative cleanup successful for {$table}");
                } catch (\Exception $e2) {
                    $this->error("Failed both cleanup methods for {$table}: " . $e2->getMessage());
                }
            }
        }
        
        // Reset sequences for PostgreSQL to avoid ID conflicts  
        $sequenceTables = ['categories', 'items', 'questions', 'answers', 'groups', 'takers', 'exams', 'deliveries', 'attempts'];
        foreach ($sequenceTables as $table) {
            try {
                if (Schema::hasTable($table)) {
                    DB::statement("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), 1, false)");
                    $this->info("Reset sequence for {$table}");
                }
            } catch (\Exception $e) {
                $this->warn("Could not reset sequence for {$table}: " . $e->getMessage());
            }
        }
        
        // Re-enable foreign key checks (PostgreSQL)
        DB::statement('SET session_replication_role = DEFAULT');
        
        $this->info('Phase 2 cleanup completed');
    }

    private function runPhase3Import()
    {
        $this->info("\n=== PHASE 3: DATA IMPORT ===");
        
        // Check what's already imported
        $categoriesCount = DB::table('categories')->count();
        $itemsCount = DB::table('items')->count();
        $questionsCount = DB::table('questions')->count();
        $answersCount = DB::table('answers')->count();
        $groupsCount = DB::table('groups')->count();
        
        // Import in dependency order, skipping what's already done
        if ($categoriesCount == 0) {
            $this->importCategories();
        } else {
            $this->info("Skipping categories import - already have {$categoriesCount} records");
        }
        
        if ($itemsCount == 0) {
            $this->importItems();
        } else {
            $this->info("Skipping items import - already have {$itemsCount} records");
        }
        
        if ($questionsCount == 0) {
            $this->importQuestions();
        } else {
            $this->info("Skipping questions import - already have {$questionsCount} records");
        }
        
        if ($answersCount == 0) {
            $this->importAnswers();
        } else {
            $this->info("Skipping answers import - already have {$answersCount} records");
        }
        
        $this->importCategoryRelationships();
        
        // Import groups if needed
        if ($groupsCount == 0) {
            $this->importGroups();
        } else {
            $this->info("Skipping groups import - already have {$groupsCount} records");
        }
        
        // Import takers if needed
        $takersCount = DB::table('takers')->count();
        if ($takersCount == 0) {
            $this->importTakers();
        } else {
            $this->info("Skipping takers import - already have {$takersCount} records");
        }
        
        // Always check and import remaining components
        $this->importGroupTakerRelationships();
        
        $examsCount = DB::table('exams')->count();
        if ($examsCount == 0) {
            $this->importExams();
        } else {
            $this->info("Skipping exams import - already have {$examsCount} records");
        }
        
        $deliveriesCount = DB::table('deliveries')->count();
        if ($deliveriesCount == 0) {
            $this->importDeliveries();
        } else {
            $this->info("Skipping deliveries import - already have {$deliveriesCount} records");
        }
        
        $this->importExamItemRelationships();
        $this->importDeliveryTakerRelationships();
        
        $attemptsCount = DB::table('attempts')->count();
        if ($attemptsCount == 0) {
            $this->importAttempts();
            $this->importAttemptQuestions();
            $this->importAttemptAnswers();
        } else {
            $this->info("Skipping attempts import - already have {$attemptsCount} records");
        }
        
        $this->info('Phase 3 import completed');
    }

    private function importCategories()
    {
        $this->info('Importing categories...');
        
        $mysqlCategories = $this->mysqlConnection->table('categories')->get();
        
        foreach ($mysqlCategories as $category) {
            DB::table('categories')->insert([
                'id' => $category->id,
                'type' => $category->type ?? 'category',
                'code' => $category->code,
                'parent' => $category->parent ?? 0,
                'name' => $category->name,
                'description' => $category->description,
                'client_id' => $this->targetClientId,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
            ]);
        }
        
        $this->info("Imported {$mysqlCategories->count()} categories");
    }

    private function importItems()
    {
        $this->info('Importing items...');
        
        $mysqlItems = $this->mysqlConnection->table('items')->get();
        
        foreach ($mysqlItems as $item) {
            DB::table('items')->insert([
                'id' => $item->id,
                'title' => $item->title,
                'content' => $item->content,
                'type' => $item->type ?? 'simple',
                'is_vignette' => $item->is_vignette ?? 0,
                'is_random' => $item->is_random ?? 0,
                'score' => $item->score ?? 0,
                'client_id' => $this->targetClientId,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ]);
        }
        
        $this->info("Imported {$mysqlItems->count()} items");
    }

    private function importQuestions()
    {
        $this->info('Importing questions...');
        
        $mysqlQuestions = $this->mysqlConnection->table('questions')->get();
        
        foreach ($mysqlQuestions as $question) {
            DB::table('questions')->insert([
                'id' => $question->id,
                'item_id' => $question->item_id,
                'type' => $question->type ?? 'simple',
                'question' => $question->question,
                'is_random' => $question->is_random ?? 0,
                'score' => $question->score ?? 100,
                'order' => $question->order ?? 0,
                'client_id' => $this->targetClientId,
                'created_at' => $question->created_at,
                'updated_at' => $question->updated_at,
            ]);
        }
        
        $this->info("Imported {$mysqlQuestions->count()} questions");
    }

    private function importAnswers()
    {
        $this->info('Importing answers...');
        
        $mysqlAnswers = $this->mysqlConnection->table('answers')->get();
        
        foreach ($mysqlAnswers as $answer) {
            DB::table('answers')->insert([
                'id' => $answer->id,
                'question_id' => $answer->question_id,
                'answer' => $answer->answer,
                'is_correct_answer' => $answer->is_correct_answer ?? 0,
                'created_at' => $answer->created_at,
                'updated_at' => $answer->updated_at,
            ]);
        }
        
        $this->info("Imported {$mysqlAnswers->count()} answers");
    }

    private function importCategoryRelationships()
    {
        $this->info('Importing category relationships...');
        
        try {
            // Check if category_question relationships already exist
            $existingCQCount = DB::table('category_question')->count();
            if ($existingCQCount > 0) {
                $this->info("Skipping category-question relationships - already have {$existingCQCount} records");
            } else {
                // Category-Question relationships
                $mysqlCQ = $this->mysqlConnection->table('category_question')->get();
                $imported = 0;
                $skipped = 0;
                
                foreach ($mysqlCQ as $relation) {
                    try {
                        // Check if both category and question exist
                        $categoryExists = DB::table('categories')->where('id', $relation->category_id)->exists();
                        $questionExists = DB::table('questions')->where('id', $relation->question_id)->exists();
                        
                        if ($categoryExists && $questionExists) {
                            DB::table('category_question')->insert([
                                'category_id' => $relation->category_id,
                                'question_id' => $relation->question_id,
                            ]);
                            $imported++;
                        } else {
                            $skipped++;
                        }
                    } catch (\Exception $e) {
                        $skipped++;
                    }
                    
                    if (($imported + $skipped) % 1000 == 0) {
                        $this->info("Processed " . ($imported + $skipped) . " category-question relationships...");
                    }
                }
                $this->info("Imported {$imported} category-question relationships, skipped {$skipped}");
            }
            
            // Category-Item relationships
            if ($this->mysqlConnection->getSchemaBuilder()->hasTable('category_item')) {
                $existingCICount = DB::table('category_item')->count();
                if ($existingCICount > 0) {
                    $this->info("Skipping category-item relationships - already have {$existingCICount} records");
                } else {
                    $mysqlCI = $this->mysqlConnection->table('category_item')->get();
                    $imported = 0;
                    $skipped = 0;
                    
                    foreach ($mysqlCI as $relation) {
                        try {
                            // Check if both category and item exist
                            $categoryExists = DB::table('categories')->where('id', $relation->category_id)->exists();
                            $itemExists = DB::table('items')->where('id', $relation->item_id)->exists();
                            
                            if ($categoryExists && $itemExists) {
                                DB::table('category_item')->insert([
                                    'category_id' => $relation->category_id,
                                    'item_id' => $relation->item_id,
                                ]);
                                $imported++;
                            } else {
                                $skipped++;
                            }
                        } catch (\Exception $e) {
                            $skipped++;
                        }
                    }
                    $this->info("Imported {$imported} category-item relationships, skipped {$skipped}");
                }
            }
        } catch (\Exception $e) {
            $this->error("Failed to import category relationships: " . $e->getMessage());
        }
    }

    private function importGroups()
    {
        $this->info('Importing groups...');
        
        try {
            $mysqlGroups = $this->mysqlConnection->table('groups')->get();
            $this->info("Found {$mysqlGroups->count()} groups in MySQL");
            
            $imported = 0;
            $skipped = 0;
            
            foreach ($mysqlGroups as $group) {
                try {
                    $closedAt = $this->validateAndCleanDate($group->closed_at);
                    $createdAt = $this->validateAndCleanDate($group->created_at) ?? now();
                    $updatedAt = $this->validateAndCleanDate($group->updated_at) ?? now();
                    
                    DB::table('groups')->insert([
                        'id' => $group->id,
                        'name' => $group->name,
                        'description' => $group->description,
                        'code' => $group->code,
                        'last_taker_code' => $group->last_taker_code ?? 1,
                        'closed_at' => $closedAt,
                        'client_id' => $this->targetClientId,
                        'created_at' => $createdAt,
                        'updated_at' => $updatedAt,
                    ]);
                    $imported++;
                } catch (\Exception $e) {
                    $this->warn("Skipping group {$group->id}: " . $e->getMessage());
                    $skipped++;
                }
            }
            
            $this->info("Imported {$imported} groups, skipped {$skipped}");
        } catch (\Exception $e) {
            $this->error("Failed to import groups: " . $e->getMessage());
        }
    }

    private function importTakers()
    {
        $this->info('Importing takers...');
        
        try {
            $mysqlTakers = $this->mysqlConnection->table('takers')->get();
            $this->info("Found {$mysqlTakers->count()} takers in MySQL");
            
            $imported = 0;
            $skipped = 0;
            $duplicates = 0;
            $errors = 0;
            $errorReasons = [];
            
            foreach ($mysqlTakers as $taker) {
                try {
                    // Check if taker with this email already exists for this client
                    $exists = DB::table('takers')
                        ->where('email', $taker->email)
                        ->where('client_id', $this->targetClientId)
                        ->exists();
                        
                    if ($exists) {
                        $duplicates++;
                        continue;
                    }
                    
                    // Validate required fields
                    if (empty($taker->email)) {
                        $skipped++;
                        $errorReasons['empty_email'] = ($errorReasons['empty_email'] ?? 0) + 1;
                        continue;
                    }
                    
                    if (empty($taker->name)) {
                        $skipped++;
                        $errorReasons['empty_name'] = ($errorReasons['empty_name'] ?? 0) + 1;
                        continue;
                    }
                    
                    // Handle dates
                    $createdAt = $this->validateAndCleanDate($taker->created_at) ?? now();
                    $updatedAt = $this->validateAndCleanDate($taker->updated_at) ?? now();
                    
                    DB::table('takers')->insert([
                        'id' => $taker->id,
                        'name' => $taker->name,
                        'reg' => $taker->reg,
                        'email' => $taker->email,
                        'password' => $taker->password,
                        'is_verified' => $taker->is_verified ?? 0,
                        'client_id' => $this->targetClientId,
                        'created_at' => $createdAt,
                        'updated_at' => $updatedAt,
                    ]);
                    $imported++;
                    
                    if ($imported % 100 == 0) {
                        $this->info("Imported {$imported} takers...");
                    }
                    
                } catch (\Exception $e) {
                    $errors++;
                    $errorMessage = $e->getMessage();
                    
                    // Categorize errors
                    if (strpos($errorMessage, 'duplicate key') !== false) {
                        $errorReasons['duplicate_key'] = ($errorReasons['duplicate_key'] ?? 0) + 1;
                    } elseif (strpos($errorMessage, 'constraint') !== false) {
                        $errorReasons['constraint_violation'] = ($errorReasons['constraint_violation'] ?? 0) + 1;
                    } elseif (strpos($errorMessage, 'NULL') !== false) {
                        $errorReasons['null_constraint'] = ($errorReasons['null_constraint'] ?? 0) + 1;
                    } else {
                        $errorReasons['other_error'] = ($errorReasons['other_error'] ?? 0) + 1;
                    }
                    
                    if ($errors <= 5) {
                        $this->warn("Error importing taker {$taker->id} ({$taker->email}): {$errorMessage}");
                    }
                }
            }
            
            $this->info("Import summary:");
            $this->info("  Imported: {$imported}");
            $this->info("  Duplicates: {$duplicates}");
            $this->info("  Validation skips: {$skipped}");
            $this->info("  Errors: {$errors}");
            
            if (!empty($errorReasons)) {
                $this->info("Error breakdown:");
                foreach ($errorReasons as $reason => $count) {
                    $this->info("  {$reason}: {$count}");
                }
            }
            
        } catch (\Exception $e) {
            $this->error("Failed to import takers: " . $e->getMessage());
        }
    }

    private function importGroupTakerRelationships()
    {
        $this->info('Importing group-taker relationships...');
        
        $mysqlGT = $this->mysqlConnection->table('group_taker')->get();
        
        foreach ($mysqlGT as $relation) {
            // Check if both group and taker exist
            $groupExists = DB::table('groups')->where('id', $relation->group_id)->exists();
            $takerExists = DB::table('takers')->where('id', $relation->taker_id)->exists();
            
            if ($groupExists && $takerExists) {
                DB::table('group_taker')->insert([
                    'group_id' => $relation->group_id,
                    'taker_id' => $relation->taker_id,
                    'taker_code' => $relation->taker_code ?? null,
                ]);
            }
        }
        
        $this->info("Imported {$mysqlGT->count()} group-taker relationships");
    }

    private function importExams()
    {
        $this->info('Importing exams...');
        
        $mysqlExams = $this->mysqlConnection->table('exams')->get();
        
        foreach ($mysqlExams as $exam) {
            DB::table('exams')->insert([
                'id' => $exam->id,
                'code' => $exam->code,
                'name' => $exam->name,
                'description' => $exam->description,
                'options' => $exam->options,
                'is_random' => $exam->is_random ?? 0,
                'is_mcq' => $exam->is_mcq ?? 0,
                'is_interview' => $exam->is_interview ?? 0,
                'is_published' => 1, // Default to published
                'title' => $exam->name, // Use name as title
                'client_id' => $this->targetClientId,
                'created_at' => $exam->created_at,
                'updated_at' => $exam->updated_at,
            ]);
        }
        
        $this->info("Imported {$mysqlExams->count()} exams");
    }

    private function importDeliveries()
    {
        $this->info('Importing deliveries...');
        
        $mysqlDeliveries = $this->mysqlConnection->table('deliveries')->get();
        
        $imported = 0;
        $skipped = 0;
        
        foreach ($mysqlDeliveries as $delivery) {
            try {
                // Handle invalid dates with comprehensive validation
                $endedAt = $this->validateAndCleanDate($delivery->ended_at);
                $scheduledAt = $this->validateAndCleanDate($delivery->scheduled_at);
                $createdAt = $this->validateAndCleanDate($delivery->created_at) ?? now();
                $updatedAt = $this->validateAndCleanDate($delivery->updated_at) ?? now();
                
                DB::table('deliveries')->insert([
                    'id' => $delivery->id,
                    'exam_id' => $delivery->exam_id,
                    'group_id' => $delivery->group_id,
                    'name' => $delivery->name,
                    'scheduled_at' => $scheduledAt,
                    'duration' => $delivery->duration,
                    'ended_at' => $endedAt,
                    'is_anytime' => $delivery->is_anytime ?? 0,
                    'automatic_start' => $delivery->automatic_start ?? 0,
                    'is_finished' => ($delivery->is_finished === null || $delivery->is_finished === '') ? 0 : $delivery->is_finished,
                    'last_status' => $delivery->last_status ?? 'Pending',
                    'display_name' => $delivery->display_name,
                    'client_id' => $this->targetClientId,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ]);
                $imported++;
                
                if ($imported % 50 == 0) {
                    $this->info("Imported {$imported} deliveries...");
                }
            } catch (\Exception $e) {
                $this->warn("Skipping delivery {$delivery->id}: " . $e->getMessage());
                $this->warn("  Problematic values - ended_at: '{$delivery->ended_at}', scheduled_at: '{$delivery->scheduled_at}', created_at: '{$delivery->created_at}', updated_at: '{$delivery->updated_at}'");
                $skipped++;
            }
        }
        
        $this->info("Imported {$imported} deliveries, skipped {$skipped}");
    }

    private function importExamItemRelationships()
    {
        $this->info('Importing exam-item relationships...');
        
        $mysqlEI = $this->mysqlConnection->table('exam_item')->get();
        
        foreach ($mysqlEI as $relation) {
            DB::table('exam_item')->insert([
                'exam_id' => $relation->exam_id,
                'item_id' => $relation->item_id,
                'order' => $relation->order ?? 0,
            ]);
        }
        
        $this->info("Imported {$mysqlEI->count()} exam-item relationships");
    }

    private function importDeliveryTakerRelationships()
    {
        $this->info('Importing delivery-taker relationships...');
        
        $mysqlDT = $this->mysqlConnection->table('delivery_taker')->get();
        
        foreach ($mysqlDT as $relation) {
            // Check if both delivery and taker exist
            $deliveryExists = DB::table('deliveries')->where('id', $relation->delivery_id)->exists();
            $takerExists = DB::table('takers')->where('id', $relation->taker_id)->exists();
            
            if ($deliveryExists && $takerExists) {
                DB::table('delivery_taker')->insert([
                    'delivery_id' => $relation->delivery_id,
                    'taker_id' => $relation->taker_id,
                    'token' => $relation->token ?? '',
                    'is_login' => $relation->is_login ?? 0,
                ]);
            }
        }
        
        $this->info("Imported {$mysqlDT->count()} delivery-taker relationships");
    }

    private function importAttempts()
    {
        $this->info('Importing attempts...');
        
        $mysqlAttempts = $this->mysqlConnection->table('attempts')->get();
        
        $imported = 0;
        $skipped = 0;
        
        foreach ($mysqlAttempts as $attempt) {
            try {
                // Check if taker, exam, and delivery exist
                $takerExists = DB::table('takers')->where('id', $attempt->attempted_by)->exists();
                $examExists = DB::table('exams')->where('id', $attempt->exam_id)->exists();
                $deliveryExists = DB::table('deliveries')->where('id', $attempt->delivery_id)->exists();
                
                if ($takerExists && $examExists && $deliveryExists) {
                    // Handle invalid dates with comprehensive validation
                    $startedAt = $this->validateAndCleanDate($attempt->started_at);
                    $endedAt = $this->validateAndCleanDate($attempt->ended_at);
                    $createdAt = $this->validateAndCleanDate($attempt->created_at) ?? now();
                    $updatedAt = $this->validateAndCleanDate($attempt->updated_at) ?? now();
                    
                    DB::table('attempts')->insert([
                        'id' => $attempt->id,
                        'attempted_by' => $attempt->attempted_by,
                        'exam_id' => $attempt->exam_id,
                        'delivery_id' => $attempt->delivery_id,
                        'ip_address' => $attempt->ip_address,
                        'started_at' => $startedAt,
                        'ended_at' => $endedAt,
                        'extra_minute' => $attempt->extra_minute ?? 0,
                        'score' => $attempt->score,
                        'progress' => $attempt->progress ?? 0,
                        'penalty' => $attempt->penalty ?? 0,
                        'finish_scoring' => $attempt->finish_scoring ?? 0,
                        'finished_at' => $endedAt,
                        'client_id' => $this->targetClientId,
                        'created_at' => $createdAt,
                        'updated_at' => $updatedAt,
                    ]);
                    $imported++;
                } else {
                    $skipped++;
                }
            } catch (\Exception $e) {
                $this->warn("Skipping attempt {$attempt->id}: " . $e->getMessage());
                $skipped++;
            }
        }
        
        $this->info("Imported {$imported} attempts, skipped {$skipped}");
    }

    private function importAttemptQuestions()
    {
        $this->info('Importing attempt questions...');
        
        $mysqlAQ = $this->mysqlConnection->table('attempt_question')->get();
        
        foreach ($mysqlAQ as $aq) {
            DB::table('attempt_question')->insert([
                'attempt_id' => $aq->attempt_id,
                'question_id' => $aq->question_id,
                'order' => $aq->order ?? 0,
                'created_at' => $aq->created_at ?? now(),
                'updated_at' => $aq->updated_at ?? now(),
            ]);
        }
        
        $this->info("Imported {$mysqlAQ->count()} attempt questions");
    }

    private function importAttemptAnswers()
    {
        $this->info('Importing attempt answers...');
        
        if ($this->mysqlConnection->getSchemaBuilder()->hasTable('attempt_answers')) {
            $mysqlAA = $this->mysqlConnection->table('attempt_answers')->get();
            
            foreach ($mysqlAA as $aa) {
                DB::table('attempt_answers')->insert([
                    'attempt_id' => $aa->attempt_id,
                    'question_id' => $aa->question_id,
                    'answer_id' => $aa->answer_id ?? null,
                    'answer_text' => $aa->answer_text ?? null,
                    'created_at' => $aa->created_at ?? now(),
                    'updated_at' => $aa->updated_at ?? now(),
                ]);
            }
            
            $this->info("Imported {$mysqlAA->count()} attempt answers");
        }
    }

    private function runPhase5Verification()
    {
        $this->info("\n=== PHASE 5: VERIFICATION & TESTING ===");
        
        $this->showCurrentCounts('AFTER IMPORT');
        $this->verifyForeignKeyIntegrity();
        $this->generateHashValues();
    }

    private function showCurrentCounts($title)
    {
        $this->info("\n=== {$title} ===");
        
        $tables = [
            'categories', 'items', 'questions', 'answers',
            'groups', 'takers', 'exams', 'deliveries', 'attempts'
        ];
        
        foreach ($tables as $table) {
            try {
                $count = DB::table($table)->count();
                $this->info("  {$table}: {$count}");
            } catch (\Exception $e) {
                $this->info("  {$table}: ERROR");
            }
        }
    }

    private function verifyForeignKeyIntegrity()
    {
        $this->info('Verifying foreign key integrity...');
        
        // Check key relationships
        $checks = [
            'questions without items' => "SELECT COUNT(*) FROM questions q LEFT JOIN items i ON q.item_id = i.id WHERE i.id IS NULL",
            'answers without questions' => "SELECT COUNT(*) FROM answers a LEFT JOIN questions q ON a.question_id = q.id WHERE q.id IS NULL",
            'attempts without deliveries' => "SELECT COUNT(*) FROM attempts a LEFT JOIN deliveries d ON a.delivery_id = d.id WHERE d.id IS NULL",
        ];
        
        foreach ($checks as $name => $query) {
            try {
                $result = DB::select($query)[0];
                $count = array_values((array)$result)[0];
                
                if ($count > 0) {
                    $this->warn("Found {$count} {$name}");
                } else {
                    $this->info("✓ No {$name}");
                }
            } catch (\Exception $e) {
                $this->warn("Could not check {$name}: " . $e->getMessage());
            }
        }
    }

    private function generateHashValues()
    {
        $this->info('Generating hash values...');
        
        $models = [
            'categories' => \App\Models\Categories\Category::class,
            'items' => \App\Models\Exams\Item::class,
            'questions' => \App\Models\Exams\Question::class,
            'answers' => \App\Models\Exams\Answer::class,
            'groups' => \App\Models\Takers\Group::class,
            'exams' => \App\Models\Exams\Exam::class,
            'deliveries' => \App\Models\Deliveries\Delivery::class,
            'attempts' => \App\Models\Attempts\Attempt::class,
        ];
        
        foreach ($models as $tableName => $modelClass) {
            try {
                if (class_exists($modelClass)) {
                    $count = $modelClass::whereNull('hash')->count();
                    if ($count > 0) {
                        $this->info("Generating hashes for {$count} {$tableName} records...");
                        
                        // Generate hashes in batches to avoid memory issues
                        $batchGenerated = 0;
                        $modelClass::whereNull('hash')->chunk(100, function ($records) use (&$batchGenerated, $tableName) {
                            foreach ($records as $record) {
                                try {
                                    // Force hash generation by accessing the hash property and saving
                                    $hashValue = $record->hash; // This should trigger the HashableId trait
                                    if ($hashValue) {
                                        // Update the hash column directly
                                        $record->update(['hash' => $hashValue]);
                                        $batchGenerated++;
                                    }
                                } catch (\Exception $e) {
                                    // Skip individual record errors but log first few
                                    if ($batchGenerated < 3) {
                                        $this->warn("Error generating hash for {$tableName} ID {$record->id}: " . $e->getMessage());
                                    }
                                }
                            }
                        });
                        
                        $newCount = $modelClass::whereNull('hash')->count();
                        $actualGenerated = $count - $newCount;
                        $this->info("Generated hashes for {$actualGenerated} {$tableName} records");
                    } else {
                        $this->info("All {$tableName} records already have hashes");
                    }
                } else {
                    $this->warn("Model class {$modelClass} not found");
                }
            } catch (\Exception $e) {
                $this->warn("Could not generate hashes for {$tableName}: " . $e->getMessage());
            }
        }
    }

    private function validateAndCleanDate($dateValue)
    {
        // Return null if the value is null
        if ($dateValue === null) {
            return null;
        }

        // Convert to string and trim whitespace
        $dateStr = trim((string) $dateValue);

        // List of invalid date patterns that should be converted to null
        $invalidPatterns = [
            '',                          // Empty string
            '0',                         // Zero as string
            '00-00-00',                  // Zero date short format
            '0000-00-00',                // Zero date MySQL format
            '0000-00-00 00:00:00',       // Zero datetime MySQL format
            '00:00:00',                  // Zero time
            '1970-01-01 00:00:00',       // Unix epoch (sometimes used as null)
            '1970-01-01',                // Unix epoch date only
        ];

        // Check for exact matches with invalid patterns
        if (in_array($dateStr, $invalidPatterns)) {
            return null;
        }

        // Check for patterns that start with invalid dates
        if (preg_match('/^(0000-00-00|00-00-00|0000\/00\/00|00\/00\/00)/', $dateStr)) {
            return null;
        }

        // Check if it's just a number (timestamp that's too small/invalid)
        if (is_numeric($dateStr) && (int)$dateStr <= 0) {
            return null;
        }

        // Try to parse the date to see if it's valid
        try {
            // Handle various date formats that might be valid
            $timestamp = strtotime($dateStr);
            
            // If strtotime fails, return null
            if ($timestamp === false) {
                return null;
            }

            // Check if the timestamp is reasonable (after 1900 and before 2100)
            if ($timestamp < strtotime('1900-01-01') || $timestamp > strtotime('2100-01-01')) {
                return null;
            }

            // Convert back to MySQL datetime format
            return date('Y-m-d H:i:s', $timestamp);
            
        } catch (\Exception $e) {
            // If any exception occurs during parsing, return null
            return null;
        }
    }
}