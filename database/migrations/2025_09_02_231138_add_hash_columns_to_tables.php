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
        // Add hash column to deliveries table
        Schema::table('deliveries', function (Blueprint $table) {
            if (!Schema::hasColumn('deliveries', 'hash')) {
                $table->string('hash', 255)->nullable()->after('id');
                $table->index('hash');
            }
        });

        // Add hash column to exams table
        Schema::table('exams', function (Blueprint $table) {
            if (!Schema::hasColumn('exams', 'hash')) {
                $table->string('hash', 255)->nullable()->after('id');
                $table->index('hash');
            }
        });

        // Add hash column to groups table
        Schema::table('groups', function (Blueprint $table) {
            if (!Schema::hasColumn('groups', 'hash')) {
                $table->string('hash', 255)->nullable()->after('id');
                $table->index('hash');
            }
        });

        // Add hash column to attempts table
        Schema::table('attempts', function (Blueprint $table) {
            if (!Schema::hasColumn('attempts', 'hash')) {
                $table->string('hash', 255)->nullable()->after('id');
                $table->index('hash');
            }
        });

        // Generate hash values for existing records
        $this->generateHashValues();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropIndex(['hash']);
            $table->dropColumn('hash');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->dropIndex(['hash']);
            $table->dropColumn('hash');
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->dropIndex(['hash']);
            $table->dropColumn('hash');
        });

        Schema::table('attempts', function (Blueprint $table) {
            $table->dropIndex(['hash']);
            $table->dropColumn('hash');
        });
    }

    /**
     * Generate hash values for existing records
     */
    private function generateHashValues()
    {
        // Generate hashes for deliveries
        $deliveries = \App\Models\Deliveries\Delivery::whereNull('hash')->get();
        foreach ($deliveries as $delivery) {
            $delivery->hash = $delivery->hash; // This will trigger the HashableId trait
            $delivery->save();
        }

        // Generate hashes for exams
        $exams = \App\Models\Exams\Exam::whereNull('hash')->get();
        foreach ($exams as $exam) {
            $exam->hash = $exam->hash; // This will trigger the HashableId trait
            $exam->save();
        }

        // Generate hashes for groups
        $groups = \App\Models\Takers\Group::whereNull('hash')->get();
        foreach ($groups as $group) {
            $group->hash = $group->hash; // This will trigger the HashableId trait
            $group->save();
        }

        // Generate hashes for attempts
        $attempts = \App\Models\Attempts\Attempt::whereNull('hash')->get();
        foreach ($attempts as $attempt) {
            $attempt->hash = $attempt->hash; // This will trigger the HashableId trait
            $attempt->save();
        }
    }
};