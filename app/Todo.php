<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    protected $table = 'todos';

    //The permitted fields to filled in the mass assignment
    protected $fillable = ['assigned_to', 'todo_desc', 'todo_status', 'todo_date', 'start_date', 'end_date', 'created_by', 'created_at', 'updated_at'];

    //Many to one with user model [The user who created this model]
    public function createdBy(){

        return $this->belongsTo('App\User', 'created_by');
    }

    //Many to one with user model
    public function assignedTo(){

        return $this->belongsTo('App\User', 'assigned_to');
    }
}
