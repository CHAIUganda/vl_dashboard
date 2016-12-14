<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyUsers extends Migration
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
            $table->string('username', 64)->unique()->change();
            $table->string('telephone',64)->nullable()->change();
            $table->integer('facility_id')->nullable()->change();
            $table->string('facility_name')->nullable()->change();
            $table->integer('hub_id')->nullable()->change();
            $table->string('hub_name')->nullable()->change();
            $table->boolean('deactivated')->default(0)->change();

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
