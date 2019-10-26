<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    //The permitted fields to filled in the mass assignment
    protected $fillable = ['user_id', 'activity_type', 'activity_status', 'activity_value', 'feedback', 'activity_date', 'created_by', 'created_at', 'updated_at'];

    //Many to one with user model [The user who created this model]
    public function createdBy(){

        return $this->belongsTo('App\User', 'created_by');
    }

    //one to one with user model [the client id]
    public function clientData(){

        return $this->belongsTo('App\User', 'user_id');
    }
}
