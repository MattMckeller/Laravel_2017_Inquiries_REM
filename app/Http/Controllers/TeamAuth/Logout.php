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


class Logout extends CI_Controller
{
    function __construct(){
        parent::__construct();
    }

    function index(Request $request) //Log in page/ submit login data(uname,password,second_factor) here through post
    {
        $this->TeamAuth->logout();
        return response()->redirectTo('login');
    }
}
