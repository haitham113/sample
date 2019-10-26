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


class SaleController extends Controller
{
   
    /**
     * A function to get all oprations needs to approve/disapprove actions ftom temp data table
     *
     * @param  
     * @return
     */
    public function getAllTempRequests(){

        try{
            $tempRequests = TempData::select('id', 'flag', 'operationCode', 'operationDesc')->get();

            //build the response
            $code = 200;
            $status = 'success';
            $message = "All Temp Requests which need approve/disapprove action";
            $dataContent = $tempRequests->toArray();;
            
            return $this->returnApiResult($code, $status, $message, $dataContent);

        }catch (Exception $e) {  

            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
 
            //Unknown error happened
            return $this->unknownErrorHappenedMsg();
 
 
         }
    }


    /**
     * A function to  approve/disapprove an action
     * @param  
     * @return
     */
    public function approveDisapproveOperation(Request $request){

        try{
            $user_id = 8; //Auth::user()->id;

            //get the loged user data
            $user = User::find($user_id);
            if(!$user){ return $this->modelNotFoundResponse(); }

            //Validate the request data
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric',
                'satatus' => 'required|numeric', //1: approveed, 0:disapproved
            ]);

            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }else{

                $id = $request->id;
                $satatus = $request->satatus;

                if($satatus == 0){ //disapproved

                    //update the temp data row [flag -> disapproved]
                    TempData::where('id', $request->id)->update(['flag'=> 'disapproved']);

                    $code = 200; //successful Request:
                    $status = 'success';
                    $message = "The data have been processed succefully";
                    $dataContent = '';


                }elseif($satatus == 1){ //approveed

                    //get the temp data record
                    $tempData = TempData::find($id);
                    if(!$tempData){ return $this->modelNotFoundResponse(); }
                   
                    if($tempData->operationCode == 1){ //Employee sold a unit to outside broker

                        return $this->approveUnitSelling($tempData);
                        
                    }elseif($tempData->operationCode == 2){ //Primary

                        return $this->approveUnitSellingPrimaryORResaleExternally($tempData, $request);

                    }elseif($tempData->operationCode == 3){ //Resale: Externally

                        return $this->approveUnitSellingPrimaryORResaleExternally($tempData, $request);

                    }elseif($tempData->operationCode == 4){ //Resale: Inernally

                        return $this->approveUnitSellingResaleInernally($tempData, $request);

                    }else{ //unknown scenario 
                        abort(401);
                    }
                
                }else{ //unknown scenario 
                    abort(401);
                }
               

                return $this->returnApiResult($code, $status, $message, $dataContent);
            }

        }catch (Exception $e) {  

            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
 
            //Unknown error happened
            return $this->unknownErrorHappenedMsg();
 
 
        }
    }


    /**
     * A function to  approve/disapprove the selling of an unit to outside broker
     * @param  
     * @return
     */
    private function approveUnitSelling($tempData){
        
        try{

            //convert the stored json request tot an object
            $tmpRequestData = json_decode($tempData->data);

            //get the unit record
            $unit = Unit::find($tmpRequestData->unit_id);
            if(!$unit){ return $this->modelNotFoundResponse(); }

            //Make sure that the unit is offered for sale in the first place
            if($unit->unit_status != 1){ //The unit NOT for sale
                
                //build the response
                $code = 401;
                $status = 'error';
                $message = "Not permitted";
                $dataContent = "The unit [Code: $unit->unit_code] already sold";
                
                return $this->returnApiResult($code, $status, $message, $dataContent);
            }


            //Update the status of the unit to Sold with outside broker [value will be: 4]
            $unit->unit_status = 4; //Sold with outside broker [value will be: 4]
            $unit->sold_by = $tempData->created_by;
            $unit->sold_at = date('Y-m-d');
            $unit->sold_to = $tmpRequestData->broker_id;
            $unit->type_of_sale = 'Out side broker';
            $unit->save();

            //Add the commission of that unit to the employee who sold it 
            $employee = Employee::where('user_id', $tempData->created_by)->first();
            if(!$employee){ return $this->modelNotFoundResponse(); }
            $employee->commissions = ($employee->commissions + $unit->commission_value);
            $employee->save();

            //update the temp data row [flag -> approveed]
            TempData::where('id', $tempData->id)->update(['flag'=> 'approveed']); 

            //build the response
            $code = 200; //successful Request:
            $status = 'success';
            $message = "The data has been updated successfully";
            $dataContent = '';


            return $this->returnApiResult($code, $status, $message, $dataContent);


        }catch (Exception $e) {  

            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
 
            //Unknown error happened
            return $this->unknownErrorHappenedMsg();
 
 
        }
       
    }


     /**
     * A function to  approve/disapprove for Primary or Resale: Externally
     * @param  
     * @return
     */
    private function approveUnitSellingPrimaryORResaleExternally($tempData, $request){


        try{

            //convert the stored json request tot an object
            $tmpRequestData = json_decode($tempData->data);

            //get the unit record
            $unit = Unit::find($tmpRequestData->unit_id);
            if(!$unit){ return $this->modelNotFoundResponse(); }

            //Make sure that the unit is offered for sale in the first place
            if($unit->unit_status != 1){ //The unit NOT for sale
                
                //build the response
                $code = 401;
                $status = 'error';
                $message = "Not permitted";
                $dataContent = "The unit [Code: $unit->unit_code] already sold";
                
                return $this->returnApiResult($code, $status, $message, $dataContent);
            }

            
            //Update the status of the unit to Sold internally [value will be: 5]
            $unit->unit_status = 5; //Sold internally [value will be: 5]
            $unit->sold_by = $tempData->created_by;
            $unit->sold_at = date('Y-m-d');
            $unit->sold_to = $tmpRequestData->client_id;
            $unit->type_of_sale = 'Sold internally';
            $unit->save();

            //set the employee's commission
            if($request->has('new_commission')){ //if the admin/teamleader add new commission
                $commission = $request->new_commission;
            }else{//take the commission the employee requested
                $commission = $unit->commission_value;
            }

            //Add the commission of that unit to the employee who sold it 
            $employee = Employee::where('user_id', $tempData->created_by)->first();
            if(!$employee){ return $this->modelNotFoundResponse(); }
            $employee->commissions = ($employee->commissions + $commission);
            $employee->save();

            //update the temp data row [flag -> approveed]
            TempData::where('id', $tempData->id)->update(['flag'=> 'approveed']); 

            //build the response
            $code = 200; //successful Request:
            $status = 'success';
            $message = "The data has been updated successfully";
            $dataContent = '';


            return $this->returnApiResult($code, $status, $message, $dataContent);


        }catch (Exception $e) {  

            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
 
            //Unknown error happened
            return $this->unknownErrorHappenedMsg();
 
 
        }
    }

    

     /**
     * A function to  approve/disapprove Resale: Inernally
     * @param  
     * @return
     */
    private function approveUnitSellingResaleInernally($tempData, $request){

        try{

            //convert the stored json request tot an object
            $tmpRequestData = json_decode($tempData->data);

            //get the unit record
            $unit = Unit::find($tmpRequestData->unit_id);
            if(!$unit){ return $this->modelNotFoundResponse(); }

            //Make sure that the unit is offered for sale in the first place
            if($unit->unit_status != 1){ //The unit NOT for sale
                
                //build the response
                $code = 401;
                $status = 'error';
                $message = "Not permitted";
                $dataContent = "The unit [Code: $unit->unit_code] already sold";
                
                return $this->returnApiResult($code, $status, $message, $dataContent);
            }

            
            //Update the status of the unit to Sold internally [value will be: 5]
            $unit->unit_status = 5; //Sold internally [value will be: 5]
            $unit->sold_by = $tempData->created_by;
            $unit->sold_at = date('Y-m-d');
            $unit->sold_to = $tmpRequestData->client_id;
            $unit->type_of_sale = 'Sold internally';
            $unit->save();


            //Here we have two commissions
            //The first one to the emp who owned the unit and the second to the emp who owned the client

            //1- The commission of the emp who owned the unit 
            //Add the commission of that unit to the employee who sold it 
            $employee = Employee::where('user_id', $unit->created_by)->first();
            if(!$employee){ return $this->modelNotFoundResponse(); }
            $employee->commissions = ($employee->commissions + $unit->commission_value);
            $employee->save();
 

            //2- The commission of the emp who owned the client
            //set the employee's commission
            if($request->has('new_commission')){ //if the admin/teamleader add new commission
                $commission = $request->new_commission;
            }else{//take the commission the employee requested
                $commission = $tmpRequestData->commission;
            }
            //Add the commission of that unit to the employee who sold it 
            $employee = Employee::where('user_id', $tempData->created_by)->first();
            if(!$employee){ return $this->modelNotFoundResponse(); }
            $employee->commissions = ($employee->commissions + $commission);
            $employee->save();

            //update the temp data row [flag -> approveed]
            TempData::where('id', $tempData->id)->update(['flag'=> 'approveed']); 

            //build the response
            $code = 200; //successful Request:
            $status = 'success';
            $message = "The data has been updated successfully";
            $dataContent = '';


            return $this->returnApiResult($code, $status, $message, $dataContent);


        }catch (Exception $e) {  

            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
 
            //Unknown error happened
            return $this->unknownErrorHappenedMsg();
 
 
        }
        
    }
    


     /*******************************************************
     * 
     * sale the unit and update the unit status [Unit point of view]
     * 
     ********************************************************/
    /**
     * A function to update the status of the unit and/or sale it
     *
     * @param  
     * @return
     */
    public function saleUpdateUnitStatus(Request $request){

        try{  
            //get logged user
            $user_id = 8; //Auth::user()->id;
            
            //get the loged user data
            $user = User::find($user_id);
            if(!$user){ return $this->modelNotFoundResponse(); }


            //Validate the request data
            $validator = Validator::make($request->all(), [
                'unit_id' => 'required|numeric',
                'status' => 'required|numeric', //A number from one to five
            ]);

 
            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }else{
                $unit_status = $request->status;
                $unit_id = $request->unit_id;
  
                /**
                 * unit_status values and descriptions:
                 * a- For sale [value will be: 1]
                 * b- Sold unknown [value will be: 2]
                 * c- Not for sale sale now [value will be: 3]
                 * d- Sold with outside broker [value will be: 4]
                 */
                if($unit_status == 1 || $unit_status == 2 || $unit_status == 3){
                    //Just update the unit's status
                    Unit::where('id', $unit_id)->update(['unit_status'=> $unit_status]);

                    //build the response
                    $code = 200; //successful Request:
                    $status = 'success';
                    $message = "The data has been updated successfully";
                    $dataContent = '';

                }elseif($unit_status == 4){ //Sold with outside broker

                    //Make sure that the request contains the broker id
                    $validator = Validator::make($request->all(), [
                        'broker_id' => 'required|numeric'
                    ]);

                    if ($validator->fails()) { //some of request data are missing or invalid
                        //return the validation errors
                        return $this->validationErrorsResponse($validator->errors());
                    }
                     
                    //add the request to the temp data table until the admin/team leader approve/disapprove it
                    //1- get the unit details
                    $unit = Unit::find($unit_id);
                    if(!$unit){ return $this->modelNotFoundResponse(); }
                    //2- build the temp request description
                    $desc = "$user->first_name sold a unit [Code: $unit->unit_code] to outside broker";
                    //save the request
                    $result = $this->addToTempDataTable($request->all(), $desc, 1);
                    
                    if($result == true){ //successful operation
                    
                        //build the response
                        $code = 200; //successful Request:
                        $status = 'success';
                        $message = "The data has been updated successfully";
                        $dataContent = '';
    
                    }else{ //Unknown error happened
                        abort(401);
                    }

                }else{
                    abort(401);
                }

                return $this->returnApiResult($code, $status, $message, $dataContent);
            }
          
        }catch (Exception $e) {  

            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
 
            //Unknown error happened
            return $this->unknownErrorHappenedMsg();
              
        }
    }




     /*******************************************************
     * 
     * sale the unit [Client point of view]
     * 
     ********************************************************/
     /**
     * A function to sale a unit to a client [primary]
     * Desc: primary means that the unit is owned by the develpoer and the company only has the client
     *       There will be one commission for the employee who owned the client [will be specified by the employee and the team leader or the admin will approve it or not] 
     * @param  
     * @return
     */
     public function primary(Request $request){

        try{

            //get logged user
            $user_id = 8; //Auth::user()->id;
        
            //get the loged user data
            $user = User::find($user_id);
            if(!$user){ return $this->modelNotFoundResponse(); }

            //Validate the request data
            $validator = Validator::make($request->all(), [
                'client_id'=> 'required|numeric',
                'compound_id'=> 'required|numeric',
                'original_price'=> 'required', //equivalent to original_price
                'unit_type'=> 'required', //apartment, stand alone, townhouse, twin villa, other
                'building_area'=> 'required', //the size of the unit
                'owner_name'=> 'required', //developer name
                'owner_phone'=> 'required', //developer phone
                'commission'=> 'required', //the commission of the employee
                'activity_type' => 'required',
                'activity_status' => 'required',
                'feedback' => 'required',
                'activity_date' => 'required|date_format:Y-m-d'
                 
            ]);

 
            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }else{

                $owner_name = $request->owner_name;
                $owner_phone = $request->owner_phone;
                $commission = $request->commission;

                //create the unit for outside develpoer
                $unit = $this->createOutsideUnite($request->only([
                    'compound_id', 'original_price', 'unit_type', 'building_area', 
                    'owner_name', 'owner_phone', 'commission'
                ]));

                //add the request to the temp data table until the admin/team leader approve/disapprove it
                $desc = "$user->first_name sold to client a unit [Code: $unit->unit_code] owned by outside developer [Name: $owner_name and Phone: $owner_phone], with commssion: $commission";
                //add the created unit id to the request then save the request
                $result = $this->addToTempDataTable($request->all() + ['unit_id' => $unit->id], $desc, 2);
                
                if($result == true){ //successful operation
                
                    //build the response
                    $code = 200; //successful Request:
                    $status = 'success';
                    $message = "The data has been updated successfully";
                    $dataContent = '';

                }else{ //Unknown error happened
                    abort(401);
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
     * A function to sale a unit to a client [Resale: Externally]
     * Desc: Same as the primary but the unit's owner is a broker [outside broker] not a developer
     * @param  
     * @return
     */
    public function resaleExternally(Request $request){

        try{

            //get logged user
            $user_id = 8; //Auth::user()->id;
        
            //get the loged user data
            $user = User::find($user_id);
            if(!$user){ return $this->modelNotFoundResponse(); }

            //Validate the request data
            $validator = Validator::make($request->all(), [
                'client_id'=> 'required|numeric',
                'compound_id'=> 'required|numeric',
                'original_price'=> 'required', //equivalent to original_price
                'unit_type'=> 'required', //apartment, stand alone, townhouse, twin villa, other
                'building_area'=> 'required', //the size of the unit
                'owner_name'=> 'required', //broker name
                'owner_phone'=> 'required', //broker phone
                'commission'=> 'required', //the commission of the employee
                'activity_type' => 'required',
                'activity_status' => 'required',
                'feedback' => 'required',
                'activity_date' => 'required|date_format:Y-m-d'
                 
            ]);

 
            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }else{

                $owner_name = $request->owner_name;
                $owner_phone = $request->owner_phone;
                $commission = $request->commission;

                //create the unit for outside broker
                $unit = $this->createOutsideUnite($request->only([
                    'compound_id', 'original_price', 'unit_type', 'building_area', 
                    'owner_name', 'owner_phone', 'commission'
                ]));

                //add the request to the temp data table until the admin/team leader approve/disapprove it
                $desc = "$user->first_name sold to client a unit [Code: $unit->unit_code] owned by outside broker [Name: $owner_name and Phone: $owner_phone], with commssion: $commission";
                //save the request
                $result = $this->addToTempDataTable($request->all() + ['unit_id' => $unit->id], $desc, 3);
                
                if($result == true){ //successful operation
                
                    //build the response
                    $code = 200; //successful Request:
                    $status = 'success';
                    $message = "The data has been added successfully";
                    $dataContent = '';

                }else{ //Unknown error happened
                    abort(401);
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
     * A function to sale a unit to a client [Resale: Internally]
     * Desc: Internally Resale means that the unit and the client owned by the company
     *       There will be two commissions one for the employee who owned the client 
     *       and another one for the employee who owned the unit
     * @param  
     * @return
     */
    public function resaleInternally(Request $request){

        try{

            //get logged user
            $user_id = 8; //Auth::user()->id;
        
            //get the loged user data
            $user = User::find($user_id);
            if(!$user){ return $this->modelNotFoundResponse(); }

            //Validate the request data
            $validator = Validator::make($request->all(), [
                'client_id'=> 'required|numeric',
                'unit_id'=> 'required|numeric',
                'commission'=> 'required', //the commission of the employee
                'activity_type' => 'required',
                'activity_status' => 'required',
                'feedback' => 'required',
                'activity_date' => 'required|date_format:Y-m-d'
                 
            ]);

 
            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }else{

                $unit_id = $request->unit_id;
                $commission = $request->commission;
             

                //get the unit data
                $unit = Unit::find($unit_id);
                if(!$unit){ return $this->modelNotFoundResponse(); }


                //add the request to the temp data table until the admin/team leader approve/disapprove it
                $desc = "$user->first_name sold to client a unit [Code: $unit->unit_code] owned by internal broker, with commssion: $commission";
                //save the request
                $result = $this->addToTempDataTable($request->all(), $desc, 4);
                
                if($result == true){ //successful operation
                
                    //build the response
                    $code = 200; //successful Request:
                    $status = 'success';
                    $message = "The data has been added successfully";
                    $dataContent = '';

                }else{ //Unknown error happened
                    abort(401);
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
     * A function to create a unit for outside broker/developer 
     * @param  
     * @return
     */
    private function createOutsideUnite($request){
        
        try{ 

            //get logged user
            $user_id = 8; //Auth::user()->id;
     
            //store new unit
            $unit = new Unit;

            $unit->compound_id = $request['compound_id'];
            $unit->original_price = $request['original_price'];
            $unit->final_price = $request['original_price'];
            $unit->unit_type = $request['unit_type'];
            $unit->building_area = $request['building_area'];
            $unit->owner_name = $request['owner_name'];
            $unit->owner_phone = $request['owner_phone'];
            $unit->commission_value = $request['commission'];
            $unit->created_by = $user_id;
            $unit->broker_type = 'External';
            $unit->unit_status = 1;
            $unit->unit_code = 'U' . date('ymd') .'_'. rand(9999, 99999);

            $unit->save();

            return $unit;

        }catch (Exception $e) {  

            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);

            //Unknown error happened
            return $this->unknownErrorHappenedMsg();


        }
    }
}
