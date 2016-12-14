<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('users', function ($table) {
            $table->string('username');
            $table->string('telephone');
            $table->integer('facility_id');
            $table->string('facility_name');
            $table->integer('hub_id');
            $table->string('hub_name');
            $table->boolean('deactivated');

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
    }
}
