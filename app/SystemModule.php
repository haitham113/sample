<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SystemModule extends Model
{
    protected $table = 'system_modules';

    public function permissions(){
        
        return $this->hasMany('App\Permission', 'system_module_id');
    }
}
