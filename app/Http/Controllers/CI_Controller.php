<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use REM\Authentication\TeamAuth as Authentication;

class CI_Controller extends Controller
{
    protected $TeamAuth;
    function __construct(){
        $this->TeamAuth = App::make('TeamAuth');
    } 
    
}
