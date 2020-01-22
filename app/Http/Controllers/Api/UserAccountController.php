<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Auth\Team\UserAccount;
use App\Models\Inquiries\Inquiry;

class UserAccountController extends Controller
{
    private $InquiryRepository;
    private $AccessControl;
    private $TeamAuth;

    function __construct(){
        $this->AccessControl = App::make('AccessControl');
        $this->TeamAuth = App::make('TeamAuth');
        $this->InquiryRepository = App::make('InquiryRepository');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /*
     * Function: Retrieve all representatives that inquiries
     * can be assigned to
     */
    function getAvailableInquiryRepresentatives() {
        $userAccountInformation = array();

        $userAccounts = UserAccount::all()->load('userAccountInformation')->load('apiKey.aclRole');
        //Iterate through user accounts looking for inquiry representatives
        foreach($userAccounts as $userAccount) {
            $userAccountId = $userAccount->id;
            $firstName = $userAccount->userAccountInformation['first_name'];
            $lastName = $userAccount->userAccountInformation['last_name'];
            $userRole = $userAccount->apiKey->aclRole['role'];

            //If the user is an inquiries representative, store for return
            if($userRole == 'admin' || $userRole == 'inquiries_manager' || $userRole == 'inquiries_agent') {
                $userAccountInformation[] = array(
                    'id' => $userAccountId,
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'role' => $userRole
                );
            }
        }
        return json_encode($userAccountInformation);
    }

    function getCurrentUser(){
        $teamAuth = App::make('TeamAuth');
        $user = $teamAuth->get_current_user_information();
        return response($user, 201);
    }
}
