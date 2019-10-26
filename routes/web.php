<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


//Login routes [disable the registraion and forget password]
Auth::routes(['register' => false, 'reset'=> false]);


Route::get('/haitham', function () {
    Auth::logout();
    echo  date('Y-m-d');
});

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'backend',  /*'middleware' => 'auth'*/], function(){

//users ----------------------------------------------------------------------------------
//1- Get all users
Route::get('/users', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin"], 
    "uses"=> "UserController@index"]
);

//1- Add new user
Route::post('/users', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin"], 
    "uses"=> "UserController@store"]
);

//2- get the user data for the update form
Route::get('/users/{id}/edit', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin"], 
    "uses"=> "UserController@edit"]
)->where('id', '[0-9]+');

//3- Update the user data
Route::post('/users/{id}', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin"], 
    "uses"=> "UserController@update"]
)->where('id', '[0-9]+');



//Permissions ----------------------------------------------------------------------------------
//0- Get all System Modules with permissions [Not Used just for testing]
Route::get('/permissions', [
        "middleware"=>["permission"], 
        "permissions"=> ["root_admin"], 
        "uses"=> "PermissionController@index"]
);

//1- Get all permissions of a user
Route::get('/userPermissions/{id}/edit', [
        "middleware"=>["permission"], 
        "permissions"=> ["root_admin"], 
        "uses"=> "PermissionController@edit"]
)->where('id', '[0-9]+');

//2- Update the permissions of a user 
Route::post('/userPermissions/{id}', [
        "middleware"=>["permission"], 
        "permissions"=> ["root_admin"], 
        "uses"=> "PermissionController@update"]
)->where('id', '[0-9]+');


//Areas ----------------------------------------------------------------------------------
//1- Get all areas
Route::get('/areas', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "area_add", "area_edit", "area_view", "area_delete"], 
    "uses"=> "AreaController@index"]
);
//1- Add new areas
Route::post('/areas/store', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "area_add"],
    "uses"=> "AreaController@store"]
);
//2- get the area data for the update form
Route::get('/areas/{id}/edit', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "area_edit"],
    "uses"=> "AreaController@edit"]
)->where('id', '[0-9]+');
//3- Update the area data
Route::post('/areas/{id}/update', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "area_edit"],
    "uses"=> "AreaController@update"]
)->where('id', '[0-9]+');
//3- Delete an area
Route::post('/areas/{id}/destroy', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "area_delete"], 
    "uses"=> "AreaController@destroy"]
)->where('id', '[0-9]+');


//Levels ----------------------------------------------------------------------------------
//1- Get all levels
Route::get('/levels', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "level_add", "level_edit", "level_view", "level_delete"], 
    "uses"=> "LevelController@index"]
);
//1- Add new level
Route::post('/levels/store', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "level_add"],
    "uses"=> "LevelController@store"]
);
//2- get the level data for the update form
Route::get('/levels/{id}/edit', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "level_edit"],
    "uses"=> "LevelController@edit"]
)->where('id', '[0-9]+');
//3- Update the levels data
Route::post('/levels/{id}/update', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "level_edit"],
    "uses"=> "LevelController@update"]
)->where('id', '[0-9]+');
//3- Delete a level
Route::post('/levels/{id}/destroy', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "level_delete"], 
    "uses"=> "LevelController@destroy"]
)->where('id', '[0-9]+');


//Employees ----------------------------------------------------------------------------------
//1- Get all Employees
Route::get('/employees', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "team_leader", "employee_add", "employee_edit", "employee_view", "employee_delete"], 
    "uses"=> "EmployeeController@index"]
);
//1- Add new Employee
Route::post('/employees/store', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "team_leader", "employee_add"],
    "uses"=> "EmployeeController@store"]
);
//2- diaplay an Employee
Route::get('/employees/{id}/show', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "team_leader", "employee_view"],
    "uses"=> "EmployeeController@show"]
);
//3- get the Employee data for the update form
Route::get('/employees/{id}/edit', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "team_leader", "employee_edit"],
    "uses"=> "EmployeeController@edit"]
)->where('id', '[0-9]+');
//4- Update the Employee data
Route::post('/employees/{id}/update', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "team_leader", "employee_edit"],
    "uses"=> "EmployeeController@update"]
)->where('id', '[0-9]+');
//5- Delete an Employee
Route::post('/employees/{id}/destroy', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "team_leader", "employee_delete"], 
    "uses"=> "EmployeeController@destroy"]
)->where('id', '[0-9]+');
//6- inject points or commissions to an employee 
Route::post('/employees/injectPointsOrCommission', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "team_leader"], 
    "uses"=> "EmployeeController@injectPointsOrCommission"]
);
//5- inject points or commissions to an employee 
Route::post('/employees/deductPointsOrCommission', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "team_leader"], 
    "uses"=> "EmployeeController@deductPointsOrCommission"]
);
//6- reassign Employee Work to another one
Route::post('/employees/reassignEmployeeWork', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "team_leader"], 
    "uses"=> "EmployeeController@reassignEmployeeWork"]
);



//Leads ----------------------------------------------------------------------------------
//1- Get all Leads
Route::get('/leads', ["uses"=> "LeadController@index"]);
//1- Add new Lead
Route::post('/leads/store', ["uses"=> "LeadController@store"]);
//2- get the Leads data for the update form
Route::get('/leads/{id}/edit', ["uses"=> "LeadController@edit"])->where('id', '[0-9]+');
//3- get the Leads data for display
Route::get('/leads/{id}/show', ["uses"=> "LeadController@show"])->where('id', '[0-9]+');
//4- Update the Leads data
Route::post('/leads/{id}/update', ["uses"=> "LeadController@update"])->where('id', '[0-9]+');
//5- Delete a Lead
Route::post('/leads/{id}/destroy', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "team_leader"],
    "uses"=> "LeadController@destroy"]
)->where('id', '[0-9]+');
//6- Reassign a lead to another employee
Route::post('/leads/{id}/reassign', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "team_leader"],
    "uses"=> "LeadController@reassignLead"]
)->where('id', '[0-9]+');
//7- Get all leads where the lead_type field equals [lead or data]
Route::get('/leads/{type}', ["uses"=> "LeadController@getAllLeadsWithType"]);
//8- get the leads Duplicates basd on the phone number
Route::get('/getDuplicates', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "team_leader"],
    "uses"=> "LeadController@getDuplicates"]
);
//9- get the details of the lead using the phone number [for the Duplicates]
Route::get('/getLeadsByPhone/{phone}', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "team_leader"],
    "uses"=> "LeadController@getLeadsByPhone"]
)->where('id', '[0-9]+');
//importLeads
Route::post('/leads/importLeads', 'LeadController@importLeads');


//Reminders ----------------------------------------------------------------------------------
//1- Get all Reminders
Route::get('/reminders', ["uses"=> "ReminderController@index"]);
//1- Add new Reminder
Route::post('/reminders/store', ["uses"=> "ReminderController@store"]);
//2- get the Reminders data for the update form
Route::get('/reminders/{id}/edit', ["uses"=> "ReminderController@edit"])->where('id', '[0-9]+');
//3- get the Leads data for display
Route::get('/reminders/{id}/show', ["uses"=> "ReminderController@show"])->where('id', '[0-9]+');
//4- Update the Reminders data
Route::post('/reminders/{id}/update', ["uses"=> "ReminderController@update"])->where('id', '[0-9]+');
//5- Delete a reminder
Route::post('/reminders/{id}/destroy', ["uses"=> "ReminderController@destroy"])->where('id', '[0-9]+');





//Compounds ----------------------------------------------------------------------------------
//1- Get all Compounds
Route::get('/compounds', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "compound_add", "compound_edit", "compound_view", "compound_delete"], 
    "uses"=> "CompoundController@index"]
);
//1- Add new compound
Route::post('/compounds/store', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "compound_add"],
    "uses"=> "CompoundController@store"]
);
//2- get the compound data for the update form
Route::get('/compounds/{id}/edit', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "compound_edit"],
    "uses"=> "CompoundController@edit"]
)->where('id', '[0-9]+');
//3- Update the compound data
Route::post('/compounds/{id}/update', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "compound_edit"],
    "uses"=> "CompoundController@update"]
)->where('id', '[0-9]+');
//3- Delete an compound
Route::post('/compounds/{id}/destroy', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "compound_delete"], 
    "uses"=> "CompoundController@destroy"]
)->where('id', '[0-9]+');


//Units ----------------------------------------------------------------------------------
//1- Get all units
Route::get('/units', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "unit_add", "unit_edit", "unit_view", "unit_delete"], 
    "uses"=> "UnitController@index"]
);
//1- Add new unit
Route::post('/units/store', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "unit_add"],
    "uses"=> "UnitController@store"]
);
//2- get the unit data for the update form
Route::get('/units/{id}/edit', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "unit_edit"],
    "uses"=> "UnitController@edit"]
)->where('id', '[0-9]+');
//3- Update the unit data
Route::post('/units/{id}/update', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "unit_edit"],
    "uses"=> "UnitController@update"]
)->where('id', '[0-9]+');
//4- Display the unit
Route::get('/units/{id}/show', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "unit_view"],
    "uses"=> "UnitController@show"]
)->where('id', '[0-9]+');
//5- Delete an unit
Route::post('/units/{id}/destroy', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "unit_delete"], 
    "uses"=> "UnitController@destroy"]
)->where('id', '[0-9]+');






//Clients ----------------------------------------------------------------------------------
//1- Get all Clients
Route::get('/clients', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "team_leader", "client_add", "client_edit", "client_view", "client_delete"], 
    "uses"=> "ClientController@index"]
);
//1- Add new client
Route::post('/clients/store', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "team_leader", "client_add"],
    "uses"=> "ClientController@store"]
);
//2- dispaly the data of the client
Route::get('/clients/{id}/show', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "team_leader", "client_show"],
    "uses"=> "ClientController@show"]
)->where('id', '[0-9]+');
//2- get the client data for the update form
Route::get('/clients/{id}/edit', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "team_leader", "client_edit"],
    "uses"=> "ClientController@edit"]
)->where('id', '[0-9]+');
//3- Update the client data
Route::post('/clients/{id}/update', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "team_leader", "client_edit"],
    "uses"=> "ClientController@update"]
)->where('id', '[0-9]+');
//3- Delete an client
Route::post('/clients/{id}/destroy', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "team_leader", "client_delete"], 
    "uses"=> "ClientController@destroy"]
)->where('id', '[0-9]+');

//
Route::post('/clients/storeInterestedInUnits', ["uses"=> "ClientController@storeInterestedInUnits"]);
Route::get('/clients/getInterestedInUnitsForClient/{id}', ["uses"=> "ClientController@getInterestedInUnitsForClient"])->where('id', '[0-9]+');
Route::post('/clients/deleteInterestedInUnitForClient', ["uses"=> "ClientController@deleteInterestedInUnitForClient"]);




//Todos ----------------------------------------------------------------------------------
//1- Get all todos
Route::get('/todos', "TodoController@index");
//1- Add new todo
Route::post('/todos/store', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "team_leader"],
    "uses"=> "TodoController@store"]
);
//2- get the todo data for the update form
Route::get('/todos/{id}/edit', "TodoController@edit")->where('id', '[0-9]+');

//3- Update the todo data
Route::post('/todos/{id}/update', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "team_leader"],
    "uses"=> "TodoController@update"]
)->where('id', '[0-9]+');

//4- Delete an todo
Route::post('/todos/{id}/destroy', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin"], 
    "uses"=> "TodoController@destroy"]
)->where('id', '[0-9]+');

//5- update the status of my todo ex: from In porgress to done
Route::post('/todos/updateMyTodoStatus', "TodoController@updateMyTodoStatus")->where('id', '[0-9]+');



//activities ----------------------------------------------------------------------------------
//1- Get all activities
Route::get('/activities', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "activity_add", "activity_edit", "activity_view", "activity_delete"], 
    "uses"=> "ActivityController@index"]
);
//1- Add new activity
Route::post('/activities/store', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "team_leader", "activity_add"],
    "uses"=> "ActivityController@store"]
);
//2- dispaly the data of the activity
Route::get('/activities/{id}/show', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "team_leader", "activity_show"],
    "uses"=> "ActivityController@show"]
)->where('id', '[0-9]+');
//2- get the activity data for the update form
Route::get('/activities/{id}/edit', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "team_leader", "activity_edit"],
    "uses"=> "ActivityController@edit"]
)->where('id', '[0-9]+');
//3- Update the activity data
Route::post('/activities/{id}/update', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "team_leader", "activity_edit"],
    "uses"=> "ActivityController@update"]
)->where('id', '[0-9]+');
//3- Delete an activity
Route::post('/activities/{id}/destroy', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "team_leader", "activity_delete"], 
    "uses"=> "ActivityController@destroy"]
)->where('id', '[0-9]+');



//Gamification
//1- get data to update the fixed points
Route::get('/points/getFixedPoints', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", 'update_fixed_points'],
    "uses"=> "PointController@getFixedPoints"]
);
//2- Update the fixed points
Route::post('/points/updateFixedPoints', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", 'update_fixed_points'],
    "uses"=> "PointController@updateFixedPoints"]
);
//3- get data to update the Happy Hour
Route::get('/points/getHappyHourPoints', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", 'update_HappyHour_points'],
    "uses"=> "PointController@getHappyHourPoints"]
);
//4- Update the Happy Hour
Route::post('/points/updateHappyHourPoints', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", 'update_HappyHour_points'],
    "uses"=> "PointController@updateHappyHourPoints"]
);
//5-get data to update the target
Route::get('/points/getTargetPoints', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", 'update_Target_points'],
    "uses"=> "PointController@getTargetPoints"]
);
//6- Update the target
Route::post('/points/updateTargetPoints', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", 'update_Target_points'],
    "uses"=> "PointController@updateTargetPoints"]
);
//5-get data to update the formula
Route::get('/points/getFormula', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", 'update_Formula_points'],
    "uses"=> "PointController@getFormula"]
);
//6- Update the formula
Route::post('/points/updateFormula', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", 'update_Formula_points'],
    "uses"=> "PointController@updateFormula"]
);


//Broker ----------------------------------------------------------------------------------
//1- Get all Brokers
Route::get('/brokers', ["uses"=> "UserController@indexBroker"]);
//1- Add new broker
Route::post('/brokers/store', ["uses"=> "UserController@storeBroker"]);
//2- get the brokers data for the update form
Route::get('/brokers/{id}/edit', ["uses"=> "UserController@editBroker"])->where('id', '[0-9]+');
//3- get the brokers data for display
Route::get('/brokers/{id}/show', ["uses"=> "UserController@showBroker"])->where('id', '[0-9]+');
//4- Update the brokers data
Route::post('/brokers/{id}/update', ["uses"=> "UserController@updateBroker"])->where('id', '[0-9]+');


//Sales ----------------------------------------------------------------------------------
//1- A function to update the status of the unit and/or sale it
Route::post('/sales/saleUpdateUnitStatus', ["uses"=> "SaleController@saleUpdateUnitStatus"]);
//2- A function to get all new temp requests to take an action
Route::get('/sales/getAllTempRequests', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", 'team_leader'],
    "uses"=> "SaleController@getAllTempRequests"]
);
//3- A function to approve/disapprove an operation
Route::post('/sales/approveDisapproveOperation', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", 'team_leader'],
    "uses"=> "SaleController@approveDisapproveOperation"]
);
//4- primary
Route::post('/sales/primary', ["uses"=> "SaleController@primary"]);
//5- Resale: Externally
Route::post('/sales/resaleExternally', ["uses"=> "SaleController@resaleExternally"]);
//6-Resale: Internally
Route::post('/sales/resaleInternally', ["uses"=> "SaleController@resaleInternally"]);


//Sales ----------------------------------------------------------------------------------
//get all Position  
Route::get('/positions', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", "position_add", "position_edit", "position_view", "position_delete"],
    "uses"=> "PositionController@index"]
);
//Add new Position
Route::post('/positions/store', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", 'position_add'],
    "uses"=> "PositionController@store"]
);
//edit Position
Route::get('/positions/{id}/edit', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", 'position_edit'],
    "uses"=> "PositionController@edit"]
);
//edit Position
Route::post('/positions/{id}/update', [
    "middleware"=>["permission"], 
    "permissions"=> ["root_admin", 'position_edit'],
    "uses"=> "PositionController@update"]
);





//Companies ----------------------------------------------------------------------------------
// //1- Get all Companies
// Route::get('/companies', [
//     "middleware"=>["permission"], 
//     "permissions"=> ["root_admin", "company_add", "company_edit", "company_view", "company_delete"], 
//     "uses"=> "CompanyController@index"]
// );
// //1- Add new company
// Route::post('/companies/store', [
//     "middleware"=>["permission"], 
//     "permissions"=> ["root_admin", "company_add"],
//     "uses"=> "CompanyController@store"]
// );
// //2- dispaly the data of the company
// Route::get('/companies/{id}/show', [
//     "middleware"=>["permission"], 
//     "permissions"=> ["root_admin", "company_show"],
//     "uses"=> "CompanyController@edit"]
// )->where('id', '[0-9]+');
// //3- Update the company data
// Route::post('/companies/{id}/update', [
//     "middleware"=>["permission"], 
//     "permissions"=> ["root_admin", "company_edit"],
//     "uses"=> "CompanyController@update"]
// )->where('id', '[0-9]+');
// //3- Delete an company
// Route::post('/companies/{id}/destroy', [
//     "middleware"=>["permission"], 
//     "permissions"=> ["root_admin", "company_delete"], 
//     "uses"=> "CompanyController@destroy"]
// )->where('id', '[0-9]+');


}); //End of group 


Route::get('/home', 'HomeController@index')->name('home');
Route::get('/test',  function(){
    return view('test');
});



/* Custom Resource controller routes
   Route::resource('photos', 'PhotoController');
    - GET 	      /photos 	                    index 	photos.index
    - GET 	      /photos/create 	            create 	photos.create
    - POST        /photos/store                 store 	photos.store
    - GET 	      /photos/{photo}/show          show 	photos.show
    - GET    	  /photos/{photo}/edit 	        edit 	photos.edit
    - POST        /photos/{photo}/update        update 	photos.update
    - POST 	      /photos/{photo}/destroy 	    destroy 	photos.destroy
*/
