<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Compound extends Model
{
    protected $table = 'compounds';

    //The permitted fields to filled in the mass assignment
    protected $fillable = ['name', 'address', 'area_id', 'created_by'];

    //Many to one with user model [The user who created this model]
    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    //Many to one with Area model
    public function area()
    {
        return $this->belongsTo('App\Area', 'area_id');
    }
}
