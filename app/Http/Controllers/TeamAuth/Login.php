<?php

namespace App\Http\Controllers\TeamAuth;

use Illuminate\Http\Request;
use App\Http\Controllers\CI_Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use REM\Authentication\TeamAuth as TeamAuth;
use App\Models\Auth\Team\UserAccount as User_account;
use App\Models\Auth\Team\AuthWord as Auth_Word;


class Login extends CI_Controller
{
    function __construct(){
        parent::__construct();
    }

    function index(Request $request) //Log in page/ submit login data(uname,password,second_factor) here through post
    {
        $second_factor_authentication = TeamAuth::$config['second_factor_authentication'];
        $second_factor_auth_method = TeamAuth::$config['second_factor_authentication_method'];

        if(session('login_processed')){
            session()->forget('login_processed');
            return redirect('login');
        }
        $user = $this->TeamAuth->get_user();

        if($user !== FALSE){
            // already logged in, redirect to welcome page
            return $this->_successful_login_handler();
        }
        $username = $request->input('username', null);
        $password = $request->input('password', null);
        $login_attempt_in_progress = session('login_attempt_in_progress');
        $second_factor_auth_val = NULL;
        $second_factor_auth_key = 'unset_second_factor';

        switch($second_factor_auth_method){
            case 'auth_word':
                $second_factor_auth_val = $request->input('auth_word');
                $second_factor_auth_key = 'auth_word';
                if($second_factor_auth_val !== FALSE){
                    session()->put('auth_word', $second_factor_auth_val);
                }
                break;
            default:
                $this->_log("Error, unknown second factor authentication method. Username:{$username}", "error", "login");
                return;
                break;
        }

        if(($username && $password) || $login_attempt_in_progress ){//Redirect to proper login page/function if username/pass are provided
            //If user submit info and then hit back/redirected to login and canceled their login process
            if( $login_attempt_in_progress === TRUE &&
                $second_factor_authentication === TRUE &&
                $second_factor_auth_val === FALSE){
                session()->put("$second_factor_auth_key", '_canceled_login_');
                return $this->_process_login();
            }

            if($second_factor_auth_val){
                return $this->_process_login();
            }

            //Store credentials to session
            session()->put('username', $username);
            session()->put('password', $password);

            $user_account = new User_account();
            $user_account->username = $username;
            $user_account->password = $password;
            $status = $this->TeamAuth->process_login($user_account, false);
            $success = $status['success'];
            if($success === TRUE){
                session()->put('login_attempt_in_progress', TRUE);
                return redirect('login/second_factor_authentication');
            }else{
                $message = 'Login attempt failed.';
                session()->put('login_warning_message', $message);
                return redirect('login');
            }
        }

        session()->forget(array('username' => '', 'password' => ''));
        $login_warning_message = session('login_warning_message');
        session()->forget('login_warning_message');
        $view_data = array('login_warning_message'=>$login_warning_message);

        return view('login/login', $view_data);
    }

    function second_factor_authentication(Request $request){
        $second_factor_authentication = TeamAuth::$config['second_factor_authentication'];
        $second_factor_auth_method = TeamAuth::$config['second_factor_authentication_method'];
        //Make sure user not already logged in
        if($this->TeamAuth->get_user() !== FALSE){
            return redirect('login');
        }

        //Get username and pass from session data
        $username = session('username');
        $password = session('password');
        if( $username === FALSE
            ||  $password === FALSE){
            return redirect('login');
        }

        //If second factor auth is disabled, no reason to be here.
        if($second_factor_authentication === FALSE){
            $this->_process_login();
            return;
        }

        $second_factor_in_progress = session('second_factor_in_progress');
        if($second_factor_in_progress === true){
            session()->forget('second_factor_in_progress');
            session()->put("$second_factor_auth_method", '_canceled_second_factor_');
            $this->_process_login();
            return;
        }
        session()->put('second_factor_in_progress', TRUE);

        /*Setting up data to pass to view*/
        $data = array();
        $user_acc = new User_account();
        $user_acc = $user_acc::where('username', $username)->first();
        /**/
        switch($second_factor_auth_method){
            case 'auth_word':
                $word_options = session('auth_word_options');
                if( $word_options === null ){
                    $word_options = $this->TeamAuth->auth_word_options($username);
                    session()->put('auth_word_options', $word_options);
                }else{
                    //Page most likely refreshed
                }
                $data['auth_word_options'] = $word_options;
                return view('login/login_auth_word', $data);
                break;
            default:
                $this->_log("Error, unknown second factor authentication method.", "error", "login");
                break;
        }
    }

    function _process_login(){
        $second_factor_authentication = TeamAuth::$config['second_factor_authentication'];
        $second_factor_auth_method = TeamAuth::$config['second_factor_authentication_method'];

        session()->forget('login_attempt_in_progress');
        //Make sure user not already logged in
        if($this->TeamAuth->get_user() !== FALSE)
        {
            return redirect('login');
        }

        // Make sure username and pass are supplied
        if( session('username') === FALSE
            ||  session('password') === FALSE){
            return redirect('login');
        }

        $user_account = new User_account();

        session()->forget('second_factor_in_progress');
        session()->put('login_processed', 1);
        /*Second factor authentication check*/
        if($second_factor_authentication){
            switch($second_factor_auth_method){
                case 'auth_word':
                    session()->forget('auth_word_options');
                    $auth_word = session('auth_word');
                    session()->forget('auth_word');

                    if($auth_word === FALSE){
                        session()->forget(array('username' => '', 'password' => ''));
                        $this->_log("Auth word was not provided for login attempt. Username:{session('username')}", "error", "login");
                        return;
                    }else{
                        $user_auth_word = new Auth_word();
                        $user_auth_word->word = $auth_word;
                        $user_account->auth_word = $user_auth_word;
                    }
                    break;
                default:
                    session()->forget(array('username' => '', 'password' => '', 'auth_word' => ''));
                    $this->_log("Error, unknown second factor authentication method. Username:{session('username')}", "error", "login");
                    return;
            }
        }
        /**/
        // A login was attempted, load the user data
        $user_account->username = session('username');
        $user_account->password = session('password');

        session()->forget(array('username' => '', 'password' => '', 'auth_word' => ''));

        // get the result of the login request
        $status = $this->TeamAuth->process_login($user_account, $second_factor_authentication);
        $successful_login = $status['success'];
        if($successful_login == true){
            return $this->_successful_login_handler();
        }else{
            return $this->_failed_login_handler();
        }
    }

    function forgot_password(){
        $this->load->view('headers/includes/common');
        $this->load->view('headers/authentication/forgot_password.php');
        $this->load->view('pages/authentication/forgot_password.php');
    }

    function forgot_password_submit(){
        session()->put('forgot_password_attempts_count', 0);
        $failure_response = array('success'=>false);
        $current_time = new DateTime("now");
        $current_time_string = $current_time->format('Y-m-d H:i:s');
        $five_minute_dateinterval = new DateInterval('PT5M'); //Five minutes
        $acceptable_datetime = $current_time->sub($five_minute_dateinterval);

        $post_input = $request->all();
        $required_keys = array(
            'email_address',
            'last_name_first_two_characters'
        );

        if(!check_array_keys_exist($post_input, $required_keys)){
            $failure_response['error'] = 'Insufficient input data';
            echo json_encode($failure_response);
            return false;
        }

        //Make sure the user has not attempted to recover an account too many times
        $forgot_password_count = session('forgot_password_attempts_count');
        $last_attempt_datetime = session('forgot_password_attempts_count_datetime');
        if(empty($forgot_password_count)){
            $forgot_password_count = 0;
            session()->put('forgot_password_attempts_count', 0);
            session()->put('forgot_password_attempts_count_datetime', $current_time_string);
        }else {
            $last_attempt_datetime_obj = DateTime::createFromFormat('Y-m-d H:i:s', $last_attempt_datetime);
            if ($forgot_password_count >= 5 && $last_attempt_datetime_obj !== FALSE && $last_attempt_datetime_obj > $acceptable_datetime) {
                $failure_response['message'] = 'Too many password recovery attempts. Try again in five minutes.';
                echo json_encode($failure_response);
                return false;
            } elseif($last_attempt_datetime_obj !== FALSE && $last_attempt_datetime_obj <= $acceptable_datetime) {
                session()->put('forgot_password_attempts_count', 0);
                session()->put('forgot_password_attempts_count_datetime', $current_time_string);
            }
        }

        session()->put('forgot_password_attempts_count', $forgot_password_count+1);
        session()->put('forgot_password_attempts_count_datetime', $current_time_string);

        //
        //Start mw code
        //
        $provided_email_address = $post_input['email_address'];
        $provided_last_two_characters = strtolower(substr($post_input['last_name_first_two_characters'],0,2));
        $success = $this->TeamAuth->forgot_password($provided_email_address, $provided_last_two_characters);

        $response = array('success'=>$success);
        echo json_encode($response);

    }

    function reset_password($forgot_password_token=''){
        if(empty($forgot_password_token)){
            return redirect('forgot_password');
        }
        session()->put('forgot_password_token', $forgot_password_token);

        $this->load->view('headers/includes/common');
        $this->load->view('headers/authentication/reset_password.php');
        $this->load->view('pages/authentication/reset_password.php');
    }

    function reset_password_submit(Request $request){
        $post_input = $request->all();
        $failure_response = array(
            'success'=>false,
        );
        $required_keys = array(
            'requested_password', 'confirm_requested_password'
        );

        if(!check_array_keys_exist($post_input, $required_keys)){
            $failure_response['error'] = 'Insufficient input data';
            echo json_encode($failure_response);
            return false;
        }
        $forgot_password_token = session('forgot_password_token');
        if(empty($forgot_password_token)) {
            return redirect('forgot_password');
        }
        $requested_password = $post_input['requested_password'];
        $confirm_password = $post_input['confirm_requested_password'];
        if(!validate_password_complexity($post_input['requested_password'])){
            $this->_send_response("Password not strong enough. Please try again.", false);
            return false;
        }
        else if($requested_password !== $confirm_password) {
            $this->_send_response("Passwords do not match", true);
            return false;
        }
        else {
            $response = $this->TeamAuth->reset_password($requested_password, $forgot_password_token);

            if($response == true) {
                $this->_send_response("Password has been reset", true);
            }
            else {
                $this->_send_response("Password not able to be reset", false);
            }
        }
        return false;
    }

    private function _log($message, $log_type="general", $class="login_controller"){
        $this->log_control->log_all($message, $log_type, $class);
    }

    function _failed_login_handler(){
        //not finished but needs to return redirect afterwards
        return redirect('login');
    }

    function _successful_login_handler(){
        $display_name = $this->TeamAuth->get_logged_in_display_name();
        session()->put('display_name', $display_name);
        return redirect('/createNewCustomer');
    }

    function _send_response($display_message, $success, $errors=false){
        $success_message = ($success)?("true"):("false");
        $response = array(
            "display_message"=>$display_message,
            "success"=>$success_message
        );
        if($errors!=false && !empty($errors)){
            $response['errors'] = $errors;
        }

        echo json_encode($response);
        return true;
    }
}
