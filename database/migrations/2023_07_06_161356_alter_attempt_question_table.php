<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attempt_question', function (Blueprint $table) {
            $table->decimal('score')->default(0)->change();
        });

        Schema::table('attempts', function (Blueprint $table) {
            $table->decimal('score')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attempt_question', function (Blueprint $table) {
            $table->integer('score')->default(0)->change();
        });

        Schema::table('attempts', function (Blueprint $table) {
            $table->integer('score')->default(0)->change();
        });
    }
};
