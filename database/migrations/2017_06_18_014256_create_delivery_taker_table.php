<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeliveryTakerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_taker', function (Blueprint $table) {
            $table->unsignedInteger('delivery_id');
            $table->unsignedInteger('taker_id');
            $table->string('token')->nullable();

            $table->foreign('delivery_id')
                ->references('id')
                ->on('deliveries')
                ->onDelete('cascade');

            $table->foreign('taker_id')
                ->references('id')
                ->on('takers')
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
        Schema::dropIfExists('delivery_taker');
    }
}
