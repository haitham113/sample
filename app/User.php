<?php

namespace App;
use App\Activity;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'company_name'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime:Y-m-d',
    ];


    public function permissions(){
		return $this->belongsToMany('App\Permission', 'user_permission', 'user_id', 'permission_id');
    }
    
    public function getPermissions(){
        $sql = "SELECT name FROM user_permission LEFT JOIN permissions ON permissions.id = user_permission.permission_id WHERE user_id = $this->id";
        return \DB::select($sql);
	}
	
	public function hasAnyPermission($permissions){
		if(is_array($permissions)){
			foreach($permissions as $permission){
				if($this->hasPermission($permission)){
					return true;
				}
			}	
		}else{
			if($this->hasPermission($permission)){
					return true;
				}
		}
		return false;

    }
    
	public function hasPermission($permission){
		if($this->permissions()->where('name', $permission)->first()){
			return true;
		}
		return false;
	}
	
	public function isTherePermission(){
		if($this->permissions()->where('user_id', $this->id)->first()){
			return true;
		}
		return false;
    }
    
    //one to one with user model [the rest of the employee data stored in user table]
    public function employee(){

        return $this->hasOne('App\Employee');
    }

    //one to one with user model [the rest of the client data stored in user table]
    public function client(){

        return $this->hasOne('App\Client');
    }


    //calculate the Activities of a user of type employee only
    public function calcActivities(){
       
        $meetings = Activity::where('activity_type', 'meeting')
                                ->where('user_id', $this->id)
                                ->count();

        $calls = Activity::where('activity_type', 'call')
                                ->where('user_id', $this->id)
                                ->count();

        $showings = Activity::where('activity_type', 'showing')
                                ->where('user_id', $this->id)
                                ->count();

        $wons = Activity::where('activity_status', 'won')
                                ->where('user_id', $this->id)
                                ->count();
        
        $numOfActivites = [
            'meetings' => $meetings,
            'calls' => $calls,
            'showings' => $showings,
            'wons' => $wons,
        ];

        return $numOfActivites;

    }
}
