<?php

namespace App\Http\Controllers;

use App\Client;
use App\User;
use App\Unit;
use App\ClientsUnit;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;

use Auth;
use Log;
use Exception;
use DB;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
         
        try{

            //get the logged user
            $user_id = 1; //Auth::user()->id;
            $user = User::find($user_id);

            //could not find the request row
            if(!$user){
                return $this->modelNotFoundResponse();
            }

            if ($user->hasPermission('root_admin') || $user->hasPermission('team_leader')){
                //Get all clients
                $sql = "SELECT * FROM users ";
                $sql .= "LEFT JOIN clients ON users.id = clients.user_id ";
                $sql .= "WHERE type = 'client' ";
               
    
                $clients = DB::select($sql);

            
            }else{
                //Get all clients for the logged user
                $sql = "SELECT * FROM users ";
                $sql .= "LEFT JOIN clients ON users.id = clients.user_id ";
                $sql .= "WHERE type = 'client' ";
                $sql .= "AND created_by = $user_id ";
               
    
                $clients = DB::select($sql);
             
            }

            //build the response
            $code = 200;
            $status = 'success';
            $message = "All Clients";
            $dataContent = $clients;
            
        }catch (Exception $e) {dd($e);

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

        try{

            $user_id = 1; //Auth::user()->id;

            //Validate the request data
            $validator = Validator::make($request->all(), [
                'request_type' => 'required',
                'budget_from' => 'required|numeric',
                'budget_to' => 'required|numeric',
                'first_name' => 'required',
                'second_name' => 'required',
                'mobile' => 'required|unique',
                'gender' => 'required',
                'email' => 'email|unique:users',
             ]);


            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }else{//successful validation

                //First create a record to this Client in the users table
                $user = new User;

                //prep. the data
                $user->active = 0;
                $user->type = 'client';
                $user->first_name = $request->input('first_name');
                $user->second_name = $request->input('second_name');
                $user->mobile = $request->input('mobile');
                $user->gender = $request->input('gender');
                $user->email = $request->input('email');
                $user->created_by =  $user_id;
                $user->password = bcrypt(RAND(9999, 99999999));

                //Save the new user
                $result = $user->save();
            
                //add to the request the logged user id then create the record
                $extra_feilds = [
                    'user_id' => $user->id, 
                    'created_by' => $user_id, 
                ];

                //exclude some of the varibale from the request
                $except_fields = [
                    'created_by',
                    'type'
                ];
                $result = Client::create($request->except($except_fields) + $extra_feilds);

                if($result){ //successful operation
                    
                    //build the response
                    $code = 200; //successful Request:
                    $status = 'success';
                    $message = "The data has been added successfully";
                    $dataContent = ['row_id'=> $result->id];

                }else{ //Unknown error happened

                    //Unknown error happened
                    return $this->unknownErrorHappenedMsg();
                }

            }

            return $this->returnApiResult($code, $status, $message, $dataContent);

        }catch (Exception $e) {

           //log what happend
           Log::channel('system_exceptions')->info('Exceptions:', [$e]);

           //Unknown error happened
           return $this->unknownErrorHappenedMsg();
             
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function show($id){
        
        try{

            //get the logged user
            $user_id = 1; //Auth::user()->id;
            $user = User::find($user_id);

            //could not find the request row
            if(!$user){
                return $this->modelNotFoundResponse();
            }

            if ($user->hasPermission('root_admin') || $user->hasPermission('team_leader')){

                //Find the model
                $client =  User::with('client')
                            ->with('client.createdBy:id,first_name,second_name')
                            ->where('type', 'client')
                            ->find($id);
            }else{

                //Find the model
                $client =  User::with('client')
                            ->with('client.createdBy:id,first_name,second_name')
                            ->where('type', 'client')
                            ->where('created_by', $user_id)
                            ->find($id);

            }

            //could not find the requested row
            if(!$client){
                return $this->modelNotFoundResponse();
            }
 
            //build the response
            $code = 200; //successful Request:
            $status = 'success';
            $message = "The data has been fetched successfully";
            $dataContent = $client;
 
            return $this->returnApiResult($code, $status, $message, $dataContent);
 
         }catch(Exception $e){
            
            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
 
            //Unknown error happened
            return $this->unknownErrorHappenedMsg();
         }
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function edit($id){
        
        try{

            //get the logged user
            $user_id = 1; //Auth::user()->id;
            $user = User::find($user_id);

            //could not find the request row
            if(!$user){
                return $this->modelNotFoundResponse();
            }

            if ($user->hasPermission('root_admin') || $user->hasPermission('team_leader')){

                //Find the model
                $client =  User::with('client')
                            ->with('client.createdBy:id,first_name,second_name')
                            ->where('type', 'client')
                            ->find($id);
            }else{

                //Find the model
                $client =  User::with('client')
                            ->with('client.createdBy:id,first_name,second_name')
                            ->where('type', 'client')
                            ->where('created_by', $user_id)
                            ->find($id);

            }

            //could not find the requested row
            if(!$client){
                return $this->modelNotFoundResponse();
            }
 
            //build the response
            $code = 200; //successful Request:
            $status = 'success';
            $message = "The data has been fetched successfully";
            $dataContent = $client;
 
            return $this->returnApiResult($code, $status, $message, $dataContent);
 
         }catch(Exception $e){
            
            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
 
            //Unknown error happened
            return $this->unknownErrorHappenedMsg();
         }
    }


     /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Client    $client 
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){
        
        try{
            
            $user_id = 1; //Auth::user()->id;

            //Validate the request data
            $validator = Validator::make($request->all(), [
                'request_type' => 'required',
                'budget_from' => 'required|numeric',
                'budget_to' => 'required|numeric',
                'first_name' => 'required',
                'second_name' => 'required',
                'mobile' => 'required',
                'gender' => 'required',
                'email' => 'email|unique:users,email,' . $id,
            ]);


            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }else{//successful validation
            
                //get the logged user
                $user = User::find($user_id);

                //could not find the request row
                if(!$user){
                    return $this->modelNotFoundResponse();
                }

                if ($user->hasPermission('root_admin') || $user->hasPermission('team_leader')){

                    //Find the model
                    $client = Client::where('user_id', $id)->first();

                }else{

                    //Find the model
                    $client = Client::where('user_id', $id)
                                        ->where('created_by', $user_id)
                                        ->first();
                    

                }

                //could not find the requested row
                if(!$client){
                    return $this->modelNotFoundResponse();
                }
                
                //get the client data from the User table
                $user = User::find($id);

                //prep. the data
                $user->first_name = $request->input('first_name');
                $user->second_name = $request->input('second_name');
                $user->mobile = $request->input('mobile');
                $user->gender = $request->input('gender');
                $user->email = $request->input('email');
                $user->created_by =  $user_id;
                

                //Save the the user
                $result = $user->save();

                //add to the request the logged user id then create the record
                $extra_feilds = [
                    'created_by' => $user_id, 
                ];

                //exclude some of the varibale from the request
                $except_fields = [
                    'created_by',
                    'type'
                ];
                $result = $client->update($request->except($except_fields) + $extra_feilds);

                
                if($result){ //successful operation
                    
                    //build the response
                    $code = 200; //successful Request:
                    $status = 'success';
                    $message = "The data has been updated successfully";
                    $dataContent = ['row_id'=> $client->id];

                }else{ //Unknown error happened

                    //Unknown error happened
                    return $this->unknownErrorHappenedMsg();
                }

            }

            return $this->returnApiResult($code, $status, $message, $dataContent);

        }catch(Exception $e){

            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);

            //Unknown error happened
            return $this->unknownErrorHappenedMsg();

        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Client    $client 
     * @return \Illuminate\Http\Response
     */
    public function destroy($id){

        return $this->pageNotFoundResponse();
    }


    //to store what the client  Interested In
    public function storeInterestedInUnits(Request $request){
        try{
            
            $user_id = 1; //Auth::user()->id;

            //Validate the request data
            $validator = Validator::make($request->all(), [
                'client_id' => 'required|numeric',
                'unit_id' => 'required|numeric'
            ]);

            
            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }else{//successful validation
            
                $client_id = $request->client_id;
                $unit_id = $request->unit_id;

                $clientsUnit = new ClientsUnit();

                $clientsUnit->user_id = $client_id;
                $clientsUnit->unit_id = $unit_id;

                $clientsUnit->save();
               

            }

            //build the response
            $code = 200; //successful Request:
            $status = 'success';
            $message = "The data has been add successfully";
            $dataContent = '';

            return $this->returnApiResult($code, $status, $message, $dataContent);

        }catch(Exception $e){  

            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);

            //Unknown error happened
            return $this->unknownErrorHappenedMsg();

        }
    }

    //get what the client  Interested In
    public function getInterestedInUnitsForClient($id){
        try{
            
            $user_id = 1; //Auth::user()->id;

            $units = DB::select("SELECT clients_units.id as row_id, units.* From clients_units LEFT JOIN units ON units.id = clients_units.unit_id WHERE user_id = $id");
                
            //build the response
            $code = 200; //successful Request:
            $status = 'success';
            $message = "The data has been fetched successfully";
            $dataContent = $units;

            return $this->returnApiResult($code, $status, $message, $dataContent);

        }catch(Exception $e){  dd($e);

            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);

            //Unknown error happened
            return $this->unknownErrorHappenedMsg();

        }
    }
    
    
    //delete
    public function deleteInterestedInUnitForClient(Request $request){
        try{
            
            $user_id = 1; //Auth::user()->id;
            
            //Validate the request data
            $validator = Validator::make($request->all(), [
                'client_id' => 'required|numeric',
                'unit_id' => 'required|numeric'
            ]);

            
            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }
            
            $client_id = $request->client_id;
            $unit_id = $request->unit_id;

            ClientsUnit::where('user_id',$client_id)->where('unit_id',$unit_id)->delete();   

            //build the response
            $code = 200; //successful Request:
            $status = 'success';
            $message = "The data has been deleted successfully";
            $dataContent = '';

            return $this->returnApiResult($code, $status, $message, $dataContent);

        }catch(Exception $e){  

            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);

            //Unknown error happened
            return $this->unknownErrorHappenedMsg();

        }
    }
}
