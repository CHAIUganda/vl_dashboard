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

            $table->string('username', 64)->unique();
            $table->string('telephone',64)->nullable();
            $table->integer('facility_id')->nullable();
            $table->string('facility_name')->nullable();
            $table->integer('hub_id')->nullable();
            $table->string('hub_name')->nullable();
            $table->boolean('deactivated')->default(0);

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
