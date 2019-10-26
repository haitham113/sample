<?php

namespace App\Http\Controllers;

use App\Lead;
use App\User;
use App\Unit;

use Illuminate\Http\Request;

use Log;
use Exception;


class APIGeneralController extends Controller
{
    public function getStatistics(Request $request){
        
        try{
          
            if (!$request->has('token')) { //some of request data are missing or invalid

                //return the validation errors
                return $this->validationErrorsResponse('Some data are missing');

            }


            //temp solution, I will change it later
            if($request->token != 'S2y$10$xjXba1aTe1S3L8aqkVIWGONYQn3UL/IluiYqCZvB7hzgBsXXoFhUmH')
                abort(401);

            //get all clients
            $clients = User::with('client')
                                ->with('client.createdBy:id,first_name,second_name')
                                ->where('type', 'client')
                                ->get();

            //get all leads
            $leads = Lead::with('createdBy:id,first_name,second_name')
                            ->with('assignedTo:id,first_name,second_name')
                            ->with('reassignedTo:id,first_name,second_name')
                            ->with('reassignedBy:id,first_name,second_name')
                            ->get();


            //Get all Employees
            $employees = User::with('employee')
                            ->with('employee.level')
                            ->with('employee.createdBy:id,first_name,second_name')
                            ->where('type', 'employee')
                            ->get();

            //get all brokers
            $brokers = User::where('type', 'broker')
                            ->get();

            
            //get all units
            $units = Unit::with('createdBy:id,first_name,second_name')
                            ->with('compound:id,name,address')
                            ->with('compound.area')
                            ->get();
            //build the response
            $code = 200;
            $status = 'success';
            $message = "Statistics";
            $dataContent = [
                'clients' => $clients,
                'leads' => $leads,
                'employees' => $employees,
                'brokers' => $brokers,
                'units' => $units,
            ];

            return $this->returnApiResult($code, $status, $message, $dataContent);

        }catch (Exception $e) {  

            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
 
            //Unknown error happened
            return $this->unknownErrorHappenedMsg();
 
 
        }
    }
}
