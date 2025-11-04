<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Accounts\User;

return new class extends Migration
{
    public function up()
    {
        // Update admin user to have proper admin privileges
        User::where('email', 'admin@localhost.com')->update([
            'is_admin' => true,
            'admin_role' => 'super_admin'
        ]);
    }

    public function down()
    {
        // Revert admin user to non-admin
        User::where('email', 'admin@localhost.com')->update([
            'is_admin' => false,
            'admin_role' => null
        ]);
    }
};