<?php

namespace App\Http\Controllers;

use App\Todo;
use App\User;

use Illuminate\Http\Request;


use Illuminate\Support\Facades\Validator;

use Auth;
use Log;
use Exception;

class TodoController extends Controller
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
                //Get all todos
                $todos = Todo::with('createdBy:id,first_name,second_name')
                                ->with('assignedTo:id,first_name,second_name')
                                ->get();
            }else{
                //Get all todos for the logged user
                $todos = Todo::with('createdBy:id,first_name,second_name')
                                ->with('assignedTo:id,first_name,second_name')
                                ->where('assigned_to', $user_id)
                                ->get();
            }

            //build the response
            $code = 200;
            $status = 'success';
            $message = "All Todos";
            $dataContent = $todos->toArray();
            
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

        try{

            $user_id = 1; //Auth::user()->id;

            //Validate the request data
            $validator = Validator::make($request->all(), [
                'assigned_to' => 'required|numeric',
                'todo_desc' => 'required',
                'todo_date' => 'required|date_format:"Y-m-d H:i:s',
                'start_date' => 'required|date_format:"Y-m-d H:i:s',
                'end_date' => 'required|date_format:"Y-m-d H:i:s',
            ]);
           
            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }else{//successful validation
            
                //add to the request the logged user id then create the record
                $except_fields = [
                    'todo_status',
                    'created_by',
                ];
                $extra_feilds = [
                    'todo_status' => 'Todo',
                    'created_by' => $user_id,
                ];
                $result = Todo::create($request->except($except_fields) + $extra_feilds);

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
     * @param  \App\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function show($id){
        
        return $this->pageNotFoundResponse();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Todo  $todo
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
                $todo = Todo::with('createdBy:id,first_name,second_name')
                                ->with('assignedTo:id,first_name,second_name')
                                ->find($id);

            }else{

                //Find the model
                $todo = Todo::with('createdBy:id,first_name,second_name')
                                ->with('assignedTo:id,first_name,second_name')
                                ->where('reassigned_to', $user_id)
                                ->find($id);

            }

           //could not find the request row
           if(!$todo){
               return $this->modelNotFoundResponse();
           }

           //build the response
           $code = 200; //successful Request:
           $status = 'success';
           $message = "The data has been fetched successfully";
           $dataContent = $todo;

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
     * @param  \App\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){
        
        try{
            
            $user_id = 1; //Auth::user()->id;

            //Validate the request data
            $validator = Validator::make($request->all(), [
                'assigned_to' => 'required|numeric',
                'todo_desc' => 'required',
                'todo_date' => 'required|date_format:"Y-m-d H:i:s',
                'start_date' => 'required|date_format:"Y-m-d H:i:s',
                'end_date' => 'required|date_format:"Y-m-d H:i:s',
                'todo_status' => 'required',
            ]);


            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }else{//successful validation
            
                //Find the model
                $todo = Todo::where('created_by', $user_id)->find($id);

                //could not find the requested row
                if(!$todo){
                    return $this->modelNotFoundResponse();
                }
                
                //add to the request the logged user id then create the record
                $result = $todo->update($request->except(['created_by']) + ['created_by' => $user_id]);

                
                if($result){ //successful operation
                    
                    //build the response
                    $code = 200; //successful Request:
                    $status = 'success';
                    $message = "The data has been updated successfully";
                    $dataContent = ['row_id'=> $todo->id];

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
     * @param  \App\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Todo $todo){
        return $this->pageNotFoundResponse();
    }

    /**
     * A function to update the todo status of an employee 
     * @request: The id of the Todo row
     * @response: The id of the updated todo status 
     */
    public function updateMyTodoStatus(Request $request){
        
        try{

            $user_id = 8; //Auth::user()->id;

            //Validate the request data
            $validator = Validator::make($request->all(), [
               'todo_id' => 'required',
               'todo_status' => 'required',
            ]);


            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }else{//successful validation
            
                $todo_id = $request->input('todo_id');
                $todo_status = $request->input('todo_status');

                //Find the model
                $todo = Todo::where('assigned_to', $user_id)->find($todo_id);

                //could not find the requested row
                if(!$todo){
                    return $this->modelNotFoundResponse();
                }
                
                $todo->todo_status = $todo_status;

                //Save the new data
                $result = $todo->save();
                
                if($result){ //successful operation
                    
                    //build the response
                    $code = 200; //successful Request:
                    $status = 'success';
                    $message = "The data has been updated successfully";
                    $dataContent = ['row_id'=> $todo->id];

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
}
