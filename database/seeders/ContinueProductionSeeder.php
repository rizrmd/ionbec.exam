<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use League\Csv\Exception;
use Illuminate\Support\Str;
use App\Jobs\CalculateScore;
use App\Models\Exams\Answer;
use Illuminate\Database\Seeder as BaseSeeder;
use App\Models\Attempts\Attempt;
use Illuminate\Support\Facades\DB;
use App\Models\Categories\Category;
use Symfony\Component\Finder\Finder;
use App\Models\Attempts\AttemptQuestion;
use App\Concerns\Collection\CsvIntoCollection;
use App\Services\RustService;

class ContinueProductionSeeder extends BaseSeeder
{
    use CsvIntoCollection;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->command->info('Continuing CSV restoration from where it left off...');

        // Only run patches that haven't been completed
        // Patch 1 was partially completed (categories, exams, items done, but not users/takers)
        $this->restoreFromCsv('1', ['users', 'takers']); // Only process remaining tables from patch 1
        $this->restoreFromCsv('2'); // Process all of patch 2
        $this->restoreFromCsv('3'); // Process all of patch 3
        $this->restoreFromCsv('4'); // Process all of patch 4

        $this->generateAnswerMultiple();
        $this->generateScore();

        DB::statement('UPDATE deliveries SET display_name = name');
        $this->command->info('✅ CSV restoration completed!');
    }

    private function restoreFromCsv($patch, $onlyTables = []): void
    {
        $finder = new Finder();
        $finder->files()->in(__DIR__.'/data/restore/'.$patch);
        $finder->sortByName();

        $tables = collect();

        // Check if Rust service is enabled
        $useRustService = env('USE_RUST_CSV_PROCESSING', true);

        if ($useRustService) {
            $this->restoreFromCsvRust($finder, $tables, $patch, $onlyTables);
        } else {
            $this->restoreFromCsvPhp($finder, $tables, $onlyTables);
        }
    }

    /**
     * Restore CSV files using Rust service for high performance
     */
    private function restoreFromCsvRust($finder, $tables, $patch, $onlyTables = []): void
    {
        $this->command->info("Using Rust service for CSV processing (patch: {$patch})");

        try {
            $rustService = app(RustService::class);

            // Check if Rust service is available
            $health = $rustService->health();
            if ($health['status'] !== 'healthy') {
                $this->command->warn('Rust service is not healthy, falling back to PHP processing');
                $this->restoreFromCsvPhp($finder, $tables, $onlyTables);
                return;
            }

            // Prepare files for batch processing
            $files = [];
            foreach ($finder as $file) {
                $tableName = Str::after($file->getFilenameWithoutExtension(), 'table-');
                if ($tableName === '') {
                    $tableName = Str::after($file->getFilenameWithoutExtension(), 'restore-');
                }
                if (empty($onlyTables) || in_array($tableName, $onlyTables)) {
                    $tables->push($tableName);

                    $files[] = [
                        'file_path' => $file->getRealPath(),
                        'table_name' => $tableName
                    ];
                }
            }

            if (empty($files)) {
                $this->command->info('No matching CSV files found to process');
                return;
            }

            $this->command->info('Processing ' . count($files) . ' CSV files with Rust service...');

            $startTime = microtime(true);
            $result = $rustService->processCsvBatch($files, 5000);
            $processingTime = (microtime(true) - $startTime) * 1000;

            if ($result['success']) {
                $this->command->info("✅ Rust CSV processing completed successfully!");
                $this->command->info("   - Total files: {$result['total_files']}");
                $this->command->info("   - Successful: {$result['successful_files']}");
                $this->command->info("   - Failed: {$result['failed_files']}");
                $this->command->info("   - Total records: {$result['total_records']}");
                $this->command->info("   - Processing time: {$result['total_processing_time_ms']}ms");

                foreach ($result['results'] as $fileResult) {
                    if (!$fileResult['success']) {
                        $this->command->error("   ❌ {$fileResult['table_name']}: {$fileResult['error']}");
                    } else {
                        $this->command->line("   ✅ {$fileResult['table_name']}: {$fileResult['records_processed']} records");
                    }
                }
            } else {
                $this->command->error("❌ Rust CSV processing failed: {$result['error']}");
                $this->command->warn('Falling back to PHP processing');
                $this->restoreFromCsvPhp($finder, $tables, $onlyTables);
            }

        } catch (\Exception $e) {
            $this->command->error("❌ Rust service error: {$e->getMessage()}");
            $this->command->warn('Falling back to PHP processing');
            $this->restoreFromCsvPhp($finder, $tables, $onlyTables);
        }
    }

    /**
     * Original PHP CSV processing (fallback)
     */
    private function restoreFromCsvPhp($finder, $tables, $onlyTables = []): void
    {
        $this->command->info('Using PHP for CSV processing (fallback)');

        foreach ($finder as $file) {
            $tableName = Str::after($file->getFilenameWithoutExtension(), 'table-');
            if ($tableName === '') {
                $tableName = Str::after($file->getFilenameWithoutExtension(), 'restore-');
            }

            // Skip if not in the onlyTables list (if specified)
            if (!empty($onlyTables) && !in_array($tableName, $onlyTables)) {
                continue;
            }

            $this->command->info('Restoring from CSV : '.$file->getRealPath());

            try {
                $records = $this->loadFiles($file->getRealPath());
                $tables->push($tableName);

                $records->each(function ($record) use ($tableName) {
                    // Convert empty values to null
                    foreach ($record as $key => $value) {
                        if (empty($value) && ! is_numeric($value)) {
                            unset($record[$key]);
                        }
                    }

                    DB::table($tableName)->insert($record);
                });

                $this->command->info("✅ Imported {$records->count()} records into {$tableName}");
            } catch (Exception $e) {
                $this->command->alert('Failed to restore CSV : '.$file->getRealPath());
                $this->command->error($e->getMessage());
            }
        }
    }

    public function generateScore()
    {
        $this->command->info('Start generate score...');
        $attempts = Attempt::query()->get();
        foreach ($attempts as $attempt) {
            dispatch(new CalculateScore($attempt));
        }
    }

    public function generateAnswerMultiple()
    {
        $this->command->info('Start generate multiple score answer...');
        $attemptQ = AttemptQuestion::query()->get();

        foreach ($attemptQ as $item) {
            if (is_numeric($item->answer)) {
                try {
                    $item->answer_id = $item->answer;
                    $item->answer_hash = Answer::idToHash($item->answer);
                    $item->save();
                } catch (Exception $e) {
                    $this->command->alert('Failed to convert attempt_questions answer : '.$item->answer);
                }
            }
        }
    }
}