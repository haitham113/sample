<?php

namespace App\Http\Middleware;

use Closure;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //just for testing [This line must deleted for the production version]
        return $next($request);


        if($request->user() === null){
            
            //build the response
            $code = 401;
            $status = 'error';
            $message = "You are not permitted to do this action";
            $dataContent = "";

            return $this->returnApiResult($code, $status, $message, $dataContent);
        }
        
		$actions = $request->route()->getAction();
		$permissions = isset($actions) ? $actions['permissions'] : null;
		
		if($request->user()->hasAnyPermission($permissions) || !$permissions){
			return $next($request);
		}
         
		//build the response
        $code = 401;
        $status = 'error';
        $message = "You are not permitted to do this action";
        $dataContent = "";

        return $this->returnApiResult($code, $status, $message, $dataContent);
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
