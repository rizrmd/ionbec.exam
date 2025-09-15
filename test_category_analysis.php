<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Categories\Category;

class AnalyzeCategoryData extends Command
{
    protected $signature = 'analyze:category-data';
    protected $description = 'Analyze category data and hash behavior';

    public function handle()
    {
        $this->info('Analyzing category data and hash behavior...');
        
        // Get first few categories
        $categories = Category::take(3)->get();
        
        foreach ($categories as $category) {
            $this->info("\n--- Category ID: {$category->id} ---");
            $this->info("Name: {$category->name}");
            
            // Check hash via accessor
            try {
                $hashViaAccessor = $category->hash;
                $this->info("Hash (via accessor): {$hashViaAccessor}");
            } catch (\Exception $e) {
                $this->warn("Error accessing hash: " . $e->getMessage());
            }
            
            // Check hash via raw attributes
            $rawAttributes = $category->getAttributes();
            $hashInDb = $rawAttributes['hash'] ?? 'NULL';
            $this->info("Hash (in DB): {$hashInDb}");
            
            // Try to save to trigger hash generation
            try {
                $category->save();
                $category->refresh();
                $newHashInDb = $category->getAttributes()['hash'] ?? 'NULL';
                $this->info("Hash (after save): {$newHashInDb}");
            } catch (\Exception $e) {
                $this->warn("Error saving: " . $e->getMessage());
            }
        }
        
        // Check database directly
        $this->info("\n--- Database Analysis ---");
        $totalCategories = Category::count();
        $categoriesWithHash = Category::whereNotNull('hash')->count();
        $categoriesWithoutHash = Category::whereNull('hash')->count();
        
        $this->info("Total categories: {$totalCategories}");
        $this->info("Categories with hash: {$categoriesWithHash}");
        $this->info("Categories without hash: {$categoriesWithoutHash}");
        
        return 0;
    }
}