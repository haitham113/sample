<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\TempData;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     *	return APi request result
     *	if failed or successed
     */
	protected function returnApiResult($code, $status, $message, $dataContent){
       
        $response['meta'] = [
	        'code' => $code,
	        'status' => $status,
	        'message' => $message,
	    ];
        $response['data'] = $dataContent;
	    
	    return response()->json($response, $code, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * A function to return a JSON response in case of User not found error
     */
    protected function userNotFoundResponse(){
        
        //build the response
        $code = 404;
        $status = 'error';
        $message = 'User not found';
        $dataContent = "";

        return $this->returnApiResult($code, $status, $message, $dataContent);
    }

    /**
     * A function to return a JSON response in case of Page not found error
     */
    protected function pageNotFoundResponse(){
        
        //build the response
        $code = 404;
        $status = 'error';
        $message = 'Page not found';
        $dataContent = "";

        return $this->returnApiResult($code, $status, $message, $dataContent);
    }


    /**
     * A function to return a JSON response in case of Model not found error
     */
    protected function modelNotFoundResponse(){
        
        //build the response
        $code = 404;
        $status = 'error';
        $message = 'The model you trying to fetch not found';
        $dataContent = "";

        return $this->returnApiResult($code, $status, $message, $dataContent);
    }

    /**
     * A function to return a JSON response in case of unknown error message happend
     */
    protected function unknownErrorHappenedMsg(){

        $code = 503; //Service Unavailable
        $status = 'errors';
        $message = "Unknown error happened, please try again later";
        $dataContent = '';
        
        return $this->returnApiResult($code, $status, $message, $dataContent);
    }

    /**
     * A function to return a JSON response in case of Model not found error
     */
    protected function validationErrorsResponse($validation_errors){
        
        //build the response
        $code = 400; //Bad Request: missing or invalid data
        $status = 'errors';
        $message = "Validation Errors";
        $dataContent = $validation_errors;

        return $this->returnApiResult($code, $status, $message, $dataContent);
    }

    /**
     * A function add a request which need approval to the temp data table
     *
     * @param  
     * @return
     */
    protected function addToTempDataTable($request, $operationDesc, $operationCode){
        
        //get logged user
        $user_id = 8; //Auth::user()->id;

        $tempData = new TempData;
        $tempData->data = json_encode($request);
        $tempData->flag = 'new';
        $tempData->operationDesc = $operationDesc;
        $tempData->operationCode = $operationCode;
        $tempData->created_by = $user_id;

        if($tempData->save()){
            return true;
        }else{
            return false;
        }
    }
}
