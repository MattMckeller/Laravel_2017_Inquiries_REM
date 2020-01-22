<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use App\Models\Customers\Customer;
use App\Models\Customers\EmailAddress;
use App\Models\Customers\Guarantor;
use App\Models\Customers\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class TeamRoutingController extends Controller {

    function __construct(){
    }

    function customer_profile(Customer $customer){
        $url = env('TEAM_URL').'/customer_profile/'.$customer->id;
        return redirect($url);
    }

    function customer_tables(){
        $url = env('TEAM_URL').'/customer_tables';
        return redirect($url);
    }

    function create_new_customer(){
        $url = env('TEAM_URL').'/create_new_customer';
        return redirect($url);
    }

    function analytics(){
        $url = env('TEAM_URL').'/analytics';
        return redirect($url);
    }

    function property_overview(){
        $url = env('TEAM_URL').'/property_overview';
        return redirect($url);
    }

    function lease_signing(){
        $url = env('TEAM_URL').'/lease_signing';
        return redirect($url);
    }

    function lease_generation(){
        $url = env('TEAM_URL').'/lease_generation';
        return redirect($url);
    }

    function document_actions(){
        $url = env('TEAM_URL').'/document_actions';
        return redirect($url);
    }

}