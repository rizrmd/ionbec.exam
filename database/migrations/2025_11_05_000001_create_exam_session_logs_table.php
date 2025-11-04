<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exam_session_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->nullable()->constrained('attempts')->onDelete('cascade');
            $table->string('session_key', 255);
            $table->integer('tab_count')->default(1);
            $table->string('ip_address', 45)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('isp', 255)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['attempt_id', 'created_at']);
            $table->index(['ip_address', 'created_at']);
            $table->index(['session_key', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_session_logs');
    }
};