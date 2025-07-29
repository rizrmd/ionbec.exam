<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttemptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attempts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('attempted_by');
            $table->unsignedInteger('exam_id');
            $table->unsignedInteger('delivery_id');
            $table->string('ip_address');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('extra_minute')->default(0);
            $table->integer('score')->default(0);
            $table->integer('progress')->default(0);
            $table->integer('penalty')->default(0);
            $table->timestamps();

            $table->foreign('attempted_by')
                ->references('id')
                ->on('takers')
                ->onDelete('cascade');

            $table->foreign('exam_id')
                ->references('id')
                ->on('exams')
                ->onDelete('cascade');

            $table->foreign('delivery_id')
                ->references('id')
                ->on('deliveries')
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
        Schema::dropIfExists('attempts');
    }
}
