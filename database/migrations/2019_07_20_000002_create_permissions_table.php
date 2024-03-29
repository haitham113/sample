<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
		Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('system_module_id');
            $table->foreign('system_module_id')->references('id')->on('system_modules')->onDelete('cascade');
            $table->string('label');
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
		Schema::drop('permissions');
    }
}
