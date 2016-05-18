<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFacilities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('facilities', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumInteger('facility_id')->unsigned();
            $table->mediumInteger('hub_id')->unsigned();
            $table->mediumInteger('district_id')->unsigned();
            $table->mediumInteger('ip_id')->unsigned();
            $table->string('name');
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
        Schema::drop('facilities');
    }
}
