<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('discipline_id');
            $table->boolean('is_active');

            $table->dateTime('consultation_datetime');
            $table->unsignedBigInteger('consultation_auditorium_id');

            $table->dateTime('exam_datetime');
            $table->unsignedBigInteger('exam_auditorium_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exams');
    }
}
