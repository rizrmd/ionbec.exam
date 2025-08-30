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
        Schema::table('takers', function (Blueprint $table) {
            // Remove the reg + client_id unique constraint since reg numbers can be duplicate in legacy data
            $table->dropUnique('takers_reg_client_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('takers', function (Blueprint $table) {
            // Restore the unique constraint (but this might fail if duplicates exist)
            $table->unique(['reg', 'client_id'], 'takers_reg_client_unique');
        });
    }
};
