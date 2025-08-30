<?php

namespace Database\Seeders;

use League\Csv\Exception;
use Illuminate\Support\Str;
use App\Jobs\CalculateScore;
use App\Models\Exams\Answer;
use Illuminate\Database\Seeder;
use App\Models\Attempts\Attempt;
use Illuminate\Support\Facades\DB;
use App\Models\Categories\Category;
use Symfony\Component\Finder\Finder;
use App\Models\Attempts\AttemptQuestion;
use App\Concerns\Collection\CsvIntoCollection;
use App\Services\RustService;

class ProductionSeeder extends Seeder
{
    use CsvIntoCollection;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        //        if (Category::query()->count() === 0) {
        //            $this->call(CategoriesTableSeeder::class);
        //        }

        $this->restoreFromCsv('1');
        $this->restoreFromCsv('2');
        $this->restoreFromCsv('3');
        $this->restoreFromCsv('4');

        $this->generateAnswerMultiple();
        $this->generateScore();

        DB::statement('UPDATE deliveries SET display_name = name');
        $this->call(GenerateTakerCodeSeeder::class);
    }

    private function restoreFromCsv($patch): void
    {
        $finder = new Finder();

        $finder->files()->in(__DIR__.'/data/restore/'.$patch);
        $finder->sortByName();

        $tables = collect();

        // Check if Rust service is enabled
        $useRustService = env('USE_RUST_CSV_PROCESSING', true);
        
        if ($useRustService) {
            $this->restoreFromCsvRust($finder, $tables, $patch);
        } else {
            $this->restoreFromCsvPhp($finder, $tables);
        }

        //        $this->command->info('fixing table autoincrement');
        //
        //        collect(DB::select("SELECT c.relname FROM pg_class c WHERE c.relkind = 'S';"))
        //            ->map(fn ($row) => [
        //                'relname' => $row->relname,
        //                'table_name' => Str::before($row->relname, '_id_seq'),
        //            ])
        //            ->filter(fn ($row) => $tables->contains($row['table_name']))
        //            ->map(fn ($row) => array_merge($row, [
        //                'last_id' => (int) DB::selectOne('SELECT id FROM '.$row['table_name'].' ORDER BY id DESC')?->id,
        //            ]))
        //            ->each(fn ($row) => DB::statement('ALTER SEQUENCE '.$row['relname'].' RESTART WITH '.($row['last_id'] + 1)));
    }

    /**
     * Restore CSV files using Rust service for high performance
     */
    private function restoreFromCsvRust($finder, $tables, $patch): void
    {
        $this->command->info("Using Rust service for CSV processing (patch: {$patch})");
        
        try {
            $rustService = app(RustService::class);
            
            // Check if Rust service is available
            $health = $rustService->health();
            if ($health['status'] !== 'healthy') {
                $this->command->warn('Rust service is not healthy, falling back to PHP processing');
                $this->restoreFromCsvPhp($finder, $tables);
                return;
            }
            
            // Prepare files for batch processing
            $files = [];
            foreach ($finder as $file) {
                $tableName = Str::after($file->getFilenameWithoutExtension(), 'table-');
                $tables->push($tableName);
                
                $files[] = [
                    'file_path' => $file->getRealPath(),
                    'table_name' => $tableName
                ];
            }
            
            if (empty($files)) {
                $this->command->info('No CSV files found to process');
                return;
            }
            
            $this->command->info('Processing ' . count($files) . ' CSV files with Rust service...');
            
            $startTime = microtime(true);
            $result = $rustService->processCsvBatch($files, 5000); // Larger batch size for better performance
            $processingTime = (microtime(true) - $startTime) * 1000;
            
            if ($result['success']) {
                $this->command->info("✅ Rust CSV processing completed successfully!");
                $this->command->info("   - Total files: {$result['total_files']}");
                $this->command->info("   - Successful: {$result['successful_files']}");
                $this->command->info("   - Failed: {$result['failed_files']}");
                $this->command->info("   - Total records: {$result['total_records']}");
                $this->command->info("   - Processing time: {$result['total_processing_time_ms']}ms");
                $this->command->info("   - Total time (including network): {$processingTime}ms");
                
                // Log individual file results for debugging
                foreach ($result['results'] as $fileResult) {
                    if (!$fileResult['success']) {
                        $this->command->error("   ❌ {$fileResult['table_name']}: {$fileResult['error']}");
                    } else {
                        $this->command->line("   ✅ {$fileResult['table_name']}: {$fileResult['records_processed']} records in {$fileResult['processing_time_ms']}ms");
                    }
                }
                
                if ($result['failed_files'] > 0) {
                    $this->command->warn('Some files failed to process. Check logs for details.');
                }
            } else {
                $this->command->error("❌ Rust CSV processing failed: {$result['error']}");
                $this->command->warn('Falling back to PHP processing');
                $this->restoreFromCsvPhp($finder, $tables);
            }
            
        } catch (\Exception $e) {
            $this->command->error("❌ Rust service error: {$e->getMessage()}");
            $this->command->warn('Falling back to PHP processing');
            $this->restoreFromCsvPhp($finder, $tables);
        }
    }
    
    /**
     * Original PHP CSV processing (fallback)
     */
    private function restoreFromCsvPhp($finder, $tables): void
    {
        $this->command->info('Using PHP for CSV processing (fallback)');
        
        foreach ($finder as $file) {
            $this->command->info('Restoring from CSV : '.$file->getRealPath());

            try {
                $records = $this->loadFiles($file->getRealPath());

                $tableName = Str::after($file->getFilenameWithoutExtension(), 'table-');

                $tables->push($tableName);

                $records->each(function ($record) use ($tableName) {
                    // exorcise the empty value into null
                    foreach ($record as $key => $value) {
                        if (empty($value) && ! is_numeric($value)) {
                            unset($record[$key]);
                        }
                    }

                    DB::table($tableName)->insert($record);
                });
            } catch (Exception $e) {
                $this->command->alert('Failed to restore CSV : '.$file->getRealPath());
                dd($e->getMessage());
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
