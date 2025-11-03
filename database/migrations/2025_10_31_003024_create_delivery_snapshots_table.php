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
        Schema::create('delivery_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delivery_id')->unique();
            $table->unsignedBigInteger('exam_id');
            $table->jsonb('exam_structure'); // Stores complete exam structure: items, questions, answers
            $table->integer('total_questions')->default(0); // For quick access in progress calculation
            $table->integer('total_items')->default(0); // For reference
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('delivery_id')->references('id')->on('deliveries')->onDelete('cascade');
            $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');

            // Index for faster lookups
            $table->index('delivery_id');
            $table->index('exam_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery_snapshots');
    }
};
