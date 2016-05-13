<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegimenData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
         Schema::create('regimen_data', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumInteger('year_month')->unsigned();
            $table->tinyInteger('age_group_id')->unsigned();
            $table->integer('facility_id')->unsigned();
            $table->enum('gender', ['m','f','x'])->default('x');
            $table->enum('treatment_indication', ['b_plus','tb']);
            $table->smallInteger('regimen_group_id')->unsigned();
            $table->tinyInteger('regimen_line')->unsigned(); 
            $table->tinyInteger('regimen_duration_id')->unsigned();
            $table->integer('count')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::drop('regimen_data');
    }
}
