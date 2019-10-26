<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name');
            $table->string('second_name');
            $table->string('project_name');
            $table->string('country_code');
            $table->string('lead_phone');
            $table->string('lead_status')->comment('New or Moved');
            $table->string('lead_type')->comment('lead or data'); //the lead type is the one added by team leaders and the data type is the one added by anyone else
            $table->unsignedBigInteger('assigned_to');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('reassigned_to');
            $table->foreign('reassigned_to')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('reassigned_by');
            $table->foreign('reassigned_by')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('created_by');
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
        Schema::dropIfExists('leads');
    }
}
