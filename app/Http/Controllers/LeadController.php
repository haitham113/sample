<?php

namespace App\Http\Controllers;

use App\Lead;
use App\User;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;

use Auth;
use Log;
use Exception;
use Excel;


class LeadController extends Controller
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
         
            if ($user->hasPermission('root_admin') || $user->hasPermission('team_leader')){
                //Get all leads
                $leads = Lead::with('createdBy:id,first_name,second_name')
                                ->with('assignedTo:id,first_name,second_name')
                                ->with('reassignedTo:id,first_name,second_name')
                                ->with('reassignedBy:id,first_name,second_name')
                                ->get();
            }else{
                //Get all leads for the logged user
                $leads = Lead::with('createdBy:id,first_name,second_name')
                                ->with('assignedTo:id,first_name,second_name')
                                ->with('reassignedTo:id,first_name,second_name')
                                ->with('reassignedBy:id,first_name,second_name')
                                ->where('reassigned_to', $user_id)
                                ->get();
            }

            //build the response
            $code = 200;
            $status = 'success';
            $message = "All Leads";
            $dataContent = $leads->toArray();
            
        }catch (Exception $e) {

           //log what happend
           Log::channel('system_exceptions')->info('Exceptions:', [$e]);

           //Unknown error happened
           return $this->unknownErrorHappenedMsg();


        }

        return $this->returnApiResult($code, $status, $message, $dataContent);
    }

    /**
     * a function to get all leads where the lead_type field equals [lead or data]
     * @param: the lead required type
     * @return: the leads
     */
    public function getAllLeadsWithType($type){
         
        try{

            //get the logged user
            $user_id = 1; //Auth::user()->id;
            $user = User::find($user_id);
        
            if ($user->hasPermission('root_admin') || $user->hasPermission('team_leader')){
                //Get all leads
                $leads = Lead::with('createdBy:id,first_name,second_name')
                                ->with('assignedTo:id,first_name,second_name')
                                ->with('reassignedTo:id,first_name,second_name')
                                ->with('reassignedBy:id,first_name,second_name')
                                ->where('lead_type', $type)
                                ->get();
            }else{
                //Get all leads for the logged user
                $leads = Lead::with('createdBy:id,first_name,second_name')
                                ->with('assignedTo:id,first_name,second_name')
                                ->with('reassignedTo:id,first_name,second_name')
                                ->with('reassignedBy:id,first_name,second_name')
                                ->where('reassigned_to', $user_id)
                                ->where('lead_type', $type)      
                                ->get();
            }

            //build the response
            $code = 200;
            $status = 'success';
            $message = "All Leads";
            $dataContent = $leads->toArray();
            
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
            $user = User::find($user_id);

            //Validate the request data
            $validator = Validator::make($request->all(), [
                'first_name' => 'required',
                'second_name' => 'required',
                'project_name' => 'required',
                'country_code' => 'required',
                'lead_phone' => 'required',
            ]);
 
            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }else{//successful validation
            
                //determine if the lead_type is data or lead
                if($user->hasPermission('team_leader')){
                    $lead_type = 'data';
                }else{
                    $lead_type = 'lead';
                }
                //add to the request the logged user id then create the record
                $except_fields = [
                    'lead_status',
                    'assigned_to',
                    'reassigned_to',
                    'reassigned_by',
                    'created_by',
                ];
                $extra_feilds = [
                    'lead_status' => 'New',
                    'assigned_to' => $user_id,
                    'reassigned_to' => $user_id,
                    'reassigned_by' => $user_id,
                    'created_by' => $user_id,
                    'lead_type' => $lead_type,
                ];
                $result = Lead::create($request->except($except_fields) + $extra_feilds);

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
     * @param  \App\Lead  $lead
     * @return \Illuminate\Http\Response
     */
    public function show($id){

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
                $lead = Lead::with('createdBy:id,first_name,second_name')
                                ->with('assignedTo:id,first_name,second_name')
                                ->with('reassignedTo:id,first_name,second_name')
                                ->with('reassignedBy:id,first_name,second_name')
                                ->find($id);

            }else{

                //Find the model
                $lead = Lead::with('createdBy:id,first_name,second_name')
                                ->with('assignedTo:id,first_name,second_name')
                                ->with('reassignedTo:id,first_name,second_name')
                                ->with('reassignedBy:id,first_name,second_name')
                                ->where('reassigned_to', $user_id)
                                ->find($id);

            }

           //could not find the request row
           if(!$lead){
               return $this->modelNotFoundResponse();
           }

           //build the response
           $code = 200; //successful Request:
           $status = 'success';
           $message = "The data has been fetched successfully";
           $dataContent = $lead;

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
     * @param  \App\Lead  $lead
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
                $lead = Lead::with('createdBy:id,first_name,second_name')
                                ->with('assignedTo:id,first_name,second_name')
                                ->with('reassignedTo:id,first_name,second_name')
                                ->with('reassignedBy:id,first_name,second_name')
                                ->find($id);

            }else{

                //Find the model
                $lead = Lead::with('createdBy:id,first_name,second_name')
                                ->with('assignedTo:id,first_name,second_name')
                                ->with('reassignedTo:id,first_name,second_name')
                                ->with('reassignedBy:id,first_name,second_name')
                                ->where('reassigned_to', $user_id)
                                ->find($id);

            }
           

            //could not find the request row
            if(!$lead){
                return $this->modelNotFoundResponse();
            }

            //build the response
            $code = 200; //successful Request:
            $status = 'success';
            $message = "The data has been fetched successfully";
            $dataContent = $lead;

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
     * @param  \App\Lead  $lead
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){
        
        try{
            
            $user_id = 1; //Auth::user()->id;
            $user = User::find($user_id);

            //Validate the request data
            $validator = Validator::make($request->all(), [
                'first_name' => 'required',
                'second_name' => 'required',
                'project_name' => 'required',
                'country_code' => 'required',
                'lead_phone' => 'required',
            ]);


            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }else{//successful validation
            
                //get the logged user
                $user = User::find($user_id);

                //could not find the request row
                if(!$user){
                    return $this->modelNotFoundResponse();
                }

                if ($user->hasPermission('root_admin') || $user->hasPermission('team_leader')){

                    //Find the model
                    $lead = Lead::find($id);

                }else{

                    //Find the model
                    $lead = Lead::where('reassigned_to', $user_id)
                                    ->find($id);

                }

                //could not find the requested row
                if(!$lead){
                    return $this->modelNotFoundResponse();
                }
                
                //add to the request the logged user id then create the record
                $except_fields = [
                    'lead_status',
                    'assigned_to',
                    'reassigned_by',
                    'created_by',
                ];
                $extra_feilds = [
                ]; 
                $result = $lead->update($request->except($except_fields) + $extra_feilds);

                
                if($result){ //successful operation
                    
                    //build the response
                    $code = 200; //successful Request:
                    $status = 'success';
                    $message = "The data has been updated successfully";
                    $dataContent = ['row_id'=> $lead->id];

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
     * @param  \App\Lead  $lead
     * @return \Illuminate\Http\Response
     */
    public function destroy($id){

        try{

            Lead::destroy($id);

            //build the response
            $code = 200; //successful Request:
            $status = 'success';
            $message = "The data have been deleted successfully";
            $dataContent = '';

            return $this->returnApiResult($code, $status, $message, $dataContent);


        }catch(Exception $e){

            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);

            //Unknown error happened
            return $this->unknownErrorHappenedMsg();

        }
        
    }

    public function reassignLead(Request $request, $id){
        try{
            
            $user_id = 1; //Auth::user()->id;
            $user = User::find($user_id);

            //Validate the request data
            $validator = Validator::make($request->all(), [
                'reassigned_to' => 'required|numeric'
            ]);


            if ($validator->fails()) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());

            }else{//successful validation
            

            $lead = Lead::find($id);

            //could not find the requested row
            if(!$lead){
                return $this->modelNotFoundResponse();
            }

            //add to the request the logged user id then create the record
            $extra_feilds = [
                'reassigned_by' => $user_id
            ];
            
            $result = $lead->update($request->only('reassigned_to') + $extra_feilds);


            if($result){ //successful operation
                
                //build the response
                $code = 200; //successful Request:
                $status = 'success';
                $message = "The data has been updated successfully";
                $dataContent = ['row_id'=> $lead->id];

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

    public function getDuplicates()
    {

        try{

            $conflicts = \DB::select("SELECT lead_phone, COUNT('lead_phone') as numofdupphones FROM leads GROUP BY lead_phone HAVING COUNT('lead_phone') > 1");


            //build the response
            $code = 200; //successful Request:
            $status = 'success';
            $message = "The duplicate in the phones of the leads";
            $dataContent = $conflicts;

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
     * @param  \App\Lead  $lead
     * @return \Illuminate\Http\Response
     */
    public function getLeadsByPhone($phone){

        try{

            //get the logged user
            $user_id = 1; //Auth::user()->id;
            $user = User::find($user_id);

            //could not find the request row
            if(!$user){
                return $this->modelNotFoundResponse();
            }

            //Find the model
            $leads = Lead::with('createdBy:id,first_name,second_name')
                            ->with('assignedTo:id,first_name,second_name')
                            ->with('reassignedTo:id,first_name,second_name')
                            ->with('reassignedBy:id,first_name,second_name')
                            ->where('lead_phone', $phone)
                            ->get();

           //could not find the request row
           if(!$leads){
               return $this->modelNotFoundResponse();
           }

           //build the response
           $code = 200; //successful Request:
           $status = 'success';
           $message = "The data has been fetched successfully";
           $dataContent = $leads;

           return $this->returnApiResult($code, $status, $message, $dataContent);

        }catch(Exception $e){
           
           //log what happend
           Log::channel('system_exceptions')->info('Exceptions:', [$e]);

           //Unknown error happened
           return $this->unknownErrorHappenedMsg();
        }
    }


  
     /**
     * A function to import leads using Excel sheet
     *
     * @param  
     * @return  
     */
    public function importLeads(Request $request){
        
        try{

            $user_id = 1; //Auth::user()->id;
            $user = User::find($user_id);

            //Validate the request data
            $validator = Validator::make($request->all(), [
                'file' => 'required|mimes:xlsx',
            ]);

            if ($validator->fails()) { //some of request data are missing or invalid
                //return the validation errors
                return $this->validationErrorsResponse($validator->errors());
            }

            //get the leads file
            $path = $_FILES['file']['tmp_name'];
            //prep. the date
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path); 
            $leads = $spreadsheet->getActiveSheet()->toArray(null,true,true,true);
    
            //Make sure the file contains first name, second name, porject name, and lead phone in the header of the file with the same order
            //1- If the file does not contains [A, B, C, D, or E] put empty string as a default value
            //   ignore whitespace and convert to the letters strtolower
            $first_name = strtolower(preg_replace('/\s*/',"", isset($leads[1]['A'])?$leads[1]['A']:''));
            $second_name = strtolower(preg_replace('/\s*/',"", isset($leads[1]['B'])?$leads[1]['B']:''));
            $project_name = strtolower(preg_replace('/\s*/',"", isset($leads[1]['C'])?$leads[1]['C']:''));
            $country_code = strtolower(preg_replace('/\s*/',"",  isset($leads[1]['D'])?$leads[1]['D']:''));
            $lead_phone = strtolower(preg_replace('/\s*/',"",  isset($leads[1]['E'])?$leads[1]['E']:''));
 
            //2- must be first name, second name, porject name, country .code, and lead phone in the header of the file with the same order
            if($first_name != 'firstname' || 
                $second_name != 'secondname' || 
                $project_name != 'porjectname' || 
                $country_code != 'countrycode' ||
                $lead_phone != 'leadphone')
            {
                //build the response
                $code = 401; 
                $status = 'error';
                $message = "Wrong headersm it must be first name, second name, porject name, country .code, and lead phone in the header of the file with the same order";
                $dataContent = '';

                return $this->returnApiResult($code, $status, $message, $dataContent);
            }

            //determine if the lead_type is data or lead
            if($user->hasPermission('team_leader')){
                $lead_type = 'data';
            }else{
                $lead_type = 'lead';
            }
            
            $firstHeaderRow = true;

            foreach($leads as $lead){
             
                //To ignore the header row 
                if($firstHeaderRow == true){
                    $firstHeaderRow = false;
                    continue;
                }
                    
                
                //Store the lead
                $newLead = new Lead;
                $newLead->first_name = $lead['A'];
                $newLead->second_name = $lead['B'];
                $newLead->project_name = $lead['C'];
                $newLead->country_code = $lead['D'];
                $newLead->lead_phone = $lead['E'];
                $newLead->lead_status = 'New';
                $newLead->lead_type = $lead_type;
                $newLead->assigned_to = $user_id;
                $newLead->reassigned_to = $user_id;
                $newLead->reassigned_by = $user_id;
                $newLead->created_by = $user_id;
                $newLead->save();
                
            }

            //build the response
            $code = 200; //successful Request:
            $status = 'success';
            $message = "The data has been added successfully";
            $dataContent = '';

            return $this->returnApiResult($code, $status, $message, $dataContent);

        }catch(Exception $e){
           
           //log what happend
           Log::channel('system_exceptions')->info('Exceptions:', [$e]);

           //Unknown error happened
           return $this->unknownErrorHappenedMsg();
        }
              
    }

}
