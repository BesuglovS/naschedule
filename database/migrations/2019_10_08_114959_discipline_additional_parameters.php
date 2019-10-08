<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DisciplineAdditionalParameters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('disciplines', function (Blueprint $table) {
            $table->boolean('active');
            $table->integer('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('disciplines', function (Blueprint $table) {
            $table->dropColumn(['active', 'type']);
        });
    }
}
