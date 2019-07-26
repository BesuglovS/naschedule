<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDisciplinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('disciplines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('attestation');
            $table->integer('auditorium_hours');
            $table->integer('auditorium_hours_per_week');
            $table->integer('lecture_hours');
            $table->integer('practical_hours');

            $table->unsignedBigInteger('student_group_id');
        });

        // attestation
        // 0: "нет",
        // 1: "зачёт",
        // 2: "экзамен",
        // 3: "зачёт и экзамен",
        // 4: "зачёт с оценкой"
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('disciplines');
    }
}
