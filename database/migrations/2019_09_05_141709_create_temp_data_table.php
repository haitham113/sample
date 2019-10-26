<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTempDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('data');
            $table->string('flag');
            $table->integer('operationCode');
            /**
             * operationCode values and descriptions:
             * a- Employee sold a unit to outside broker [value will be: 1]
             * b- Primary [value will be: 2]
             * c- Externally Resale [value will be: 3]
             * d- Inernally Resale [value will be: 4]
             */
            $table->string('operationDesc');
            $table->unsignedBigInteger('created_by')->comment('The ID of the employee but from the users table');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('temp_data');
    }
}
