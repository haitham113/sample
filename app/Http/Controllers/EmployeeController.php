<?php

namespace App\Http\Controllers;

use App\Employee;
use App\User;
use App\Client;
use App\Lead;
use App\Unit;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;

use Auth;
use Log;
use Exception;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
         
        try{
            //Get all Employees
            $employees = User::with('employee')
                                ->with('employee.teamLeader')
                                ->with('employee.position')
                                ->with('employee.level')
                                ->with('employee.createdBy:id,first_name,second_name')
                                ->where('type', 'employee')
                                ->get();
                                
            //build the response
            $code = 200;
            $status = 'success';
            $message = "All Employees";
            $dataContent = $employees->toArray();
            
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
                'active' => 'required|numeric',
                'team_leader_id' => 'required|numeric',
                'position_id' => 'required|numeric',
                'first_name' => 'required',
                'second_name' => 'required',
                'email' => 'required|email|unique:users',
                'mobile' => 'required',
                'password' => 'required',
                'gender' => 'required',
                'job_title' => 'required',
                'national_id' => 'required',
                'joining_date' => 'required|date'
             ]);


            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }else{//successful validation

                //First create a record to this employee in the users table
                $user = new User;

                //prep. the data
                $user->active = $request->input('active');
                $user->type = 'employee';
                $user->first_name = $request->input('first_name');
                $user->second_name = $request->input('second_name');
                $user->mobile = $request->input('mobile');
                $user->gender = $request->input('gender');
                $user->email = $request->input('email');
                $user->password = bcrypt($request->input('password'));
                $user->created_by = $user_id;

                //Save the new user
                $result = $user->save();
            
                //add to the request the logged user id then create the record
                $extra_feilds = [
                    'user_id' => $user->id, 
                    'created_by' => $user_id, 
                    'points' => 0,
                    'level_id' => 1
                ];
                $result = Employee::create($request->except(['created_by', 'type', 'level_id']) + $extra_feilds);

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

            //Find the model
            $employee =  User::with('employee')
                                ->with('employee.teamLeader')
                                ->with('employee.position')
                                ->with('employee.level')
                                ->with('employee.createdBy:id,first_name,second_name')
                                ->where('type', 'employee')
                                ->find($id);
                    
            //could not find the requested row
            if(!$employee){
                return $this->modelNotFoundResponse();
            }
            
            $employee->numOfActivites = $employee->calcActivities();
            $employee->totalSales = Unit::where('sold_by', $employee->user_id)->sum('original_price');
            
            //build the response
            $code = 200; //successful Request:
            $status = 'success';
            $message = "The data has been fetched successfully";
            $dataContent = $employee;
 
            return $this->returnApiResult($code, $status, $message, $dataContent);
 
        }catch(Exception $e){ dd($e);
            
            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
 
            //Unknown error happened
            return $this->unknownErrorHappenedMsg();
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function edit($id){

        try{

            //Find the model
            $employee =  User::with('employee')
                                ->with('employee.teamLeader')
                                ->with('employee.position')
                                ->with('employee.level')
                                ->with('employee.createdBy:id,first_name,second_name')
                                ->where('type', 'employee')
                                ->find($id);
                    
            //could not find the requested row
            if(!$employee){
                return $this->modelNotFoundResponse();
            }
            
            $employee->numOfActivites = $employee->calcActivities();
            $employee->totalSales = Unit::where('sold_by', $employee->user_id)->sum('original_price');
            
            //build the response
            $code = 200; //successful Request:
            $status = 'success';
            $message = "The data has been fetched successfully";
            $dataContent = $employee;
 
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
     * @param  \App\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){
        
        try{
            
            $user_id = 1; //Auth::user()->id;

            //Validate the request data
            $validator = Validator::make($request->all(), [
                'active' => 'required|numeric',
                'position_id' => 'required|numeric',
                'team_leader_id' => 'required|numeric',
                'first_name' => 'required',
                'second_name' => 'required',
                'email' => 'required|email|unique:users,email,' . $id,
                'mobile' => 'required',
                'gender' => 'required',
                'job_title' => 'required',
                'national_id' => 'required',
                'joining_date' => 'required|date_format:Y-m-d',
            ]);

            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }else{//successful validation
            
                //First create a record to this employee in the users table
                $user = User::find($id);

                //could not find the requested model
                if(!$user){
                    return $this->modelNotFoundResponse();
                }

                //Get the Employee info
                $employee = Employee::where('user_id', $user->id)->first();

                //could not find the requested model
                if(!$employee){
                    return $this->modelNotFoundResponse();
                }
            

                //prep. the data
                $user->active = $request->input('active');
                $user->first_name = $request->input('first_name');
                $user->second_name = $request->input('second_name');
                $user->mobile = $request->input('mobile');
                $user->gender = $request->input('gender');
                $user->email = $request->input('email');
                if ($request->has('password')) {
                    $user->password = bcrypt($request->input('password'));
                }

                //Save the the user
                $result = $user->save();
            
                //add to the request the logged user id then create the record
                $extra_feilds = [
                    'created_by' => $user_id, 
                ];
                //
                $result = $employee->update($request->except(['created_by', 'type', 'level_id']) + $extra_feilds);

                if($result){ //successful operation
                    
                    //build the response
                    $code = 200; //successful Request:
                    $status = 'success';
                    $message = "The data has been updated successfully";
                    $dataContent = ['row_id'=> $employee->id];

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
     * @param  \App\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function destroy(Employee $employee)
    {
        return $this->pageNotFoundResponse();
    }

    /**
     * a function to inject points or commissions to an employee 
     *
     * @param   
     * @return 
     */
    public function injectPointsOrCommission(Request $request){
        
       try{

            $user_id = 1; //Auth::user()->id;

            //Validate the request data
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'injectTarget' => 'required',
                'value' => 'required|numeric',
            ]);
        
            if ($validator->fails()) { //some of requested data are missing or invalid
    
                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());
    
            }

            $user_id = $request->user_id;
            $injectTarget = $request->injectTarget;
            $value = $request->value;

            //find the employee 
            $employee = Employee::where('user_id', $user_id)->first();
            if(!$employee){return $this->modelNotFoundResponse();}

            if($injectTarget == 'points'){
                
                //Update the points value
                //1- get the old points
                $points = $employee->points;
                //2- add the new value to the old one
                $total = $points + $value;
                //3- update the points of the employee
                $employee->points = $total;
                //4- save the data
                $result = $employee->save();

            }elseif($injectTarget == 'commission'){

                //Update the commissions value
                //1- get the old commissions
                $commissions = $employee->commissions;
                //2- add the new value to the old one
                $total = $commissions + $value;
                //3- update the commissions of the employee
                $employee->commissions = $total;
                //4- save the data
                $result = $employee->save();

            }else{
                
                //build the response
                $code = 401; //successful Request:
                $status = 'Error';
                $message = "The injection target must be points or commission";
                $dataContent = ['row_id'=> $employee->id];

            }

            if($result){ //successful operation
                
                //build the response
                $code = 200; //successful Request:
                $status = 'success';
                $message = "The data has been updated successfully";
                $dataContent = ['row_id'=> $employee->id];

            }else{ //Unknown error happened

                //Unknown error happened
                return $this->unknownErrorHappenedMsg();
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
     * a function to deduct points or commissions from an employee 
     *
     * @param   
     * @return 
     */
    public function deductPointsOrCommission(Request $request){
       
        try{

            $user_id = 1; //Auth::user()->id;

            //Validate the request data
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'injectTarget' => 'required',
                'value' => 'required|numeric',
            ]);
        
            if ($validator->fails()) { //some of requested data are missing or invalid
    
                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());
    
            }

            $user_id = $request->user_id;
            $injectTarget = $request->injectTarget;
            $value = $request->value;

            //find the employee 
            $employee = Employee::where('user_id', $user_id)->first();
            if(!$employee){return $this->modelNotFoundResponse();}

            if($injectTarget == 'points'){
                
                //Update the points value
                //1- get the old points
                $points = $employee->points? $employee->points: 0;
                //2- add the new value to the old one
                $total = $points - $value;
                //3- update the points of the employee
                $employee->points = $total;
                //4- save the data
                $result = $employee->save();

            }elseif($injectTarget == 'commission'){

                //Update the commissions value
                //1- get the old commissions
                $commissions = $employee->commissions?$employee->commissions:0;
                //2- add the new value to the old one
                $total = $commissions - $value;
                //3- update the commissions of the employee
                $employee->commissions = $total;
                //4- save the data
                $result = $employee->save();

            }else{
                
                //build the response
                $code = 401; //successful Request:
                $status = 'Error';
                $message = "The injection target must be points or commission";
                $dataContent = ['row_id'=> $employee->id];

            }

            if($result){ //successful operation
                
                //build the response
                $code = 200; //successful Request:
                $status = 'success';
                $message = "The data has been updated successfully";
                $dataContent = ['row_id'=> $employee->id];

            }else{ //Unknown error happened

                //Unknown error happened
                return $this->unknownErrorHappenedMsg();
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
     * A function to reassign the work of the employee [clients, units, leads] 
     * to another employee
     * @param   
     * @return 
     */
    public function reassignEmployeeWork(Request $request){
        
        try{

            $user_id = 1; //Auth::user()->id;

            //Validate the request data
            $validator = Validator::make($request->all(), [
                'from_emp_id' => 'required|numeric',
                'to_emp_id' => 'required|numeric',
            ]);
        
            if ($validator->fails()) { //some of requested data are missing or invalid
    
                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());
    
            }else{

                $from_emp_id = $request->from_emp_id;
                $to_emp_id = $request->to_emp_id;

                //update client's table
                Client::where('created_by', $from_emp_id)->update(['created_by'=> $to_emp_id]);

                //update units table
                Unit::where('created_by', $from_emp_id)->update(['created_by'=> $to_emp_id]);

                //update leads table
                Lead::where('reassigned_to', $from_emp_id)->update(['reassigned_to'=> $to_emp_id]);

                //build the response
                $code = 200; //successful Request:
                $status = 'success';
                $message = "The data has been updated successfully";
                $dataContent = "";

                return $this->returnApiResult($code, $status, $message, $dataContent);

            }


        }catch(Exception $e){

            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);

            //Unknown error happened
            return $this->unknownErrorHappenedMsg();

        }
    }



    



}




