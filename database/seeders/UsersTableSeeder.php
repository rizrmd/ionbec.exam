<?php

namespace Database\Seeders;

use League\Csv\Reader;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Users table seeder.
 *
 * @author      veelasky <veelasky@gmail.com>
 */
class UsersTableSeeder extends Seeder
{
    protected array $data = [
        'users' => __DIR__.'/data/users/users.csv',
        'role_user' => __DIR__.'/data/users/role_user.csv',
    ];

    /**
     * @return void
     *
     * @throws \League\Csv\Exception
     */
    public function run(): void
    {
        foreach ($this->data as $table => $path) {
            $data = $this->csvToCollection($path);

            $data->each(function ($record) use ($table) {
                // exorcise the empty value into null
                foreach ($record as $key => $value) {
                    if (empty($value) && ! is_numeric($value)) {
                        unset($record[$key]);
                    }
                }

                DB::table($table)->insert($record);
            });
        }
    }

    /**
     * @throws \League\Csv\Exception
     */
    private function csvToCollection(string $path): Collection
    {
        $csv = Reader::createFromPath($path, 'r');

        $csv->setHeaderOffset(0);

        return new Collection($csv->getRecords());
    }
}
