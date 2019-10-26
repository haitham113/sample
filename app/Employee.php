<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    //The permitted fields to filled in the mass assignment
    protected $fillable = ['user_id', 'team_leader_id', 'position_id', 'job_title', 'level_id', 'points', 'national_id', 'joining_date', 'profile_picture', 'created_by', 'created_at', 'updated_at'];

    //one to one with Level model
    public function level(){

        return $this->belongsTo('App\Level');
    }

    //one to one with user model [the rest of the employee data stored in user table]
    public function user(){

        return $this->belongsTo('App\User');
    }

    //Many to one with user model [The user who created this model]
    public function createdBy(){

        return $this->belongsTo('App\User', 'created_by');
    }

    //
    public function teamLeader(){

        return $this->belongsTo('App\User', 'team_leader_id');
    }

    //
    public function position(){

        return $this->belongsTo('App\Position');
    }
}
