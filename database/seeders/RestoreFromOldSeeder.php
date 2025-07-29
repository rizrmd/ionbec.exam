<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RestoreFromOldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call(ProductionSeeder::class);
        $this->call(RestoreAttachmentProductionSeeder::class);
    }
}
