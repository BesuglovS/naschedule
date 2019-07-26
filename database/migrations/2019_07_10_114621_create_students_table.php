<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('f');
            $table->string('i');
            $table->string('o');
            $table->string('zach_number');
            $table->date('birth_date');
            $table->string('address');
            $table->string('phone');
            $table->string('orders');
            $table->string('starosta');
            $table->string('n_factor');
            $table->string('paid_edu');
            $table->string('expelled');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('students');
    }
}
