<?php

namespace App\Http\Controllers;

use App\Permission;
use App\User;
use App\SystemModule;

use Illuminate\Http\Request;

//Additional added by Haitham
use Exception;

class PermissionController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() //Not used just for testing
    {
       
        try{
            //Get all System Modules with thier permissions
            $sysModules = SystemModule::with('permissions')->get();

            //build the response
            $code = 200;
            $status = 'success';
            $message = "All system permissions";
            $dataContent = ['sysModules'=> $sysModules->toArray()];
            
        }catch (Exception $e) {

            //build the response
            $code = 401;
            $status = 'Exception';
            $message = "Something went wrong";
            $dataContent = $e;
        }

        return $this->returnApiResult($code, $status, $message, $dataContent);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function edit($id){

        try{
            //Find the user using the id
            $user = User::find($id);

            if (!$user) { //If the user not found
                return $this->userNotFoundResponse();
                
            }

            //Get all permissions of the user
            $permissions = $user->permissions()->get();
            //Get all System Modules with thier permissions
            $sysModules = SystemModule::with('permissions')->get();

            //build the response
            $code = 200;
            $status = 'success';
            $message = "All permissions of the user";
            $dataContent = [
                'user_permissions' => $permissions->toArray(),
                'sysModulesPermissions'=> $sysModules->toArray()
            ];
            
        }catch (Exception $e) {

            //build the response
            $code = 401;
            $status = 'Exception';
            $message = "Something went wrong";
            $dataContent = $e;
        }

        return $this->returnApiResult($code, $status, $message, $dataContent);

    }
    

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){
        try{

            //Find the user using the id
            $user = User::find($id);

            if (!$user) { //If the user not found
                return $this->userNotFoundResponse();
            }
            
            //Delete all old permissions
            $user->permissions()->detach();
            
            //Add new permissions
            foreach($request->all() as $row){
                $result = $user->permissions()->attach(Permission::where('name', $row)->first());
            }

            //Get all permissions of the user
            $permissions = $user->permissions()->get();
            //Get all System Modules with thier permissions
            $sysModules = SystemModule::with('permissions')->get();


            //build the response
            $code = 200;
            $status = 'success';
            $message = 'The permissions have been updated successfully';
            $dataContent = [
                'user_permissions' => $permissions->toArray(),
                'sysModulesPermissions'=> $sysModules->toArray()
            ];

            

        }catch (Exception $e) {

                //build the response
                $code = 401;
                $status = 'Exception';
                $message = "Something went wrong";
                $dataContent = $e;
        }

        return $this->returnApiResult($code, $status, $message, $dataContent);
        
    }

  
}
