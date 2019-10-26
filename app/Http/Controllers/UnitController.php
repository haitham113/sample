<?php

namespace App\Http\Controllers;

use App\Unit;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;

use Auth;
use Log;
use Exception;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){ 

        try{
            //Get all units
            $units = Unit::with('createdBy:id,first_name,second_name')
                            ->with('compound:id,name,address')
                            ->with('compound.area')
                            ->get();

            //build the response
            $code = 200;
            $status = 'success';
            $message = "All Units";
            $dataContent = $units->toArray();
            
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
                'compound_id' => 'required|numeric',
                //'unit_type' => 'required',
                //'broker_type' => 'required', //external or internal broker
                //'unit_num' => 'required',
                //'land_area' => 'required',
               // 'building_area' => 'required',
                //'garden_area' => 'required',
                'offering_type' => 'required',
                //'owner_name' => 'required',
               // 'owner_phone' => 'required',
                //'bedrooms' => 'required|numeric',
                //'bathrooms' => 'required|numeric',
                //'floor_num' => 'required|numeric',
                //'unit_view' => 'required',
                //'unit_desc' => 'required',
                //'original_price' => 'required',
                //'market_price' => 'required',
                //'owner_price' => 'required|numeric',
                //'over_price' => 'required|numeric',
                //'commission_percentage' => 'required',
                //'commission_value' => 'required',
                //'final_price' => 'required|numeric',
                //'original_downpayment' => 'required|numeric',
                //'final_downpayment' => 'required|numeric',
            ]);

            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }else{//successful validation

                //generate the unit code
                $unit_code = 'U' . date('ymd') .'_'. rand(9999, 99999);
   
                //add to the request the logged user id then create the record
                $result = Unit::create($request->except(['created_by']) + ['created_by' => $user_id, 'unit_code' => $unit_code]);

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

        }catch (Exception $e) { dd($e);

           //log what happend
           Log::channel('system_exceptions')->info('Exceptions:', [$e]);

           //Unknown error happened
           return $this->unknownErrorHappenedMsg();
             
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Unit  $unit
     * @return \Illuminate\Http\Response
     */
    public function show($id){

        try{

           //Find the model
           $unit = Unit::with('createdBy:id,first_name,second_name')
                            ->with('compound:id,name,address')
                            ->find($id);

           //could not find the request row
           if(!$unit){
               return $this->modelNotFoundResponse();
           }

           //build the response
           $code = 200; //successful Request:
           $status = 'success';
           $message = "The data has been fetched successfully";
           $dataContent = $unit;

           return $this->returnApiResult($code, $status, $message, $dataContent);

        }catch(Exception $e){
           
           //log what happend
           Log::channel('system_exceptions')->info('Exceptions:', [$e]);

           //Unknown error happened
           return $this->unknownErrorHappenedMsg();
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Unit  $unit
     * @return \Illuminate\Http\Response
     */
    public function edit($id){

        try{

           //Find the model
           $unit = Unit::with('createdBy:id,first_name,second_name')
                            ->with('compound:id,name,address')
                            ->find($id);

           //could not find the request row
           if(!$unit){
               return $this->modelNotFoundResponse();
           }

           //build the response
           $code = 200; //successful Request:
           $status = 'success';
           $message = "The data has been fetched successfully";
           $dataContent = $unit;

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
     * @param  \App\Unit  $unit
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){

        try{
            
            $user_id = 1; //Auth::user()->id;

            //Validate the request data
            $validator = Validator::make($request->all(), [
                'compound_id' => 'required|numeric',
                //'unit_type' => 'required',
                //'broker_type' => 'required', //external or internal broker
                //'unit_num' => 'required',
                //'land_area' => 'required',
               // 'building_area' => 'required',
                //'garden_area' => 'required',
                'offering_type' => 'required',
                //'owner_name' => 'required',
               // 'owner_phone' => 'required',
                //'bedrooms' => 'required|numeric',
                //'bathrooms' => 'required|numeric',
                //'floor_num' => 'required|numeric',
                //'unit_view' => 'required',
                //'unit_desc' => 'required',
                //'original_price' => 'required',
                //'market_price' => 'required',
                //'owner_price' => 'required|numeric',
                //'over_price' => 'required|numeric',
                //'commission_percentage' => 'required',
                //'commission_value' => 'required',
                //'final_price' => 'required|numeric',
                //'original_downpayment' => 'required|numeric',
                //'final_downpayment' => 'required|numeric',
            ]);


            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }else{//successful validation
            
                //Find the model
                $unit = Unit::find($id);

                //could not find the request row
                if(!$unit){
                    return $this->modelNotFoundResponse();
                }
                
                //add to the request the logged user id then create the record
                $result = $unit->update($request->except(['created_by']) + ['created_by' => $user_id]);

                
                if($result){ //successful operation
                    
                    //build the response
                    $code = 200; //successful Request:
                    $status = 'success';
                    $message = "The data has been updated successfully";
                    $dataContent = ['row_id'=> $unit->id];

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
     * @param  \App\Unit  $unit
     * @return \Illuminate\Http\Response
     */
    public function destroy(Unit $unit)
    {
        //
    }
}
