<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Accounts\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin@exam.com'],
            [
                'name' => 'Exam Administrator',
                'username' => 'admin',
                'email' => 'admin@exam.com',
                'password' => Hash::make('admin123'),
                'is_admin' => true,
                'admin_role' => 'super_admin',
                'email_verified_at' => now(),
            ]
        );

        // Create viewer admin user
        $viewer = User::updateOrCreate(
            ['email' => 'viewer@exam.com'],
            [
                'name' => 'Exam Viewer',
                'username' => 'viewer',
                'email' => 'viewer@exam.com',
                'password' => Hash::make('viewer123'),
                'is_admin' => true,
                'admin_role' => 'viewer',
                'email_verified_at' => now(),
            ]
        );

        // Create manager admin user
        $manager = User::updateOrCreate(
            ['email' => 'manager@exam.com'],
            [
                'name' => 'Exam Manager',
                'username' => 'manager',
                'email' => 'manager@exam.com',
                'password' => Hash::make('manager123'),
                'is_admin' => true,
                'admin_role' => 'manager',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin users created:');
        $this->command->info('- Super Admin: admin@exam.com / admin123');
        $this->command->info('- Manager: manager@exam.com / manager123');
        $this->command->info('- Viewer: viewer@exam.com / viewer123');
        $this->command->info('');
        $this->command->info('Access the admin dashboard at: /admin/exam-logs');
        $this->command->info('');
        $this->command->info('Admin Role Permissions:');
        $this->command->info('- super_admin: Full access including delete');
        $this->command->info('- manager: View and export, no delete');
        $this->command->info('- viewer: View only, no export or delete');
    }
}