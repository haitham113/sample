<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{

    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'rem_date' => 'datetime:Y-m-d',
    ];
    
    //The permitted fields to filled in the mass assignment
    protected $fillable = ['rem_date', 'rem_desc', 'created_by'];


    //Many to one with user model [The user who created this model]
    public function createdBy(){

        return $this->belongsTo('App\User', 'created_by');
    }

}
