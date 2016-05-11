<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDashboard extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('dashboard', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumInteger('year_month')->unsigned();
            $table->tinyInteger('age_group_id')->unsigned();
            $table->integer('facility_id')->unsigned();
            $table->integer('samples_received')->unsigned();
            $table->integer('dbs_samples')->unsigned();
            $table->integer('cd4_less_than_500')->unsigned();
            $table->integer('pmtct_option_b_plus')->unsigned();
            $table->integer('children_under_15')->unsigned();
            $table->integer('other_treatment')->unsigned();
            $table->integer('treatment_blank_on_form')->unsigned();
            $table->integer('tb_infection')->unsigned();
            $table->integer('rejected_samples')->unsigned();
            $table->integer('sample_quality_rejections')->unsigned();
            $table->integer('eligibility_rejections')->unsigned();
            $table->integer('incomplete_form_rejections')->unsigned();
            $table->integer('total_results')->unsigned();
            $table->integer('valid_results')->unsigned();
            $table->integer('suppressed')->unsigned();
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
        //drop the database
        Schema::drop('dashboard');
    }
}
