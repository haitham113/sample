<?php

namespace App\Http\Controllers;

use App\Area;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;

use Auth;
use Log;
use Exception;

class AreaController extends Controller
{

 
    function __construct(){
       
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){ 

        
        try{
            //Get all Areas
            $area = Area::with('createdBy:id,first_name,second_name')->get();

            //build the response
            $code = 200;
            $status = 'success';
            $message = "All Area";
            $dataContent = $area->toArray();
            
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
                'name' => 'required',
                'address' => 'required',
            ]);

            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }else{//successful validation
            
                //add to the request the logged user id then create the record
                $result = Area::create($request->except(['created_by']) + ['created_by' => $user_id]);

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
     * @param  \App\Area  $area
     * @return \Illuminate\Http\Response
     */
    public function show(Area $area){
        
        return $this->pageNotFoundResponse();
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
            $area = Area::find($id);

            //could not find the request row
            if(!$area){
                return $this->modelNotFoundResponse();
            }

            //build the response
            $code = 200; //successful Request:
            $status = 'success';
            $message = "The data has been fetched successfully";
            $dataContent = $area;

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
                'address' => 'required',
            ]);


            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }else{//successful validation
            
                //Find the model
                $area = Area::find($id);

                //could not find the request row
                if(!$area){
                    return $this->modelNotFoundResponse();
                }
                
                //add to the request the logged user id then create the record
                $result = $area->update($request->except(['created_by']) + ['created_by' => $user_id]);

                
                if($result){ //successful operation
                    
                    //build the response
                    $code = 200; //successful Request:
                    $status = 'success';
                    $message = "The data has been updated successfully";
                    $dataContent = ['row_id'=> $area->id];

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
     * @param  \App\Area  $area
     * @return \Illuminate\Http\Response
     */
    public function destroy($id){

        return $this->pageNotFoundResponse();
    }
}
