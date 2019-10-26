<?php

namespace App\Http\Controllers;

use App\User;
use App\Point;
use App\Target;
use App\Formula;
use App\Employee;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;

use Auth;
use Log;
use Exception;


class PointController extends Controller
{
    /**
     * A function to get the fixed points 
     * @request:
     * @response: all actions with their values
     */
    public function getFixedPoints(){
        try{

            //get the logged user
            $user_id = 1; //Auth::user()->id;
            
            $points = Point::get(['action', 'fixed_points']);

            //build the response
            $code = 200;
            $status = 'success';
            $message = "All fixes points";
            $dataContent = $points->toArray();
            
        }catch (Exception $e) {

           //log what happend
           Log::channel('system_exceptions')->info('Exceptions:', [$e]);

           //Unknown error happened
           return $this->unknownErrorHappenedMsg();


        }

        return $this->returnApiResult($code, $status, $message, $dataContent);
    }

    /**
     * A function to update the fixed points 
     * @request: which contains the fixed points foreach action
     * @response: success or Exception
     */
    public function updateFixedPoints(Request $request){
        
        try{
            
            $user_id = 8; //Auth::user()->id;

            //Validate the request data
            $validator = Validator::make($request->all(), [
                'call_fixed_points' => 'required|numeric',
                'meeting_fixed_points' => 'required|numeric',
                'won_fixed_points' => 'required|numeric',
                'showing_fixed_points' => 'required|numeric',
             ]);

 
            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }else{

                //Update the fixed points of call, meeting, showing, and won
                $call = Point::find(1);
                $call->fixed_points = $request->call_fixed_points;
                $call->created_by = $user_id;
                $call->save();

                $meeting = Point::find(2);
                $meeting->fixed_points = $request->meeting_fixed_points;
                $meeting->created_by = $user_id;
                $meeting->save();

                $won = Point::find(3);
                $won->fixed_points = $request->won_fixed_points;
                $won->created_by = $user_id;
                $won->save();


                $showing = Point::find(4);
                $showing->fixed_points = $request->showing_fixed_points;
                $showing->created_by = $user_id;
                $showing->save();

                   
                //build the response
                $code = 200; //successful Request:
                $status = 'success';
                $message = "The data has been updated successfully";
                $dataContent = '';
            }
            
        }catch (Exception $e) {  

           //log what happend
           Log::channel('system_exceptions')->info('Exceptions:', [$e]);

           //Unknown error happened
           return $this->unknownErrorHappenedMsg();


        }

        return $this->returnApiResult($code, $status, $message, $dataContent);

    }

    /**
     * A function to get the Happy Hour points 
     * @request:
     * @response: all actions with their values
     */
    public function getHappyHourPoints(){
        try{

            //get the logged user
            $user_id = 1; //Auth::user()->id;
            
            $points = Point::get(['action', 'happy_points', 'happy_start', 'happy_end']);

            //build the response
            $code = 200;
            $status = 'success';
            $message = "All Happy Hour data";
            $dataContent = $points->toArray();
            
        }catch (Exception $e) {

           //log what happend
           Log::channel('system_exceptions')->info('Exceptions:', [$e]);

           //Unknown error happened
           return $this->unknownErrorHappenedMsg();


        }

        return $this->returnApiResult($code, $status, $message, $dataContent);
    }

    /**
     * A function to update the HappyHour points 
     * @request: which contains the HappyHour points foreach action
     * @response: success or Exception
     */
    public function updateHappyHourPoints(Request $request){
        
        try{
            
            $user_id = 8; //Auth::user()->id;

            //Validate the request data
            $validator = Validator::make($request->all(), [
                'call_HappyHour_points' => 'required|numeric',
                'meeting_HappyHour_points' => 'required|numeric',
                'won_HappyHour_points' => 'required|numeric',
                'showing_HappyHour_points' => 'required|numeric',
                'happy_start' => 'required|date_format:"Y-m-d H:i:s',
                'happy_end' => 'required|date_format:"Y-m-d H:i:s'
             ]);

 
            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }else{

                //Update the Happy Hour points of call, meeting, and won
                $call = Point::find(1);
                $call->happy_points = $request->call_HappyHour_points;
                $call->happy_start = $request->happy_start;
                $call->happy_end = $request->happy_end;
                $call->created_by = $user_id;
                $call->save();

                $meeting = Point::find(2);
                $meeting->happy_points = $request->meeting_HappyHour_points;
                $meeting->happy_start = $request->happy_start;
                $meeting->happy_end = $request->happy_end;
                $meeting->created_by = $user_id;
                $meeting->save();

                $won = Point::find(3);
                $won->happy_points = $request->won_HappyHour_points;
                $won->happy_start = $request->happy_start;
                $won->happy_end = $request->happy_end;
                $won->created_by = $user_id;
                $won->save();


                $showing = Point::find(4);
                $showing->happy_points = $request->showing_HappyHour_points;
                $showing->happy_start = $request->happy_start;
                $showing->happy_end = $request->happy_end;
                $showing->created_by = $user_id;
                $won->save();

                   
                //build the response
                $code = 200; //successful Request:
                $status = 'success';
                $message = "The data has been updated successfully";
                $dataContent = '';
            }
            
        }catch (Exception $e) {  

           //log what happend
           Log::channel('system_exceptions')->info('Exceptions:', [$e]);

           //Unknown error happened
           return $this->unknownErrorHappenedMsg();


        }

        return $this->returnApiResult($code, $status, $message, $dataContent);

    }


    /**
     * A function to get the Target points 
     * @request:
     * @response: all actions with their numbers
     */
    public function getTargetPoints(){
        try{

            //get the logged user
            $user_id = 1; //Auth::user()->id;
            
            $target = Target::with('createdBy:id,first_name,second_name')->get();

            //build the response
            $code = 200;
            $status = 'success';
            $message = "All target's data";
            $dataContent = $target->toArray();
            
        }catch (Exception $e) {

           //log what happend
           Log::channel('system_exceptions')->info('Exceptions:', [$e]);

           //Unknown error happened
           return $this->unknownErrorHappenedMsg();


        }

        return $this->returnApiResult($code, $status, $message, $dataContent);
    }

    /**
     * A function to update the Target points 
     * @request:  
     * @response: success or Exception
     */
    public function updateTargetPoints(Request $request){
        
        try{
            
            $user_id = 8; //Auth::user()->id;

            //Validate the request data
            $validator = Validator::make($request->all(), [
                'call_target_value' => 'required|numeric',
                'meeting_target_value' => 'required|numeric',
                'won_target_value' => 'required|numeric',
                'showing_target_value' => 'required|numeric',
                'target_start' => 'required|date',
                'target_end' => 'required|date',
                'target_points' => 'required|numeric'
             ]);

 
            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }else{
 
                //Update the target points of call, meeting, and won
                $call = Target::find(1);
                $call->target_value = $request->call_target_value;
                $call->target_points = $request->target_points;
                $call->target_start = $request->target_start;
                $call->target_end = $request->target_end;
                $call->created_by = $user_id;
                $call->save();

                $meeting = Target::find(2);
                $meeting->target_value = $request->meeting_target_value;
                $meeting->target_points = $request->target_points;
                $meeting->target_start = $request->target_start;
                $meeting->target_end = $request->target_end;
                $meeting->created_by = $user_id;
                $meeting->save();

                $won = Target::find(3);
                $won->target_value = $request->won_target_value;
                $won->target_points = $request->target_points;
                $won->target_start = $request->target_start;
                $won->target_end = $request->target_end;
                $won->created_by = $user_id;
                $won->save();


                $showing = Target::find(4);
                $showing->target_value = $request->showing_target_value;
                $showing->target_points = $request->target_points;
                $showing->target_start = $request->target_start;
                $showing->target_end = $request->target_end;
                $showing->created_by = $user_id;
                $showing->save();

                

                //Reset the target flag in the employee table
                Employee::query()->update(['target_flag' => 0]);
                   
                //build the response
                $code = 200; //successful Request:
                $status = 'success';
                $message = "The data has been updated successfully";
                $dataContent = '';
            }
            
        }catch (Exception $e) {  

           //log what happend
           Log::channel('system_exceptions')->info('Exceptions:', [$e]);

           //Unknown error happened
           return $this->unknownErrorHappenedMsg();


        }

        return $this->returnApiResult($code, $status, $message, $dataContent);

    }


    /**
     * A function to get formula's dates
     * @request:
     * @response: formula's dates
     */
    public function getFormula(){
        try{

            //get the logged user
            $user_id = 1; //Auth::user()->id;
            
            $formula = Formula::with('createdBy:id,first_name,second_name')->get();

            //build the response
            $code = 200;
            $status = 'success';
            $message = "Formula's dates";
            $dataContent = $formula->toArray();
            
        }catch (Exception $e) {

           //log what happend
           Log::channel('system_exceptions')->info('Exceptions:', [$e]);

           //Unknown error happened
           return $this->unknownErrorHappenedMsg();


        }

        return $this->returnApiResult($code, $status, $message, $dataContent);
    }

    /**
     * A function to update the formula's dates
     * @request:  
     * @response: success or Exception
     */
    public function updateFormula(Request $request){
        
        try{
            
            $user_id = 8; //Auth::user()->id;

            //Validate the request data
            $validator = Validator::make($request->all(), [
                'formula_start' => 'required|date',
                'formula_end' => 'required|date'
             ]);

 
            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }else{
 
                //Update the formula
                $formula = Formula::find(1);
                $formula->formula_start = $request->formula_start;
                $formula->formula_end = $request->formula_end;
                $formula->created_by = $user_id;
                $formula->save();

                //Reset the formula flag in the employee table
                Employee::query()->update(['formula_flag' => 0]);
                   
                //build the response
                $code = 200; //successful Request:
                $status = 'success';
                $message = "The data has been updated successfully";
                $dataContent = '';
            }
            
        }catch (Exception $e) {  

           //log what happend
           Log::channel('system_exceptions')->info('Exceptions:', [$e]);

           //Unknown error happened
           return $this->unknownErrorHappenedMsg();


        }

        return $this->returnApiResult($code, $status, $message, $dataContent);

    }
}
