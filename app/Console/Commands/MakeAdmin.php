<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Accounts\User;

class MakeAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:admin {email} {role=super_admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make an existing user an admin';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $role = $this->argument('role');

        if (!in_array($role, ['super_admin', 'manager', 'viewer'])) {
            $this->error('Role must be one of: super_admin, manager, viewer');
            return 1;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found");
            return 1;
        }

        $user->is_admin = true;
        $user->admin_role = $role;
        $user->save();

        $this->info("✅ User '{$user->name}' ({$user->email}) has been made an admin with role: {$role}");

        return 0;
    }
}