<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncCategoryQuestionRelationships extends Command
{
    protected $signature = 'categories:sync-relationships';
    protected $description = 'Sync category-question relationships from MySQL reference database';

    private $mysqlConnection;

    public function handle()
    {
        $this->info('Starting category-question relationships synchronization...');

        // Connect to MySQL reference database
        $this->connectToMysql();

        // Clear existing relationships first
        $this->clearExistingRelationships();

        // Sync category-question relationships
        $this->syncCategoryQuestionRelationships();

        $this->info('Category-question relationships synchronization completed!');
    }

    private function connectToMysql()
    {
        // Add MySQL connection configuration temporarily
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
            $this->info('Successfully connected to MySQL reference database');
        } catch (\Exception $e) {
            $this->error('Failed to connect to MySQL: ' . $e->getMessage());
            exit(1);
        }
    }

    private function clearExistingRelationships()
    {
        $this->info('Clearing existing category-question relationships...');
        DB::table('category_question')->truncate();
        $this->info('Cleared all existing relationships');
    }

    private function syncCategoryQuestionRelationships()
    {
        $this->info('Syncing category-question relationships...');
        
        // Get all relationships from MySQL
        $mysqlRelations = $this->mysqlConnection->table('category_question')->get();
        $this->info("Found {$mysqlRelations->count()} relationships in MySQL");
        
        $created = 0;
        $skipped = 0;
        
        foreach ($mysqlRelations as $relation) {
            // Check if both category and question exist in PostgreSQL
            $pgCategory = DB::table('categories')->find($relation->category_id);
            $pgQuestion = DB::table('questions')->find($relation->question_id);
            
            if ($pgCategory && $pgQuestion) {
                try {
                    DB::table('category_question')->insert([
                        'category_id' => $relation->category_id,
                        'question_id' => $relation->question_id,
                    ]);
                    
                    $created++;
                    
                    if ($created % 100 == 0) {
                        $this->info("Created {$created} relationships...");
                    }
                } catch (\Exception $e) {
                    $this->error("Failed to create relationship: " . $e->getMessage());
                    $skipped++;
                }
            } else {
                $skipped++;
                if (!$pgCategory) {
                    $this->warn("Category ID {$relation->category_id} not found in PostgreSQL");
                }
                if (!$pgQuestion) {
                    $this->warn("Question ID {$relation->question_id} not found in PostgreSQL");
                }
            }
        }
        
        $this->info("Created {$created} relationships, skipped {$skipped}");
        
        // Show final summary
        $this->showSummary();
    }

    private function showSummary()
    {
        $this->info("\n=== SUMMARY ===");
        
        // Show category counts with questions
        $categories = DB::table('categories as c')
            ->leftJoin('category_question as cq', 'c.id', '=', 'cq.category_id')
            ->select('c.id', 'c.name', 'c.type', DB::raw('COUNT(cq.question_id) as question_count'))
            ->groupBy('c.id', 'c.name', 'c.type')
            ->orderBy('c.type')
            ->get();
            
        $this->info("Categories with question counts:");
        
        $currentType = '';
        foreach ($categories as $category) {
            if ($category->type !== $currentType) {
                $currentType = $category->type;
                $this->info("\n{$currentType}:");
            }
            $this->info("  {$category->name}: {$category->question_count} questions");
        }
        
        // Overall totals
        $totalCategories = DB::table('categories')->count();
        $totalQuestions = DB::table('questions')->count();
        $totalRelationships = DB::table('category_question')->count();
        
        $this->info("\nTotals:");
        $this->info("  Categories: {$totalCategories}");
        $this->info("  Questions: {$totalQuestions}");
        $this->info("  Category-Question relationships: {$totalRelationships}");
    }
}