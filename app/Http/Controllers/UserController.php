<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;

use App\User;
use Illuminate\Http\Request;

use Auth;
use Log;
use Exception;

class UserController extends Controller
{
     
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        try{
            //Get all System users
            $users = User::select('id', 'active', 'type', 'first_name', 'second_name', 'mobile', 'gender', 'email', 'created_at', 'updated_at')
                            ->where('type', 'user')
                            ->get();

            //build the response
            $code = 200;
            $status = 'success';
            $message = "All users";
            $dataContent = $users->toArray();
            
        }catch (Exception $e) {

           //log what happend
           Log::channel('system_exceptions')->info('Exceptions:', [$e]);

           //Unknown error happened
           return $this->unknownErrorHappenedMsg();
        }

        return $this->returnApiResult($code, $status, $message, $dataContent);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(){
        return $this->pageNotFoundResponse();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){

        $user_id = 1; //Auth::user()->id;

        //Validate the request data
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|max:50',
            'second_name' => 'required|max:50',
            'mobile' => 'required',
            'gender' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) { //some of request data are missing or invalid

            //return the validation errors
            return $this->validationErrorsResponse($validator->errors());

        }else{ //successful validation
         
            //Add new user
            $user = new User;

            //prep. the data
            $user->active = 1;
            $user->type = 'user';
            $user->first_name = $request->input('first_name');
            $user->second_name = $request->input('second_name');
            $user->mobile = $request->input('mobile');
            $user->gender = $request->input('gender');
            $user->email = $request->input('email');
            $user->password = bcrypt($request->input('password'));
            $user->created_by = $user_id;

            //Save the new user
            $result = $user->save();

            if($result){
                
                //build the response
                $code = 200; //successful Request:
                $status = 'success';
                $message = "The user has been added successfully";
                $dataContent = ['created_user_id'=> $user->id];

            }else{ //Unknown error happened

                //Unknown error happened
                return $this->unknownErrorHappenedMsg();
            }
           
        }

        return $this->returnApiResult($code, $status, $message, $dataContent);
       
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user){

        return $this->pageNotFoundResponse();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit($id){
        //Find the user
        $user = User::select('id', 'active', 'type', 'first_name', 'second_name', 'mobile', 'gender', 'email', 'created_at', 'updated_at')
                        ->where('type', 'user')
                        ->find($id);

        //could not find the user
        if(!$user){
            return $this->userNotFoundResponse();
        }

        //build the response
        $code = 200; //successful Request:
        $status = 'success';
        $message = "The user has been fetched successfully";
        $dataContent = $user;

        return $this->returnApiResult($code, $status, $message, $dataContent);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user_id = 1; //Auth::user()->id;

         //Validate the request data
         $validator = Validator::make($request->all(), [
            'active' => 'required|numeric',
            'first_name' => 'required|max:50',
            'second_name' => 'required|max:50',
            'mobile' => 'required',
            'gender' => 'required',
            'email' => 'required|email|unique:users,email,' . $id 
        ]);

        if ($validator->fails()) { //some of request data are missing or invalid

           //return the validation errors
           return $this->validationErrorsResponse($validator->errors());

        }else{ //successful validation

            //Find the user
            $user = User::where('type', 'user')->find($id);

            //could not find the user
            if(!$user){
                return $this->userNotFoundResponse();
            }

            //prep. the data
            $user->active = $request->input('active');
            $user->first_name = $request->input('first_name');
            $user->second_name = $request->input('second_name');
            $user->mobile = $request->input('mobile');
            $user->gender = $request->input('gender');
            $user->email = $request->input('email');
            $user->created_by = $user_id;

            //check if the request has a password 
            if (!empty($request->input('password'))){
                $user->password = bcrypt($request->input('password'));
            }
    
            //Update the user's info
            $result = $user->update();

            if($result){
                
                //build the response
                $code = 200; //successful Request:
                $status = 'success';
                $message = "The user has been updated successfully";
                $dataContent = ['updated_user_id'=> $user->id];

            }else{ //Unknown error happened

               //Unknown error happened
               return $this->unknownErrorHappenedMsg();
            }

        }

        return $this->returnApiResult($code, $status, $message, $dataContent);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        return $this->pageNotFoundResponse();
    }


    /*********************************************************************
     * Broker
     **********************************************************************/
    /**
     * A function to get all broker 
     * @param  
     * @return 
     */
    public function indexBroker(){

        try{
            //Get all System broker
            $broker = User::select('id', 'active', 'type', 'first_name', 'second_name', 'mobile', 'gender', 'email', 'created_at', 'updated_at')
                            ->where('type', 'broker')
                            ->get();

            //build the response
            $code = 200;
            $status = 'success';
            $message = "All broker";
            $dataContent = $broker->toArray();
            
        }catch (Exception $e) {

           //log what happend
           Log::channel('system_exceptions')->info('Exceptions:', [$e]);

           //Unknown error happened
           return $this->unknownErrorHappenedMsg();
        }

        return $this->returnApiResult($code, $status, $message, $dataContent);
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeBroker(Request $request){

        $user_id = 1; //Auth::user()->id;

        //Validate the request data
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|max:50',
            'second_name' => 'required|max:50',
            'mobile' => 'required',
            'gender' => 'required',
            'company_name' => 'required',
        ]);

        if ($validator->fails()) { //some of request data are missing or invalid

            //return the validation errors
            return $this->validationErrorsResponse($validator->errors());

        }else{ //successful validation
         
            //Add new user
            $user = new User;

            //prep. the data
            $user->active = 1;
            $user->type = 'broker';
            $user->first_name = $request->input('first_name');
            $user->second_name = $request->input('second_name');
            $user->mobile = $request->input('mobile');
            $user->gender = $request->input('gender');
            $user->company_name = $request->input('company_name');
            $user->password =  bcrypt(RAND(9999, 99999999));
            $user->created_by = $user_id;

            //Save the new user
            $result = $user->save();

            if($result){
                
                //build the response
                $code = 200; //successful Request:
                $status = 'success';
                $message = "The broker has been added successfully";
                $dataContent = ['created_broker_id'=> $user->id];

            }else{ //Unknown error happened

                //Unknown error happened
                return $this->unknownErrorHappenedMsg();
            }
           
        }

        return $this->returnApiResult($code, $status, $message, $dataContent);
       
    }


     /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function editBroker($id){
        //Find the user
        $user = User::select('id', 'active', 'type', 'first_name', 'second_name', 'mobile', 'gender', 'email', 'created_at', 'updated_at')
                        ->where('type', 'broker')
                        ->find($id);

        //could not find the user
        if(!$user){
            return $this->userNotFoundResponse();
        }

        //build the response
        $code = 200; //successful Request:
        $status = 'success';
        $message = "The broker has been fetched successfully";
        $dataContent = $user;

        return $this->returnApiResult($code, $status, $message, $dataContent);
    }

     /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function updateBroker(Request $request, $id){

        $user_id = 1; //Auth::user()->id;

         //Validate the request data
         $validator = Validator::make($request->all(), [
            'active' => 'required|numeric',
            'first_name' => 'required|max:50',
            'second_name' => 'required|max:50',
            'mobile' => 'required',
            'gender' => 'required',
            'company_name' => 'required',
        ]);

        if ($validator->fails()) { //some of request data are missing or invalid

           //return the validation errors
           return $this->validationErrorsResponse($validator->errors());

        }else{ //successful validation

            //Find the broker
            $user = User::where('type', 'broker')->find($id);

            //could not find the user
            if(!$user){
                return $this->userNotFoundResponse();
            }

            //prep. the data
            $user->active = $request->input('active');
            $user->first_name = $request->input('first_name');
            $user->second_name = $request->input('second_name');
            $user->mobile = $request->input('mobile');
            $user->gender = $request->input('gender');
            $user->company_name = $request->input('company_name');
            $user->created_by = $user_id;

            //Update the user's info
            $result = $user->update();

            if($result){
                
                //build the response
                $code = 200; //successful Request:
                $status = 'success';
                $message = "The broker has been updated successfully";
                $dataContent = ['updated_broker_id'=> $user->id];

            }else{ //Unknown error happened

               //Unknown error happened
               return $this->unknownErrorHappenedMsg();
            }

        }

        return $this->returnApiResult($code, $status, $message, $dataContent);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function showBroker($id){

        //Find the user
        $user = User::select('id', 'active', 'type', 'first_name', 'second_name', 'mobile', 'gender', 'email', 'created_at', 'updated_at')
                        ->where('type', 'broker')
                        ->find($id);

        //could not find the user
        if(!$user){
            return $this->userNotFoundResponse();
        }

        //build the response
        $code = 200; //successful Request:
        $status = 'success';
        $message = "The broker has been fetched successfully";
        $dataContent = $user;

        return $this->returnApiResult($code, $status, $message, $dataContent);
    }




}
