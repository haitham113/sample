<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('The ID of the client but from the users table');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('activity_type')->comment('Call or Meeting');
            $table->string('activity_status')->comment('Ongoing, Won, Lost or No Answer');
            $table->double('activity_value')->comment('The points gained from this activity');
            $table->text('feedback');
            $table->date('activity_date');
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
        Schema::dropIfExists('activities');
    }
}
