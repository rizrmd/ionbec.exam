<?php

namespace App\Console\Commands;

use App\Models\Accounts\User;
use Illuminate\Console\Command;

class UpdateAdminUser extends Command
{
    protected $signature = 'admin:update {email} {is_admin=1}';
    protected $description = 'Update admin status for a user';

    public function handle()
    {
        $email = $this->argument('email');
        $isAdmin = $this->argument('is_admin');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found.");
            return 1;
        }

        $user->is_admin = (bool) $isAdmin;
        $user->save();

        $this->info("User {$email} admin status updated to: " . ($isAdmin ? 'Admin' : 'Not Admin'));
        return 0;
    }
}