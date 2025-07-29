<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('type')->default('attachment');
            $table->unsignedInteger('uploaded_by');
            $table->string('title')->nullable();
            $table->string('path')->nullable();
            $table->string('mime')->nullable();
            $table->text('description')->nullable();
            $table->text('options')->nullable();
            $table->timestamps();

            $table->primary('id');

            $table->foreign('uploaded_by')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('attachments');
    }
}
