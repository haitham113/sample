<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
     //The permitted fields to filled in the mass assignment
     protected $fillable = ['first_name', 'second_name', 'project_name', 'country_code', 'lead_phone', 'lead_status', 'assigned_to', 'reassigned_to', 'reassigned_by', 'created_by'];

    //Many to one with user model [The user who created this model]
    public function createdBy(){

        return $this->belongsTo('App\User', 'created_by');
    }
     
    //Many to one with user model
    public function assignedTo(){

        return $this->belongsTo('App\User', 'assigned_to');
    }

    //Many to one with user model 
    public function reassignedTo(){

        return $this->belongsTo('App\User', 'reassigned_to');
    }

    //Many to one with user model
    public function reassignedBy(){

        return $this->belongsTo('App\User', 'reassigned_by');
    }
}
