<?php

namespace App\Http\Controllers;

use App\Activity;
use App\Employee;
use App\User;
use App\Point;
use App\Level;
use App\Formula;
use App\Target;
use App\TempData;
use App\Unit;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;

use Auth;
use Log;
use Exception;


class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){  

        try{
            //Get all 
            $activities = Activity::with('createdBy:id,first_name,second_name')
                                    ->with('clientData:id,first_name,second_name')
                                    ->get();
                                
            //build the response
            $code = 200;
            $status = 'success';
            $message = "All Activities";
            $dataContent = $activities->toArray();
            
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

            $user_id = 8; //Auth::user()->id;

            
            $activity_value = 0; //just an initialization
            //get the loged user data
            $employee = Employee::where('user_id', $user_id)->first();
            if(!$employee){ return $this->modelNotFoundResponse(); }

            //Validate the request data
            $validator = Validator::make($request->all(), [
                'client_id' => 'required|numeric',
                'activity_type' => 'required',
                'activity_status' => 'required',
                'feedback' => 'required',
                'activity_date' => 'required|date_format:Y-m-d'
             ]);

 
            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }else{//successful validation

                //if this is won status, then this the wrong rout to store
                /*if($request->activity_status == 'Won'){

                    //build the response
                    $code = 401; //successful Request:
                    $status = 'erorr';
                    $message = "This the wrong rout to store the won status";
                    $dataContent = '';

                    return $this->returnApiResult($code, $status, $message, $dataContent);
                }*/

                //Make sure that the client exist
                $userExist = User::find($request->client_id);
                if(!$userExist){ return $this->modelNotFoundResponse(); }


                //Calc. the amoount of gained points from this activity
                
                //0- If the activity is 'Call', you have to make sure that, this the first call for today
                if($request->activity_type == 'Call'){

                    $activityExist = Activity::where('activity_date', $request->activity_date)
                                                ->where('user_id', $request->client_id)
                                                ->first();
                }else{
                    $activityExist = null;
                }
                
                
                if($activityExist != null){ //he made a call today to the same client so no points will gained for this activity
                    $activity_value = 0;
                }else{

                    //1- Get the points for this activity
                    $points = Point::where('action', $request->activity_type)->first();
                    if(!$points){ return $this->modelNotFoundResponse(); }

                    //2- Get the current datetime
                    $currentDateTime = strtotime(date('Y-m-d H:m:i'));
                    //3- Convert the happy start and end dates formate
                    $happyStartDateTime = strtotime($points->happy_start);
                    $happyEndDateTime = strtotime($points->happy_end);
                     

                    //4- Check if the currentDateTime in between the  happy start and end dates or not
                    if(($currentDateTime >= $happyStartDateTime) && ($currentDateTime >= $happyEndDateTime)){
                        //The happy hour is enabled know
                        $activity_value = $points->happy_points;

                        //If the status is won, add to activity_value the value of Won from the table
                        if($request->activity_status == 'Won'){

                            $wonPoints = Point::where('action', 'Won')->first();
                            if(!$wonPoints){ return $this->modelNotFoundResponse(); }

                            $activity_value += $wonPoints->happy_points;
                        }

                    }else{
                        //The happy hour is disabled
                        $activity_value = $points->fixed_points;

                        //If the status is won add to activity_value the value of Won from the table
                        if($request->activity_status == 'Won'){

                            $wonPoints = Point::where('action', 'Won')->first();
                            if(!$wonPoints){ return $this->modelNotFoundResponse(); }

                            $activity_value += $wonPoints->fixed_points;
                        }
                    }

                    //update the points and the level
                    $this->updatePointsAndLevel($employee, $activity_value);
                   
                }
                
                //add to the request the logged user id then create the record
                $except_fields = [
                    'user_id',
                    'activity_value',
                    'created_by'
                ];
                
                $extra_feilds = [
                    'activity_value' => $activity_value,
                    'user_id' => $request->client_id, //the id of the client in the users table
                    'created_by' => $user_id, //The id of the logged user from users table
                ];
                $result = Activity::create($request->except($except_fields) + $extra_feilds);

                if($result){ //successful operation

                    if($activity_value != 0){ // it does not make sense to applay target or formula if the value of the activity is zero
                        
                        if($employee->formula_flag != 1){ //to make sure that the formula applied once each time
                            //Check if the fromula is enabled or not, then appaly if it is enabled
                            $this->applyFormulaIfEnabled($user_id);
                        }
                        
                        if($employee->target_flag != 1){ //to make sure that the target applied once each time
                            //Check if the target is enabled or not, then appaly if it is enabled
                           $this->applyTargetIfEnabled($user_id);
                             
                        }

                    }
                        
                    
                    //build the response
                    $code = 200; //successful Request:
                    $status = 'success';
                    $message = "The data has been added successfully";
                    $dataContent = ['row_id'=> $result->id];

                }else{ //Unknown error happened

                    //Unknown error happened
                    return $this->unknownErrorHappenedMsg();
                }

            }//End of validation

            return $this->returnApiResult($code, $status, $message, $dataContent);

        }catch (Exception $e) { 

           //log what happend
           Log::channel('system_exceptions')->info('Exceptions:', [$e]);

           //Unknown error happened
           return $this->unknownErrorHappenedMsg();
             
        }
    }

    

    /**
     * A function to check if the formula is enabled and if it is, it will apply it
     * @param: 
     * @return: 
     */
    private function applyFormulaIfEnabled($user_id){

        try{

             $employee = Employee::where('user_id', $user_id)->first();
            
            //1- Get the current date and convert to time
            $currentDateTime = strtotime(date('Y-m-d'));

            //2- get the formula start and end dates
            $formula = Formula::find(1);
            if(!$formula){ return $this->modelNotFoundResponse(); }

            //3- convert the formula start and end to time
            $formulaStartDate = strtotime($formula->formula_start);
            $formulaEndDate = strtotime($formula->formula_end);

            //4- check if the current date in between the start and the end date or not
            if(($currentDateTime >= $formulaStartDate) && ($currentDateTime <= $formulaEndDate)){
 
                //Change the formula flag to '1' for this employee
                $employee->formula_flag = 1;
                $employee->save();
                
                //get the diff. between the current level's points and the next level's points
                $level = Level::find($employee->level_id);
                if(!$level){ return $this->modelNotFoundResponse(); }
                $nextLevel = Level::where('value','>', $level->value)
                                    ->orderBy('id', 'asc')
                                    ->first();
                if(!$nextLevel){ return $this->modelNotFoundResponse(); }

                $gained_points = $nextLevel->value - $level->value; 
                 
                //update the points and the level
                return $this->updatePointsAndLevel($employee, $gained_points);


            }else{//the formula is disabled
                return true;
            }
        }catch (Exception $e) {  

            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
 
            //Unknown error happened
            return $this->unknownErrorHappenedMsg();
              
         }
        

                       
         
    }

     /**
     * A function to check if the target is enabled and if it is, it will apply it
     * @param: 
     * @return: 
     */
    private function applyTargetIfEnabled($user_id){
        try{

            
            $employee = Employee::where('user_id', $user_id)->first();

            //1- Get the current date and convert to time
            $currentDateTime = strtotime(date('Y-m-d'));

            //2- get the target start and end dates
            $target = Target::find(1);
            if(!$target){ return $this->modelNotFoundResponse(); }

            //3- convert the target start and end to time
            $targetStartDate = strtotime($target->target_start);
            $targetEndDate = strtotime($target->target_end);

            //4- check if the current date in between the start and the end date or not
            if(($currentDateTime >= $targetStartDate) && ($currentDateTime <= $targetEndDate)){
               
                $from = $target->target_start;
                $to = $target->target_end;
                
                //count the number of calls
                $calls = Activity::whereBetween('activity_date', [$from, $to])
                                    ->where('activity_type', 'call')
                                    ->where('created_by', $user_id)
                                    ->where('activity_value', '>', 0)
                                    ->get()
                                    ->count();

                //count the number of meetings
                $meetings = Activity::whereBetween('activity_date', [$from, $to])
                                    ->where('activity_type', 'meeting')
                                    ->where('created_by', $user_id)
                                    ->where('activity_value', '>', 0)
                                    ->get()
                                    ->count();

                //count the number of Showing
                $showing = Activity::whereBetween('activity_date', [$from, $to])
                                        ->where('activity_status', 'Showing')
                                        ->where('created_by', $user_id)
                                        ->where('activity_value', '>', 0)
                                        ->get()
                                        ->count();


                 //count the number of wons
                 $wons = Activity::whereBetween('activity_date', [$from, $to])
                                    ->where('activity_status', 'Won')
                                    ->where('created_by', $user_id)
                                    ->where('activity_value', '>', 0)
                                    ->get()
                                    ->count();
                                    
                                    
                
                //get the target of calls 
                $callTarget = Target::find(1);
                 //get the target of meetins 
                $meetinTarget = Target::find(2);
                 //get the target of wons 
                $wonTarget = Target::find(3);
                 //get the target of Showings 
                 $showingTarget = Target::find(4);

                if(!$callTarget || !$meetinTarget || !$wonTarget || !$showingTarget){ return $this->modelNotFoundResponse(); }

                if(($calls >= $callTarget->target_value) && 
                    ($meetings >= $meetinTarget->target_value) && 
                    ($showing >= $showingTarget->target_value) && 
                    ($wons >= $wonTarget->target_value) 
                ){ //he achieved the target
                    
                    //Change the target flag to '1' for this employee
                    $employee->target_flag = 1;
                    $employee->save();

                    $gained_points = $callTarget->target_points; //I ahve used 'callTarget->target_points' here because target points for all actions are the same
                    //update the points and the level
                    return $this->updatePointsAndLevel($employee, $gained_points);

                }else{
                    return true;
                }
                
            }else{ //the target is disabled
                return true; 
            }
        }catch (Exception $e) { 

            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
 
            //Unknown error happened
            return $this->unknownErrorHappenedMsg();
              
         }
    }

    /**
     * A function to update the amoumt of points and upgrade the level 
     * @param: the logged employee object and the gained points to add them
     * @return: success or exception 
     */
    private function updatePointsAndLevel($employee, $gained_points){

        try{
            //add the poinys of the target to the total number of points
            $newTotalNumOfPoints = $employee->points + $gained_points; 

            //Check the level 
            $level = Level::find($employee->level_id);
            if(!$level){ return $this->modelNotFoundResponse(); }

            if($newTotalNumOfPoints > $level->value){
                $newLevel = Level::where('value','>=', $newTotalNumOfPoints)
                                    ->orderBy('id', 'asc')
                                    ->first();
                if(!$newLevel){ return $this->modelNotFoundResponse(); }
                $employee->level_id = $newLevel->id;
            }

            //update the total number of points
            $employee->points = $newTotalNumOfPoints;
            $employee->save();

            return true;
            
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
     * @param  \App\Activity  $activity
     * @return \Illuminate\Http\Response
     */
    public function show(Activity $activity)
    {
        return $this->pageNotFoundResponse();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Activity  $activity
     * @return \Illuminate\Http\Response
     */
    public function edit(Activity $activity)
    {
        return $this->pageNotFoundResponse();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Activity  $activity
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Activity $activity)
    {
        return $this->pageNotFoundResponse();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Activity  $activity
     * @return \Illuminate\Http\Response
     */
    public function destroy(Activity $activity)
    {
        return $this->pageNotFoundResponse();
    }

    

    
}
