<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestTakersImport extends Command
{
    protected $signature = 'test:takers-import';
    protected $description = 'Test takers import with detailed diagnostics';

    private $mysqlConnection;
    private $targetClientId = 16;

    public function handle()
    {
        $this->info('Starting takers import diagnostic test...');
        
        // Connect to MySQL
        $this->connectToMysql();
        
        // Clear existing takers for clean test
        $this->info('Clearing existing takers...');
        DB::table('takers')->delete();
        
        // Run takers import with diagnostics
        $this->importTakers();
        
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

    private function importTakers()
    {
        $this->info('Importing takers with detailed diagnostics...');
        
        try {
            $mysqlTakers = $this->mysqlConnection->table('takers')->get();
            $this->info("Found {$mysqlTakers->count()} takers in MySQL");
            
            $imported = 0;
            $skipped = 0;
            $duplicates = 0;
            $errors = 0;
            $errorReasons = [];
            
            // Sample a few records to examine data quality
            $this->info("\nSampling first 5 taker records:");
            foreach ($mysqlTakers->take(5) as $index => $taker) {
                $this->info("Taker {$index}: id={$taker->id}, name='{$taker->name}', email='{$taker->email}', reg='{$taker->reg}'");
            }
            
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
                        if ($skipped <= 3) {
                            $this->warn("Skipping taker {$taker->id}: empty email");
                        }
                        continue;
                    }
                    
                    if (empty($taker->name)) {
                        $skipped++;
                        $errorReasons['empty_name'] = ($errorReasons['empty_name'] ?? 0) + 1;
                        if ($skipped <= 3) {
                            $this->warn("Skipping taker {$taker->id}: empty name");
                        }
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
                    
                    if ($errors <= 10) {
                        $this->warn("Error importing taker {$taker->id} ({$taker->email}): {$errorMessage}");
                    }
                }
            }
            
            $this->info("\n=== IMPORT SUMMARY ===");
            $this->info("  Total in MySQL: {$mysqlTakers->count()}");
            $this->info("  Imported: {$imported}");
            $this->info("  Duplicates: {$duplicates}");
            $this->info("  Validation skips: {$skipped}");
            $this->info("  Errors: {$errors}");
            
            if (!empty($errorReasons)) {
                $this->info("\nError breakdown:");
                foreach ($errorReasons as $reason => $count) {
                    $this->info("  {$reason}: {$count}");
                }
            }
            
        } catch (\Exception $e) {
            $this->error("Failed to import takers: " . $e->getMessage());
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