<?php

namespace App\Http\Controllers\InquiryConfig;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;

class InquiryConfigController extends Controller
{
    private $AccessControl;
    private $TeamAuth;

    function __construct(){
        $this->AccessControl = App::make('AccessControl');
        $this->TeamAuth = App::make('TeamAuth');
    }

    /*
     * Function: View display for configuration of inquiry settings
     *
     */
    public function index() {
        return view('inquiryConfig/inquiryConfig');
    }
}
