<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLessonLogEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lesson_log_events', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('old_lesson_id');
            $table->unsignedBigInteger('new_lesson_id');

            $table->dateTime('date_time');

            $table->string('public_comment');
            $table->string('hidden_comment');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lesson_log_events');
    }
}
