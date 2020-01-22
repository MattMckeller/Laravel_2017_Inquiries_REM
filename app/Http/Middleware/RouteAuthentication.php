<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
//use Illuminate\Session\SessionManager;
use REM\AccessControl\AccessControl;
use REM\Authentication\TeamAuth;

class RouteAuthentication
{
    private $AccessControl;
    private $TeamAuth;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    function __construct()
    {
        $this->AccessControl = App::make('AccessControl');
        $this->TeamAuth = App::make('TeamAuth');
    }

    public function handle($request, Closure $next)
    {
        $appServRequest = $this->checkIfAppServerRequest();
        if($appServRequest === true){
            return $this->handleAppServ($request, $next);
        }else{
            return $this->handleUser($request, $next);
        }
    }

    function handleUser($request, Closure $next){
        $pathSegments = $request->segments();
        if(!$this->isLoggedIn() && !$this->isDefaultAllowedController($pathSegments)){
            return redirect('login');
        }

        if($this->isAuthorizedForPath($request, $pathSegments)){
            return $next($request);
        }else{
            return response('Error - Access to page denied.', 404);
        }
    }

    function handleAppServ($request, Closure $next){
        $pathSegments = $request->segments();
        $app_login_success = $this->TeamAuth->process_web_app_login();
        //Destroy denied/failed web_app logins
        if($app_login_success !== true){
            return response('Error - Access to page denied.', 404);
        }else if($this->isAuthorizedForPath($request, $pathSegments)){
            return $next($request);
        }else{
            return response('Error - Access to page denied.', 404);
        }
    }

    function isLoggedIn(){
        $currentUser = $this->TeamAuth->get_user();
        return (!empty($currentUser) && $currentUser->id);
    }
    
    
    
    function isAuthorizedForPath($request, $pathSegments){
        $pathSegmentOne = (!empty($pathSegments[0]))?($pathSegments[0]):("");
        $pathSegmentTwo = (!empty($pathSegmentOne) && $pathSegmentOne == 'api' && !empty($pathSegments[1]))?($pathSegments[1]):(false);

        if(!$this->isDefaultAllowedController($pathSegments)) {
            $access_control_returns_controller = $this->AccessControl->check_auth_for_controller($pathSegmentOne, $pathSegmentTwo);
            $authorized = $access_control_returns_controller['success'];
            return $authorized;
        }else {
            return true; //Authorized
        }
    }

    function isDefaultAllowedController($pathSegments){
        $pathSegmentOne = (!empty($pathSegments[0]))?($pathSegments[0]):("");
        $pathSegmentTwo = (!empty($pathSegmentOne) && $pathSegmentOne == 'api' && !empty($pathSegments[1]))?($pathSegments[1]):(false);
        $pathSegmentOne = (!empty($pathSegments[0]))?($pathSegments[0]):("");
        if($pathSegmentOne =='api'){
            $allowed_api_controllers = array('getApiKey', 'resetApiKey');
            return (in_array($pathSegmentTwo, $allowed_api_controllers));
        }else {
            $allowed_controllers = array('login', 'logout', 'team', '');
            return (in_array($pathSegmentOne, $allowed_controllers));
        }
    }
    
    function checkIfAppServerRequest(){
        if($this->AccessControl->is_app_server()){
            return true;
        }else { //THE REQUEST IS BEING MADE BY A USER
            $this->AccessControl->check_logged_in_user();
            return false;
        }
    }
}
