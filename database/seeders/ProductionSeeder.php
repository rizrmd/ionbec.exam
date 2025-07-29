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
