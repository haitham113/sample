<?php

namespace App\Http\Controllers;

use App\Compound;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;

use Auth;
use Log;
use Exception;


class CompoundController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){ 

        
        try{
            //Get all Compounds
            $compounds = Compound::with('createdBy:id,first_name,second_name')
                                    ->with('area:id,name,address')
                                    ->get();

            //build the response
            $code = 200;
            $status = 'success';
            $message = "All Compounds";
            $dataContent = $compounds->toArray();
            
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
                'area_id' => 'required|numeric',
                'name' => 'required',
                'address' => 'required',
            ]);

            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }else{//successful validation
            
                //add to the request the logged user id then create the record
                $result = Compound::create($request->except(['created_by']) + ['created_by' => $user_id]);

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
     * @param  \App\Compound  $compound
     * @return \Illuminate\Http\Response
     */
    public function show($id){
        return $this->pageNotFoundResponse();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Compound  $compound
     * @return \Illuminate\Http\Response
     */
    public function edit($id){

        try{

           //Find the model
           $compound = Compound::with('createdBy:id,first_name,second_name')
                                    ->with('area:id,name,address')
                                    ->find($id);

           //could not find the request row
           if(!$compound){
               return $this->modelNotFoundResponse();
           }

           //build the response
           $code = 200; //successful Request:
           $status = 'success';
           $message = "The data has been fetched successfully";
           $dataContent = $compound;

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
     * @param  \App\Compound  $compound
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){

        try{
            
            $user_id = 1; //Auth::user()->id;

            //Validate the request data
            $validator = Validator::make($request->all(), [
                'area_id' => 'required|numeric',
                'name' => 'required',
                'address' => 'required',
            ]);


            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }else{//successful validation
            
                //Find the model
                $compound = Compound::find($id);

                //could not find the request row
                if(!$compound){
                    return $this->modelNotFoundResponse();
                }
                
                //add to the request the logged user id then create the record
                $result = $compound->update($request->except(['created_by']) + ['created_by' => $user_id]);

                
                if($result){ //successful operation
                    
                    //build the response
                    $code = 200; //successful Request:
                    $status = 'success';
                    $message = "The data has been updated successfully";
                    $dataContent = ['row_id'=> $compound->id];

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
     * @param  \App\Compound  $compound
     * @return \Illuminate\Http\Response
     */
    public function destroy($id){
        return $this->pageNotFoundResponse();
    }
}
