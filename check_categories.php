<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncCategoryQuestionRelationships extends Command
{
    protected $signature = 'check:category-types';
    protected $description = 'Check category types in the database';

    public function handle()
    {
        $this->info('Checking category types in database...');
        
        // Check what category types we have
        $categoryTypes = DB::table('categories')->select('type')->distinct()->pluck('type');
        
        $this->info('Category types in database:');
        foreach ($categoryTypes as $type) {
            $this->info("- {$type}");
        }
        
        // Count categories by type
        $this->info("\nCategory counts by type:");
        $counts = DB::table('categories')->selectRaw('type, count(*) as count')->groupBy('type')->get();
        foreach ($counts as $count) {
            $this->info("- {$count->type}: {$count->count}");
        }
        
        // Check expected types from enum
        $this->info("\nExpected category types from CategoryType enum:");
        $expectedTypes = ['disease-group', 'region-group', 'specific-part', 'typical-group'];
        foreach ($expectedTypes as $type) {
            $exists = $categoryTypes->contains($type);
            $this->info("- {$type}: " . ($exists ? 'EXISTS' : 'MISSING'));
        }
        
        return 0;
    }
}