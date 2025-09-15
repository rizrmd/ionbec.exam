<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncItemTypes extends Command
{
    protected $signature = 'items:sync-types';
    protected $description = 'Sync item types and vignette flags from MySQL reference database';

    private $mysqlConnection;

    public function handle()
    {
        $this->info('Syncing item types and vignette flags...');

        // Connect to MySQL reference database
        $this->connectToMysql();

        // Analyze current state
        $this->analyzeCurrentState();

        // Sync item types based on patterns
        $this->syncItemTypes();

        $this->info('Item types sync completed!');
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

    private function analyzeCurrentState()
    {
        $this->info("\n=== ANALYZING CURRENT STATE ===");
        
        // MySQL state
        $mysqlTypes = $this->mysqlConnection->table('items')
            ->select('type', 'is_vignette', DB::raw('count(*) as count'))
            ->groupBy('type', 'is_vignette')
            ->get();
            
        $this->info("MySQL Reference Database:");
        foreach ($mysqlTypes as $type) {
            $vignetteText = $type->is_vignette ? 'Vignette' : 'Non-Vignette';
            $this->info("  {$type->type} ({$vignetteText}): {$type->count}");
        }
        
        // PostgreSQL state for client 16
        $pgTypes = DB::table('items')
            ->where('client_id', 16)
            ->select('type', 'is_vignette', DB::raw('count(*) as count'))
            ->groupBy('type', 'is_vignette')
            ->get();
            
        $this->info("\nPostgreSQL Client 16:");
        foreach ($pgTypes as $type) {
            $vignetteText = $type->is_vignette ? 'Vignette' : 'Non-Vignette';
            $this->info("  {$type->type} ({$vignetteText}): {$type->count}");
        }
    }

    private function syncItemTypes()
    {
        $this->info("\n=== SYNCING ITEM TYPES ===");
        
        // Get items from client 16 that need type updates
        $pgItems = DB::table('items')->where('client_id', 16)->get();
        
        $updated = 0;
        $vignetteUpdated = 0;
        
        foreach ($pgItems as $item) {
            $needsUpdate = false;
            $updates = [];
            
            // Check if type needs to be updated based on content patterns
            $newType = $this->determineItemType($item);
            if ($newType !== $item->type) {
                $updates['type'] = $newType;
                $needsUpdate = true;
            }
            
            // Check if vignette flag needs to be updated
            $isVignette = $this->determineIfVignette($item);
            if ($isVignette !== (bool)$item->is_vignette) {
                $updates['is_vignette'] = $isVignette ? 1 : 0;
                $needsUpdate = true;
                $vignetteUpdated++;
            }
            
            if ($needsUpdate) {
                try {
                    DB::table('items')
                        ->where('id', $item->id)
                        ->update($updates);
                    $updated++;
                    
                    if ($updated % 100 == 0) {
                        $this->info("Updated {$updated} items...");
                    }
                } catch (\Exception $e) {
                    $this->error("Failed to update item {$item->id}: " . $e->getMessage());
                }
            }
        }
        
        $this->info("Updated {$updated} items total");
        $this->info("Updated vignette flags for {$vignetteUpdated} items");
        
        // Show final summary
        $this->showFinalSummary();
    }

    private function determineItemType($item)
    {
        // Check content for patterns that indicate essay vs multiple choice
        $content = strtolower($item->content ?? '');
        $title = strtolower($item->title ?? '');
        
        // Essay patterns
        if (strpos($content, 'essay') !== false || 
            strpos($title, 'essay') !== false ||
            strpos($title, 'osce') !== false ||
            strpos($content, 'explain') !== false ||
            strpos($content, 'describe') !== false ||
            strpos($content, 'discuss') !== false) {
            return 'essay';
        }
        
        // Interview patterns
        if (strpos($title, 'interview') !== false) {
            return 'interview';
        }
        
        // Default to multiple-choice
        return 'multiple-choice';
    }

    private function determineIfVignette($item)
    {
        $content = $item->content ?? '';
        $title = $item->title ?? '';
        
        // Vignette indicators (longer content, case presentations, etc.)
        if (strlen($content) > 500 || 
            strpos(strtolower($content), 'year') !== false ||
            strpos(strtolower($content), 'patient') !== false ||
            strpos(strtolower($content), 'examination') !== false ||
            strpos(strtolower($title), 'case') !== false) {
            return true;
        }
        
        return false;
    }

    private function showFinalSummary()
    {
        $this->info("\n=== FINAL SUMMARY ===");
        
        // Show updated statistics
        $finalTypes = DB::table('items')
            ->where('client_id', 16)
            ->select('type', 'is_vignette', DB::raw('count(*) as count'))
            ->groupBy('type', 'is_vignette')
            ->orderBy('type')
            ->get();
            
        $this->info("Updated PostgreSQL Client 16 statistics:");
        
        $totalMultipleChoice = 0;
        $totalEssay = 0;
        $totalVignette = 0;
        $totalNonVignette = 0;
        
        foreach ($finalTypes as $type) {
            $vignetteText = $type->is_vignette ? 'Vignette' : 'Non-Vignette';
            $this->info("  {$type->type} ({$vignetteText}): {$type->count}");
            
            if ($type->type === 'multiple-choice') {
                $totalMultipleChoice += $type->count;
            } elseif ($type->type === 'essay') {
                $totalEssay += $type->count;
            }
            
            if ($type->is_vignette) {
                $totalVignette += $type->count;
            } else {
                $totalNonVignette += $type->count;
            }
        }
        
        $this->info("\nSummary totals:");
        $this->info("  Total Multiple-Choice: {$totalMultipleChoice}");
        $this->info("  Total Essay: {$totalEssay}");
        $this->info("  Total Vignette: {$totalVignette}");
        $this->info("  Total Non-Vignette: {$totalNonVignette}");
    }
}