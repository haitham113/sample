<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

//Additional added by Haitham
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
 

   /**
     * Overwrite the login function at AuthenticatesUsers controller
     *  
     * Handle an authentication attempt.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return Response
     */
    public function login(Request $request){

        //$credentials = $request->only('email', 'password');
        $email = $request->input('email');
        $password = $request->input('password');
        $active = 1;

        if (Auth::attempt(['email' => $email, 'password' => $password, 'active' => $active])){
            // Authentication passed...
            //return redirect()->intended('dashboard');

            $user = auth()->user();
            $permissions = $user->getPermissions();

            //build the response
            $code = 200;
			$status = 'success';
			$message = 'The user has been logged successfully';
            $dataContent = [
                'user' => $user,
                'permissions' => $permissions
            ];
    
            return $this->returnApiResult($code, $status, $message, $dataContent);

        }else{ //Failed

            //build the response
            $code = 404;
            $status = 'error';
            $message = 'These credentials do not match our records or your account has been deactivated';
            $dataContent = "";
     
            return $this->returnApiResult($code, $status, $message, $dataContent);

        }
    }


    /**
     * Overwrite the logout function at AuthenticatesUsers controller
     * 
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request){

        $this->guard()->logout();

        $request->session()->invalidate();

        //build the response
        $code = 200;
        $status = 'success';
        $message = 'The user has been logged out successfully';
        $dataContent = "";
 
        return $this->returnApiResult($code, $status, $message, $dataContent);
       
    }

    
}
