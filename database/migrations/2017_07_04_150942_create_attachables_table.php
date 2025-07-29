<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttachablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attachables', function (Blueprint $table) {
            $table->uuid('attachment_id');
            $table->unsignedInteger('attachable_id');
            $table->string('attachable_type');

            $table->foreign('attachment_id')
                ->references('id')
                ->on('attachments')
                ->onDelete('cascade');

            $table->index(['attachment_id', 'attachable_id', 'attachable_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attachables');
    }
}
