<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call(RoleTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(CategoriesTableSeeder::class);
        $this->call(ItemsTableSeeder::class);
        $this->call(TakersTableSeeder::class);
        $this->call(GroupsTableSeeder::class);
        $this->call(ExamsTableSeeder::class);
        $this->call(DeliveriesTableSeeder::class);
        $this->call(SecondTrialDataSeeder::class);
        $this->call(WednesdayTrialDataSeeder::class);
        $this->call(InstituteTestSeeder::class);

        DB::statement('UPDATE deliveries SET display_name = name');
        $this->call(GenerateTakerCodeSeeder::class);
    }
}
