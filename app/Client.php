<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    
    protected $table = 'clients';

     //The permitted fields to filled in the mass assignment
    protected $fillable = ['user_id', 'request_type', 'budget_from', 'budget_to', 'created_by','created_at', 'updated_at'];

    //Many to one with user model [The user who created this model]
    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    //one to one with user model [the rest of the client data stored in user table]
    public function user(){

        return $this->belongsTo('App\User');
    }
}
