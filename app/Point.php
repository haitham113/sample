<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    //Many to one with user model [The user who created this model]
    public function createdBy(){

        return $this->belongsTo('App\User', 'created_by');
    }

}
