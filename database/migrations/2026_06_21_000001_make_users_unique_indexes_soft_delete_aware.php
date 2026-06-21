<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Make the (username, client_id) and (email, client_id) uniqueness on the
     * users table soft-delete aware. Previously these were plain composite
     * unique constraints that still counted soft-deleted rows, so a username
     * or email belonging to a deleted user could never be reused — creating a
     * new user with that value failed.
     *
     * On PostgreSQL we replace the constraints with partial unique indexes
     * scoped to `deleted_at IS NULL`. Application-level validation
     * (Rule::unique()->whereNull('deleted_at')) enforces the same rule for
     * any driver that does not support partial indexes (e.g. MySQL).
     *
     * @return void
     */
    public function up()
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_username_client_unique');
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_email_client_unique');

            DB::statement('CREATE UNIQUE INDEX users_username_client_unique ON users (username, client_id) WHERE deleted_at IS NULL');
            DB::statement('CREATE UNIQUE INDEX users_email_client_unique ON users (email, client_id) WHERE deleted_at IS NULL');
        }

        // MySQL/MariaDB do not support partial indexes. The composite unique
        // constraints stay in place and soft-delete-aware uniqueness is
        // enforced at the application layer instead.
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS users_username_client_unique');
            DB::statement('DROP INDEX IF EXISTS users_email_client_unique');

            DB::statement('ALTER TABLE users ADD CONSTRAINT users_username_client_unique UNIQUE (username, client_id)');
            DB::statement('ALTER TABLE users ADD CONSTRAINT users_email_client_unique UNIQUE (email, client_id)');
        }
    }
};
