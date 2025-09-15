<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckCategoryTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:category-types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check category types in the database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Checking category types in database...');
        
        // Check what category types we have
        $categoryTypes = \DB::table('categories')->select('type')->distinct()->pluck('type');
        
        $this->info('Category types in database:');
        foreach ($categoryTypes as $type) {
            $this->info("- {$type}");
        }
        
        // Count categories by type
        $this->info("\nCategory counts by type:");
        $counts = \DB::table('categories')->selectRaw('type, count(*) as count')->groupBy('type')->get();
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
