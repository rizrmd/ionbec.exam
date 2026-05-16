<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('exam_session_logs', 'client_id')) {
            Schema::table('exam_session_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('client_id')->nullable()->after('id');
                $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
                $table->index(['client_id', 'created_at']);
            });
        }

        DB::statement('
            UPDATE exam_session_logs
            SET client_id = attempts.client_id
            FROM attempts
            WHERE exam_session_logs.attempt_id = attempts.id
              AND exam_session_logs.client_id IS NULL
        ');
    }

    public function down(): void
    {
        if (Schema::hasColumn('exam_session_logs', 'client_id')) {
            Schema::table('exam_session_logs', function (Blueprint $table) {
                $table->dropForeign(['client_id']);
                $table->dropIndex(['client_id', 'created_at']);
                $table->dropColumn('client_id');
            });
        }
    }
};
