<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Position;
use Auth;

use Illuminate\Support\Facades\Validator;

use Log;
use Exception;

class PositionController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
         
        try{
            //Get all Employees
            $positions = Position::get();
                                
            //build the response
            $code = 200;
            $status = 'success';
            $message = "All Positions";
            $dataContent = $positions->toArray();
            
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
    public function store(Request $request){

        try{

            $user_id = 1; //Auth::user()->id;
            
            //Validate the request data
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'target_commission_monthly' => 'required',
                'target_commission_quarterly' => 'required',
                'target_commission_yearly' => 'required',
                'target_sales_monthly' => 'required',
                'target_sales_quarterly' => 'required',
                'target_sales_yearly' => 'required',
             
             ]);


            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }else{//successful validation

               
                $result = Position::create($request->all());

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
     * Show the form for editing the specified resource.
     *
     * @param  \App\Area  $area
     * @return \Illuminate\Http\Response
     */
    public function edit($id){

        try{

           //Find the model
           $position = Position::find($id);

           //could not find the request row
           if(!$position){
               return $this->modelNotFoundResponse();
           }

           //build the response
           $code = 200; //successful Request:
           $status = 'success';
           $message = "The data has been fetched successfully";
           $dataContent = $position;

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
     * @param  \App\Area  $area
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){

        try{
            
            $user_id = 1; //Auth::user()->id;

            //Validate the request data
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'target_commission_monthly' => 'required',
                'target_commission_quarterly' => 'required',
                'target_commission_yearly' => 'required',
                'target_sales_monthly' => 'required',
                'target_sales_quarterly' => 'required',
                'target_sales_yearly' => 'required',
            ]);


            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }else{//successful validation
            
                //Find the model
                $position = Position::find($id);

                //could not find the request row
                if(!$position){
                    return $this->modelNotFoundResponse();
                }
                
                //add to the request the logged user id then create the record
                $result = $position->update($request->all());

                
                if($result){ //successful operation
                    
                    //build the response
                    $code = 200; //successful Request:
                    $status = 'success';
                    $message = "The data has been updated successfully";
                    $dataContent = ['row_id'=> $position->id];

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
