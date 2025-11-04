<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class IonbecClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create ionbec.com client for main domain - only create if doesn't exist
        Client::firstOrCreate(
            ['slug' => 'ionbec'],
            [
                'name' => 'Ionbec Main Platform',
                'domains' => ['ionbec.com', 'www.ionbec.com'],
                'is_active' => true,
                'primary_contact_email' => 'admin@ionbec.com',
                'primary_contact_phone' => '+1234567890',
                'settings' => [
                    'theme' => 'default',
                    'max_attempts' => 3,
                    'time_zone' => 'Asia/Jakarta'
                ],
                'notes' => 'Main Ionbec platform client'
            ]
        );

        $this->command->info('Ionbec client created successfully!');
        $this->command->info('Domain: ionbec.com, www.ionbec.com');
    }
}