<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('units', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('unit_code')->nullable();
            $table->string('unit_status')->nullable(); 
            /**
             * unit_status values and descriptions:
             * a- For sale [value will be: 1]
             * b- Sold unknown [value will be: 2]
             * c- Not for sale sale now [value will be: 3]
             * d- Sold with outside broker [value will be: 4]
             * e- Sold internally [value will be: 5]
             */
            $table->string('type_of_sale')->nullable();
            $table->unsignedBigInteger('sold_to')->nullable();
            $table->foreign('sold_to')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('sold_by')->nullable();
            $table->foreign('sold_by')->references('id')->on('users')->onDelete('cascade');
            $table->string('sold_at')->nullable();
            $table->unsignedBigInteger('compound_id');
            $table->foreign('compound_id')->references('id')->on('compounds')->onDelete('cascade');
            $table->string('unit_type')->nullable(); //apartment, stand alone, townhouse, twin villa, other
            $table->string('broker_type')->nullable(); //external or internal broker
            $table->string('unit_num')->nullable();
            $table->string('land_area')->nullable();
            $table->string('building_area')->nullable();
            $table->string('garden_area')->nullable();
            $table->string('offering_type')->nullable();//for sale, for rent
            $table->string('owner_name')->nullable();
            $table->string('owner_phone')->nullable();
            $table->string('owner_email')->nullable();
            $table->text('owner_notes')->nullable();
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->integer('floor_num')->nullable();
            $table->string('unit_view')->nullable();
            $table->text('unit_desc')->nullable();
            $table->double('original_price')->nullable();
            $table->string('market_price')->nullable();
            $table->double('owner_price')->nullable();
            $table->double('over_price')->nullable();
            $table->string('commission_percentage')->nullable();
            $table->string('commission_value')->nullable();
            $table->double('final_price')->nullable();
            $table->double('original_downpayment')->nullable();
            $table->double('final_downpayment')->nullable();
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
        Schema::dropIfExists('units');
    }
}
