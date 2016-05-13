<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTreatmentIndication extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        //
        Schema::create('treatment_indication', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumInteger('year_month')->unsigned();
            $table->tinyInteger('age_group_id')->unsigned();
            $table->integer('facility_id')->unsigned();

            $table->integer('cd4_less_than_500')->unsigned();
            $table->integer('pmtct_option_b_plus')->unsigned();
            $table->integer('children_under_15')->unsigned();
            $table->integer('other_treatment')->unsigned();
            $table->integer('treatment_blank_on_form')->unsigned();
            $table->integer('tb_infection')->unsigned();
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
         Schema::drop('treatment_indication');
    }
}
