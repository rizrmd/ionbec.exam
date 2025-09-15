<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AnalyzeCategoryData extends Command
{
    protected $signature = 'categories:analyze';
    protected $description = 'Analyze categories and questions data between MySQL and PostgreSQL';

    private $mysqlConnection;

    public function handle()
    {
        $this->info('Analyzing categories and questions data...');

        // Connect to MySQL reference database
        $this->connectToMysql();

        // Compare data between databases
        $this->compareData();
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

    private function compareData()
    {
        $this->info("\n=== DATA COMPARISON ===");
        
        // Question ID ranges
        $mysqlMinQ = $this->mysqlConnection->table('questions')->min('id');
        $mysqlMaxQ = $this->mysqlConnection->table('questions')->max('id');
        $pgMinQ = DB::table('questions')->min('id');
        $pgMaxQ = DB::table('questions')->max('id');
        
        $this->info("Question ID ranges:");
        $this->info("  MySQL: {$mysqlMinQ} - {$mysqlMaxQ}");
        $this->info("  PostgreSQL: {$pgMinQ} - {$pgMaxQ}");
        
        // Find common question IDs
        $mysqlQuestionIds = $this->mysqlConnection->table('questions')->pluck('id')->toArray();
        $pgQuestionIds = DB::table('questions')->pluck('id')->toArray();
        
        $commonQuestionIds = array_intersect($mysqlQuestionIds, $pgQuestionIds);
        $mysqlOnlyIds = array_diff($mysqlQuestionIds, $pgQuestionIds);
        $pgOnlyIds = array_diff($pgQuestionIds, $mysqlQuestionIds);
        
        $this->info("\nQuestion ID overlap:");
        $this->info("  Common questions: " . count($commonQuestionIds));
        $this->info("  MySQL only: " . count($mysqlOnlyIds));
        $this->info("  PostgreSQL only: " . count($pgOnlyIds));
        
        // Show some examples of missing questions
        if (count($mysqlOnlyIds) > 0) {
            $this->info("\nFirst 10 MySQL-only question IDs:");
            $this->info("  " . implode(', ', array_slice($mysqlOnlyIds, 0, 10)));
        }
        
        // Now sync relationships for common questions only
        $this->syncCommonRelationships($commonQuestionIds);
    }

    private function syncCommonRelationships($commonQuestionIds)
    {
        $this->info("\n=== SYNCING RELATIONSHIPS FOR COMMON QUESTIONS ===");
        
        // Clear existing relationships
        DB::table('category_question')->truncate();
        
        // Get relationships from MySQL for common questions only
        $mysqlRelations = $this->mysqlConnection->table('category_question')
            ->whereIn('question_id', $commonQuestionIds)
            ->get();
            
        $this->info("Found {$mysqlRelations->count()} relationships for common questions");
        
        $created = 0;
        $skipped = 0;
        
        foreach ($mysqlRelations as $relation) {
            // Check if category exists in PostgreSQL
            $pgCategory = DB::table('categories')->find($relation->category_id);
            
            if ($pgCategory) {
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
            }
        }
        
        $this->info("Successfully created {$created} relationships, skipped {$skipped}");
        
        // Show final results
        $this->showFinalResults();
    }

    private function showFinalResults()
    {
        $this->info("\n=== FINAL RESULTS ===");
        
        // Show category counts with questions
        $categoryTypes = DB::table('categories as c')
            ->leftJoin('category_question as cq', 'c.id', '=', 'cq.category_id')
            ->select('c.type', DB::raw('COUNT(DISTINCT c.id) as category_count'), DB::raw('COUNT(cq.question_id) as question_count'))
            ->groupBy('c.type')
            ->orderBy('c.type')
            ->get();
            
        $this->table(['Category Type', 'Categories', 'Questions'], $categoryTypes->map(function($item) {
            return [$item->type, $item->category_count, $item->question_count];
        })->toArray());
        
        // Overall totals
        $totalCategories = DB::table('categories')->count();
        $totalQuestions = DB::table('questions')->count();
        $totalRelationships = DB::table('category_question')->count();
        
        $this->info("Overall totals:");
        $this->info("  Categories: {$totalCategories}");
        $this->info("  Questions: {$totalQuestions}");
        $this->info("  Category-Question relationships: {$totalRelationships}");
    }
}