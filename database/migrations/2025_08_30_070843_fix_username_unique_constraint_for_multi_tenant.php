<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Fix users table constraints for multi-tenancy
        Schema::table('users', function (Blueprint $table) {
            // Drop the existing global unique constraints
            $table->dropUnique(['username']);
            $table->dropUnique(['email']);
            
            // Add composite unique constraints for multi-tenancy
            $table->unique(['username', 'client_id'], 'users_username_client_unique');
            $table->unique(['email', 'client_id'], 'users_email_client_unique');
        });

        // Fix groups table constraints for multi-tenancy
        Schema::table('groups', function (Blueprint $table) {
            // Drop the existing global unique constraint on code
            $table->dropUnique(['code']);
            
            // Add composite unique constraint for code + client_id
            $table->unique(['code', 'client_id'], 'groups_code_client_unique');
        });

        // Add unique constraints to takers table for multi-tenancy
        Schema::table('takers', function (Blueprint $table) {
            // Add composite unique constraints where email/reg should be unique per client
            $table->unique(['email', 'client_id'], 'takers_email_client_unique');
            $table->unique(['reg', 'client_id'], 'takers_reg_client_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reverse users table changes
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_username_client_unique');
            $table->dropUnique('users_email_client_unique');
            $table->unique('username');
            $table->unique('email');
        });

        // Reverse groups table changes
        Schema::table('groups', function (Blueprint $table) {
            $table->dropUnique('groups_code_client_unique');
            $table->unique('code');
        });

        // Reverse takers table changes
        Schema::table('takers', function (Blueprint $table) {
            $table->dropUnique('takers_email_client_unique');
            $table->dropUnique('takers_reg_client_unique');
        });
    }
};
