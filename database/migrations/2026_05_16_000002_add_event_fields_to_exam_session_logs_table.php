<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_session_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_session_logs', 'event_type')) {
                $table->string('event_type', 100)->nullable()->after('tab_count');
            }

            if (!Schema::hasColumn('exam_session_logs', 'tab_id')) {
                $table->string('tab_id', 255)->nullable()->after('event_type');
            }

            if (!Schema::hasColumn('exam_session_logs', 'client_timestamp')) {
                $table->timestamp('client_timestamp')->nullable()->after('tab_id');
            }

            if (!Schema::hasColumn('exam_session_logs', 'server_timestamp')) {
                $table->timestamp('server_timestamp')->nullable()->after('client_timestamp');
            }
        });

        if (Schema::hasColumn('exam_session_logs', 'server_timestamp')) {
            DB::table('exam_session_logs')
                ->whereNull('server_timestamp')
                ->update(['server_timestamp' => DB::raw('created_at')]);
        }

        DB::statement('CREATE INDEX IF NOT EXISTS exam_session_logs_event_type_created_at_index ON exam_session_logs (event_type, created_at)');
        DB::statement('CREATE INDEX IF NOT EXISTS exam_session_logs_tab_id_created_at_index ON exam_session_logs (tab_id, created_at)');
        DB::statement('CREATE INDEX IF NOT EXISTS exam_session_logs_server_timestamp_index ON exam_session_logs (server_timestamp)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS exam_session_logs_event_type_created_at_index');
        DB::statement('DROP INDEX IF EXISTS exam_session_logs_tab_id_created_at_index');
        DB::statement('DROP INDEX IF EXISTS exam_session_logs_server_timestamp_index');

        Schema::table('exam_session_logs', function (Blueprint $table) {
            if (Schema::hasColumn('exam_session_logs', 'server_timestamp')) {
                $table->dropColumn('server_timestamp');
            }

            if (Schema::hasColumn('exam_session_logs', 'client_timestamp')) {
                $table->dropColumn('client_timestamp');
            }

            if (Schema::hasColumn('exam_session_logs', 'tab_id')) {
                $table->dropColumn('tab_id');
            }

            if (Schema::hasColumn('exam_session_logs', 'event_type')) {
                $table->dropColumn('event_type');
            }
        });
    }
};
