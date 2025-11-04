<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Accounts\Role;
use App\Models\Accounts\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create sample clients
        $client1 = Client::firstOrCreate(
            ['slug' => 'acme-corp'],
            [
                'name' => 'ACME Corporation',
                'domains' => ['acme.localhost', 'acme.example.com'],
                'is_active' => true,
                'primary_contact_email' => 'admin@acme.com',
                'primary_contact_phone' => '+1234567890',
                'settings' => [
                    'theme' => 'blue',
                    'max_attempts' => 3,
                    'time_zone' => 'America/New_York'
                ],
                'notes' => 'Premium client with enterprise features'
            ]
        );

        $client2 = Client::firstOrCreate(
            ['slug' => 'tech-edu'],
            [
                'name' => 'Tech Education Institute',
                'domains' => ['techedu.localhost', 'techedu.example.com'],
                'is_active' => true,
                'primary_contact_email' => 'admin@techedu.com',
                'primary_contact_phone' => '+0987654321',
                'settings' => [
                    'theme' => 'green',
                    'max_attempts' => 5,
                    'time_zone' => 'Europe/London'
                ],
                'notes' => 'Educational institution with special pricing'
            ]
        );

        // Create ionbec.com client for main domain
        $ionbecClient = Client::firstOrCreate(
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

        // Get roles
        $clientAdminRole = Role::where('slug', Role::CLIENT_ADMIN)->first();

        // Create admin users for each client
        $acmeAdmin = User::firstOrCreate(
            ['email' => 'admin@acme.com'],
            [
                'name' => 'ACME Admin',
                'username' => 'acme_admin',
                'password' => Hash::make('AcmeAdmin@123'),
                'client_id' => $client1->id
            ]
        );

        if ($clientAdminRole && !$acmeAdmin->roles()->where('roles.id', $clientAdminRole->id)->exists()) {
            $acmeAdmin->roles()->attach($clientAdminRole);
        }

        $techEduAdmin = User::firstOrCreate(
            ['email' => 'admin@techedu.com'],
            [
                'name' => 'Tech Edu Admin',
                'username' => 'techedu_admin',
                'password' => Hash::make('TechEdu@123'),
                'client_id' => $client2->id
            ]
        );

        if ($clientAdminRole && !$techEduAdmin->roles()->where('roles.id', $clientAdminRole->id)->exists()) {
            $techEduAdmin->roles()->attach($clientAdminRole);
        }

        // Note: DO NOT modify existing users - they should retain their original client_id
        // Users without client_id will be handled by the application logic for main domain access

        // Create admin users for each client
        $acmeAdmin = User::firstOrCreate(
            ['email' => 'admin@acme.com'],
            [
                'name' => 'ACME Admin',
                'username' => 'acme_admin',
                'password' => Hash::make('AcmeAdmin@123'),
                'client_id' => $client1->id
            ]
        );

        if ($clientAdminRole && !$acmeAdmin->roles()->where('roles.id', $clientAdminRole->id)->exists()) {
            $acmeAdmin->roles()->attach($clientAdminRole);
        }

        $techEduAdmin = User::firstOrCreate(
            ['email' => 'admin@techedu.com'],
            [
                'name' => 'Tech Edu Admin',
                'username' => 'techedu_admin',
                'password' => Hash::make('TechEdu@123'),
                'client_id' => $client2->id
            ]
        );

        if ($clientAdminRole && !$techEduAdmin->roles()->where('roles.id', $clientAdminRole->id)->exists()) {
            $techEduAdmin->roles()->attach($clientAdminRole);
        }

        // Create ionbec admin user
        $ionbecAdmin = User::firstOrCreate(
            ['email' => 'admin@ionbec.com'],
            [
                'name' => 'Ionbec Admin',
                'username' => 'ionbec_admin',
                'password' => Hash::make('IonbecAdmin@123'),
                'client_id' => $ionbecClient->id
            ]
        );

        if ($clientAdminRole && !$ionbecAdmin->roles()->where('roles.id', $clientAdminRole->id)->exists()) {
            $ionbecAdmin->roles()->attach($clientAdminRole);
        }

        $this->command->info('Sample clients created successfully!');
        $this->command->info('');
        $this->command->info('Client 1: ACME Corporation');
        $this->command->info('Domains: acme.localhost, acme.example.com');
        $this->command->info('Admin: admin@acme.com / AcmeAdmin@123');
        $this->command->info('');
        $this->command->info('Client 2: Tech Education Institute');
        $this->command->info('Domains: techedu.localhost, techedu.example.com');
        $this->command->info('Admin: admin@techedu.com / TechEdu@123');
        $this->command->info('');
        $this->command->info('Client 3: Ionbec Main Platform');
        $this->command->info('Domains: ionbec.com, www.ionbec.com');
        $this->command->info('Admin: admin@ionbec.com / IonbecAdmin@123');
        $this->command->info('Existing users have been assigned to Ionbec client');
    }
}
