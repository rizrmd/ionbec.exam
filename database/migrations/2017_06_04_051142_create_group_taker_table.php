<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupTakerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_taker', function (Blueprint $table) {
            $table->unsignedInteger('taker_id');
            $table->unsignedInteger('group_id');
            $table->string('taker_code')->nullable();

            $table->foreign('taker_id')
                ->references('id')
                ->on('takers')
                ->onDelete('cascade');

            $table->foreign('group_id')
                ->references('id')
                ->on('groups')
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
        Schema::dropIfExists('group_taker');
    }
}
