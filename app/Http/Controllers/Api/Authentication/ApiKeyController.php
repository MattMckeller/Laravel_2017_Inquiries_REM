<?php

namespace App\Http\Controllers\Api\Authentication;

use App\Models\Customers\Customer;
use App\Models\Inquiries\Inquiry;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class ApiKeyController extends Controller
{
    private $AccessControl;
    private $TeamAuth;

    function __construct(){
        $this->AccessControl = App::make('AccessControl');
        $this->TeamAuth = App::make('TeamAuth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getApiKey(Request $request)
    {
        $logged_in = $this->TeamAuth->is_logged_in();
        $logged_in_id = $this->TeamAuth->get_logged_in_id();

        $response = array(
            'logged_in'=>false,
            'user_id'=>false,
            'api_key'=>false,
            'display_name'=>false,
            'error'=>false
        );

        if($logged_in !== true){
            return $this->_respond($response);
        }else{
            $response['logged_in'] = true;
        }

        if($logged_in_id === false){
            $response['error'] = 'Could not determine user id.';
            return $this->_respond($response);
        }else{
            $response['user_id'] = $logged_in_id;
        }

        $api_key_obj = $this->AccessControl->_get_api_key_object();
        if($api_key_obj===FALSE){
            $response['error'] = 'User logged in, but no api key was found.';
            return $this->_respond($response);
        }else{
            $expected_key = $api_key_obj->key_value;
            $current_key = $request->cookie('api_key_cookie');
            if(!empty($expected_key) && !empty($current_key)){
                if($expected_key === $current_key){
                    $response['api_key'] = $current_key;
                    $response['display_name'] = $this->TeamAuth->get_logged_in_display_name();
                    return $this->_respond($response);
                }else{
                    $response['error'] = 'Expected key does not match provided key.';
                    return $this->_respond($response);
                }
            }else{
                $response['error'] = 'Could not determine api key.';
                return $this->_respond($response);
            }
        }
    }

    function resetApiKey(Request $request){
        $current_key = $request->cookie('api_key_cookie');
        $keyVal = $request->input('api_key');
        if($current_key != $keyVal && !empty($keyVal)) {
            $this->AccessControl->create_cookie_with_key($keyVal);
            return $this->_respond(['success'=>true]);
        }else{
            return $this->_respond(['success'=>false]);
        }
    }

    function _respond($response){
        return $response;
    }
}
