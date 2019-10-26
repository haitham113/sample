<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated 
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            
            //build the response
            $code = 401;
            $status = 'error';
            $message = 'You are already logged in';
            $dataContent = "";
     
            return $this->returnApiResult($code, $status, $message, $dataContent);
        }

        return $next($request);
    }


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
	    
	    return response()->json($response, 200, [], JSON_UNESCAPED_UNICODE);
    }
}
