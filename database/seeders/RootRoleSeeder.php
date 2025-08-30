<?php

namespace Database\Seeders;

use App\Models\Accounts\Role;
use App\Models\Accounts\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RootRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create system roles
        $rootRole = Role::firstOrCreate(
            ['slug' => Role::ROOT],
            [
                'name' => 'Root Administrator',
                'description' => 'Super admin with full system access'
            ]
        );

        $clientAdminRole = Role::firstOrCreate(
            ['slug' => Role::CLIENT_ADMIN],
            [
                'name' => 'Administrator',
                'description' => 'Administrator for a specific client'
            ]
        );

        $scorerRole = Role::firstOrCreate(
            ['slug' => 'scorer'],
            [
                'name' => 'Scorer / Committee',
                'description' => 'Test scorer and committee member'
            ]
        );

        // Create a default root user if it doesn't exist
        $rootUser = User::withoutGlobalScopes()->firstOrCreate(
            ['email' => 'root@admin.com'],
            [
                'name' => 'Root Administrator',
                'username' => 'root',
                'password' => Hash::make('RootAdmin@123')
            ]
        );

        // Attach root role to the root user
        if (!$rootUser->roles()->where('roles.id', $rootRole->id)->exists()) {
            $rootUser->roles()->attach($rootRole);
        }

        $this->command->info('Root role and user created successfully!');
        $this->command->info('Root user credentials:');
        $this->command->info('Email: root@admin.com');
        $this->command->info('Password: RootAdmin@123');
        $this->command->warn('Please change the root password after first login!');
    }
}
