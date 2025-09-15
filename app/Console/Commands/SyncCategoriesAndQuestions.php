<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Categories\Category;
use App\Models\Exams\Question;
use App\Models\Exams\Item;

class SyncCategoriesAndQuestions extends Command
{
    protected $signature = 'categories:sync-from-mysql';
    protected $description = 'Sync categories and questions from MySQL reference database to PostgreSQL';

    private $mysqlConnection;

    public function handle()
    {
        $this->info('Starting categories and questions synchronization...');

        // Connect to MySQL reference database
        $this->connectToMysql();

        // Analyze current state
        $this->analyzeCurrentState();

        // Sync categories first
        $this->syncCategories();

        // Sync items
        $this->syncItems();

        // Sync questions  
        $this->syncQuestions();

        // Sync category-question relationships
        $this->syncCategoryQuestionRelationships();

        $this->info('Categories and questions synchronization completed!');
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

    private function analyzeCurrentState()
    {
        $this->info("\n=== ANALYZING CURRENT STATE ===");
        
        // MySQL state
        $mysqlCategories = $this->mysqlConnection->table('categories')->count();
        $mysqlItems = $this->mysqlConnection->table('items')->count();
        $mysqlQuestions = $this->mysqlConnection->table('questions')->count();
        $mysqlCategoryQuestion = $this->mysqlConnection->table('category_question')->count();
        
        $this->info("MySQL Reference Database:");
        $this->info("  Categories: {$mysqlCategories}");
        $this->info("  Items: {$mysqlItems}");
        $this->info("  Questions: {$mysqlQuestions}");
        $this->info("  Category-Question links: {$mysqlCategoryQuestion}");
        
        // PostgreSQL state
        $pgCategories = DB::table('categories')->count();
        $pgItems = DB::table('items')->count();
        $pgQuestions = DB::table('questions')->count();
        $pgCategoryQuestion = DB::table('category_question')->count();
        
        $this->info("\nPostgreSQL Current Database:");
        $this->info("  Categories: {$pgCategories}");
        $this->info("  Items: {$pgItems}");
        $this->info("  Questions: {$pgQuestions}");
        $this->info("  Category-Question links: {$pgCategoryQuestion}");
    }

    private function syncCategories()
    {
        $this->info("\n=== SYNCING CATEGORIES ===");
        
        $mysqlCategories = $this->mysqlConnection->table('categories')->get();
        
        foreach ($mysqlCategories as $mysqlCategory) {
            // Check if category already exists
            $existingCategory = DB::table('categories')
                ->where('name', $mysqlCategory->name)
                ->where('type', $mysqlCategory->type)
                ->first();
                
            if (!$existingCategory) {
                try {
                    DB::table('categories')->insert([
                        'name' => $mysqlCategory->name,
                        'description' => $mysqlCategory->description,
                        'type' => $mysqlCategory->type,
                        'code' => $mysqlCategory->code,
                        'parent' => $mysqlCategory->parent ?? 0,
                        'client_id' => 1, // Default client_id
                        'created_at' => $mysqlCategory->created_at,
                        'updated_at' => $mysqlCategory->updated_at,
                    ]);
                    
                    $this->info("Created category: {$mysqlCategory->name} (Type: {$mysqlCategory->type})");
                } catch (\Exception $e) {
                    $this->error("Failed to create category '{$mysqlCategory->name}': " . $e->getMessage());
                }
            } else {
                $this->line("Category already exists: {$mysqlCategory->name}");
            }
        }
    }

    private function syncItems()
    {
        $this->info("\n=== SYNCING ITEMS ===");
        
        $mysqlItems = $this->mysqlConnection->table('items')->get();
        
        foreach ($mysqlItems as $mysqlItem) {
            // Check if item already exists by title and content
            $existingItem = DB::table('items')
                ->where('title', $mysqlItem->title)
                ->where('content', $mysqlItem->content)
                ->first();
                
            if (!$existingItem) {
                try {
                    DB::table('items')->insert([
                        'id' => $mysqlItem->id, // Keep same ID for foreign key relationships
                        'title' => $mysqlItem->title,
                        'content' => $mysqlItem->content,
                        'type' => $mysqlItem->type,
                        'is_vignette' => $mysqlItem->is_vignette ?? 0,
                        'is_random' => $mysqlItem->is_random ?? 0,
                        'score' => $mysqlItem->score ?? 0,
                        'client_id' => 1, // Default client_id
                        'created_at' => $mysqlItem->created_at,
                        'updated_at' => $mysqlItem->updated_at,
                    ]);
                    
                    $this->info("Created item: {$mysqlItem->title}");
                } catch (\Exception $e) {
                    $this->error("Failed to create item '{$mysqlItem->title}': " . $e->getMessage());
                }
            } else {
                $this->line("Item already exists: {$mysqlItem->title}");
            }
        }
    }

    private function syncQuestions()
    {
        $this->info("\n=== SYNCING QUESTIONS ===");
        
        $mysqlQuestions = $this->mysqlConnection->table('questions')->get();
        
        foreach ($mysqlQuestions as $mysqlQuestion) {
            // Check if question already exists
            $existingQuestion = DB::table('questions')
                ->where('item_id', $mysqlQuestion->item_id)
                ->where('question', $mysqlQuestion->question)
                ->first();
                
            if (!$existingQuestion) {
                try {
                    DB::table('questions')->insert([
                        'id' => $mysqlQuestion->id, // Keep same ID for relationships
                        'item_id' => $mysqlQuestion->item_id,
                        'type' => $mysqlQuestion->type ?? 'simple',
                        'question' => $mysqlQuestion->question,
                        'is_random' => $mysqlQuestion->is_random ?? 0,
                        'score' => $mysqlQuestion->score ?? 100,
                        'order' => $mysqlQuestion->order ?? 0,
                        'client_id' => 1, // Default client_id
                        'created_at' => $mysqlQuestion->created_at,
                        'updated_at' => $mysqlQuestion->updated_at,
                    ]);
                    
                    $this->info("Created question ID: {$mysqlQuestion->id}");
                } catch (\Exception $e) {
                    $this->error("Failed to create question ID '{$mysqlQuestion->id}': " . $e->getMessage());
                }
            } else {
                $this->line("Question already exists: ID {$mysqlQuestion->id}");
            }
        }
    }

    private function syncCategoryQuestionRelationships()
    {
        $this->info("\n=== SYNCING CATEGORY-QUESTION RELATIONSHIPS ===");
        
        $mysqlRelations = $this->mysqlConnection->table('category_question')->get();
        
        foreach ($mysqlRelations as $relation) {
            // Get PostgreSQL category and question IDs
            $pgCategory = DB::table('categories')->find($relation->category_id);
            $pgQuestion = DB::table('questions')->find($relation->question_id);
            
            if ($pgCategory && $pgQuestion) {
                // Check if relationship already exists
                $existingRelation = DB::table('category_question')
                    ->where('category_id', $relation->category_id)
                    ->where('question_id', $relation->question_id)
                    ->first();
                    
                if (!$existingRelation) {
                    try {
                        DB::table('category_question')->insert([
                            'category_id' => $relation->category_id,
                            'question_id' => $relation->question_id,
                        ]);
                        
                        $this->info("Linked category {$relation->category_id} with question {$relation->question_id}");
                    } catch (\Exception $e) {
                        $this->error("Failed to link category {$relation->category_id} with question {$relation->question_id}: " . $e->getMessage());
                    }
                } else {
                    $this->line("Relationship already exists: Category {$relation->category_id} <-> Question {$relation->question_id}");
                }
            } else {
                $this->warn("Missing category or question for relationship: Category {$relation->category_id} <-> Question {$relation->question_id}");
            }
        }
        
        // Final summary
        $this->showFinalSummary();
    }

    private function showFinalSummary()
    {
        $this->info("\n=== FINAL SUMMARY ===");
        
        // PostgreSQL final state
        $pgCategories = DB::table('categories')->count();
        $pgItems = DB::table('items')->count();
        $pgQuestions = DB::table('questions')->count();
        $pgCategoryQuestion = DB::table('category_question')->count();
        
        $this->info("PostgreSQL After Sync:");
        $this->info("  Categories: {$pgCategories}");
        $this->info("  Items: {$pgItems}");
        $this->info("  Questions: {$pgQuestions}");
        $this->info("  Category-Question links: {$pgCategoryQuestion}");
        
        // Show category types and counts
        $categoryTypes = DB::table('categories')
            ->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get();
            
        $this->info("\nCategory Types:");
        foreach ($categoryTypes as $type) {
            $this->info("  {$type->type}: {$type->count}");
        }
    }
}