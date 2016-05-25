<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSamplesData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
         Schema::create('samples_data', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumInteger('year_month')->unsigned()->default(0);
            $table->tinyInteger('age_group_id')->unsigned()->default(0);
            $table->integer('facility_id')->unsigned()->default(0);
            $table->enum('gender', ['m','f','x'])->default('x');            
            $table->smallInteger('regimen_group_id')->unsigned()->default(0);
            $table->tinyInteger('regimen_line')->unsigned()->default(0);
            $table->smallInteger('regimen_time_id')->unsigned()->default(0);
            $table->tinyInteger('treatment_indication_id')->unsigned()->default(0);      
            $table->integer('samples_received')->unsigned()->default(0);
            $table->integer('dbs_samples')->unsigned()->default(0);            
            $table->integer('rejected_samples')->unsigned()->default(0);
            $table->integer('sample_quality_rejections')->unsigned()->default(0);
            $table->integer('eligibility_rejections')->unsigned()->default(0);
            $table->integer('incomplete_form_rejections')->unsigned()->default(0);
            $table->integer('total_results')->unsigned()->default(0);
            $table->integer('valid_results')->unsigned()->default(0);
            $table->integer('suppressed')->unsigned()->default(0);
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
         Schema::drop('samples_data');
    }
}
