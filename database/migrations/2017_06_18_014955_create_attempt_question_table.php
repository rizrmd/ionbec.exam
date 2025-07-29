<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttemptQuestionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attempt_question', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('attempt_id');
            $table->unsignedInteger('question_id');
            $table->unsignedInteger('answer_id')->nullable();
            $table->string('answer_hash')->nullable();
            $table->longText('answer')->nullable();
            $table->boolean('is_correct')->default(0);
            $table->integer('score')->default(0);
            $table->timestamps();

            $table->foreign('attempt_id')
                ->references('id')
                ->on('attempts')
                ->onDelete('cascade');

            $table->foreign('question_id')
                ->references('id')
                ->on('questions')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attempt_question');
    }
}
