<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    //The permitted fields to filled in the mass assignment
    protected $fillable = ['name', 'address', 'created_by', 'created_at', 'updated_at'];

    //Many to one with user model [The user who created this model]
    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by');
    }
}
