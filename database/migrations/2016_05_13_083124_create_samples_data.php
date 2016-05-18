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
            $table->mediumInteger('year_month')->unsigned();
            $table->tinyInteger('age_group_id')->unsigned();
            $table->integer('facility_id')->unsigned();
            $table->enum('gender', ['m','f','x'])->default('x');
            $table->enum('treatment_indication', ['b_plus','tb','x'])->default('x');
            $table->smallInteger('regimen_group_id')->unsigned();            

            $table->integer('samples_received')->unsigned();
            $table->integer('dbs_samples')->unsigned();            
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
        //
         Schema::drop('samples_data');
    }
}
