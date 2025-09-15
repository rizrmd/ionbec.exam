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
        // Add hash column to categories table
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'hash')) {
                $table->string('hash', 255)->nullable()->after('id');
                $table->index('hash');
            }
        });

        // Add hash column to items table
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'hash')) {
                $table->string('hash', 255)->nullable()->after('id');
                $table->index('hash');
            }
        });

        // Add hash column to questions table
        Schema::table('questions', function (Blueprint $table) {
            if (!Schema::hasColumn('questions', 'hash')) {
                $table->string('hash', 255)->nullable()->after('id');
                $table->index('hash');
            }
        });

        // Add hash column to answers table
        Schema::table('answers', function (Blueprint $table) {
            if (!Schema::hasColumn('answers', 'hash')) {
                $table->string('hash', 255)->nullable()->after('id');
                $table->index('hash');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'hash')) {
                $table->dropIndex(['hash']);
                $table->dropColumn('hash');
            }
        });

        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'hash')) {
                $table->dropIndex(['hash']);
                $table->dropColumn('hash');
            }
        });

        Schema::table('questions', function (Blueprint $table) {
            if (Schema::hasColumn('questions', 'hash')) {
                $table->dropIndex(['hash']);
                $table->dropColumn('hash');
            }
        });

        Schema::table('answers', function (Blueprint $table) {
            if (Schema::hasColumn('answers', 'hash')) {
                $table->dropIndex(['hash']);
                $table->dropColumn('hash');
            }
        });
    }
};
