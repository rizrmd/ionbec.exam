<?php

namespace Database\Seeders;

use App\Models\Accounts\Role;
use App\Models\Accounts\User;
use Illuminate\Database\Seeder;

class AssignAdminRoleSeeder extends Seeder
{
    public function run()
    {
        $user = User::where('email', 'admin@localhost.com')->first();
        $adminRole = Role::where('slug', 'administrator')->first();
        
        if ($user && $adminRole) {
            $user->roles()->syncWithoutDetaching([$adminRole->id]);
            echo "Administrator role assigned to admin user\n";
        } else {
            echo "User or role not found\n";
        }
    }
}