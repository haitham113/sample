<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $table = 'units';

    //The permitted fields to filled in the mass assignment
    protected $fillable = ['unit_code', 'broker_type', 'compound_id', 'unit_type', 'unit_num', 'land_area', 'building_area', 'garden_area', 'offering_type', 'owner_name', 'owner_phone', 'owner_email', 'owner_notes', 'bedrooms', 'bathrooms', 'floor_num', 'unit_view', 'unit_desc', 'original_price', 'market_price', 'owner_price', 'over_price', 'commission_percentage', 'commission_value', 'final_price', 'original_downpayment', 'final_downpayment', 'created_by'];

    //Many to one with user model [The user who created this model]
    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    //Many to one with Compound model
    public function compound()
    {
        return $this->belongsTo('App\Compound', 'compound_id');
    }
}
