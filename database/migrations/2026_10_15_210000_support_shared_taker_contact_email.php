<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            throw new RuntimeException('Shared participant identity migration requires PostgreSQL.');
        }
        DB::statement('ALTER TABLE takers ADD COLUMN login_id uuid NOT NULL DEFAULT gen_random_uuid()');
        DB::statement('ALTER TABLE takers ADD CONSTRAINT takers_login_id_unique UNIQUE (login_id)');
        DB::statement('CREATE UNIQUE INDEX delivery_taker_token_identity_unique ON delivery_taker (upper(token)) WHERE token IS NOT NULL');
        DB::statement('ALTER TABLE takers DROP CONSTRAINT takers_email_client_unique');
    }

    public function down(): void
    {
        // This must fail atomically while shared emails exist: never delete or
        // merge identities merely to make a rollback succeed.
        DB::statement('ALTER TABLE takers ADD CONSTRAINT takers_email_client_unique UNIQUE (email, client_id)');
        DB::statement('DROP INDEX delivery_taker_token_identity_unique');
        DB::statement('ALTER TABLE takers DROP COLUMN login_id');
    }
};
