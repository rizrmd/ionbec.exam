<?php

namespace Database\Seeders;

use League\Csv\Reader;
use Illuminate\Database\Seeder;
use App\Models\Categories\Category;

class CategoriesFromRestoreTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $csv = Reader::createFromPath(database_path('seeders/data/restore/1/categories.csv'), 'r');
        $csv->setHeaderOffset(0); // set the CSV header offset

        foreach ($csv as $index => $data) {
            Category::query()->updateOrCreate([
                'type' => $data['type'],
                'name' => $data['name'],
            ]);
        }
    }
}
