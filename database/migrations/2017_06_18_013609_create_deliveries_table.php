
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('exam_id');
            $table->unsignedInteger('group_id');
            $table->string('name')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->integer('duration')->default(60);
            $table->timestamp('ended_at')->nullable();
            $table->boolean('is_anytime')->default(0);
            $table->boolean('automatic_start')->default(1);
            $table->timestamp('is_finished')->nullable();
            $table->string('last_status')->nullable();
            $table->string('display_name')->nullable();
            $table->timestamps();

            $table->foreign('exam_id')
                ->references('id')
                ->on('exams')
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
        Schema::dropIfExists('deliveries');
    }
}
