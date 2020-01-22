<?php

namespace App\Http\Controllers\Api;

use App\Models\Auth\Team\UserAccount;
use App\Models\Customers\Customer;
use App\Models\Inquiries\Inquiry;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use App\Repositories\Inquiries\InquiryRepository;
use REM\Authentication\TeamAuth;

class InquiryController extends Controller
{
    private $InquiryRepository;
    private $TeamAuth;

    function __construct(){
        /** @var  InquiryRepository */
        $this->InquiryRepository = App::make('InquiryRepository');
        $this->TeamAuth = App::make('TeamAuth');
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
    public static function store(Request $request)
    {
        $inquiry = new Inquiry();
        $inquiry->saveOrFail();

        return response($inquiry, 201);
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
     * Function: Retrieve all customer information related
     * for the provided inquiry
     *
     * @param $request Request | the provided inquiry id for customer querying
     */
    public function getCustomerInquiryInformation(Request $request){
        $inquiryID = $request->input('inquiryID');
        return $this->InquiryRepository->getCustomerInquiryInformation($inquiryID);
    }

    public function getInquiryStatus(Request $request)
    {
        $inquiryId = $request->id;
        $inquiryInformation = array(
            'id' => $inquiryId
        );
        return json_encode($inquiryInformation);
    }

    public function getInquiryAgents() {
        $availableUserAccounts = array();
        $userAccounts = UserAccount::where('webserver', '0')->get()->load('apiKey.aclRole');
        foreach($userAccounts as $userAccount) {
            $aclRole = (!empty($userAccount->apiKey->aclRole->role)) ? $userAccount->apiKey->aclRole->role : '';
            if($aclRole == 'inquiry_manager' || $aclRole == 'inquiry_representative' || $aclRole == 'admin') {
                array_push($availableUserAccounts, ['id' => $userAccount->id, 'username' => $userAccount->username, 'name' => $userAccount->userAccountInformation->first_name . " " . $userAccount->userAccountInformation->last_name]);
            }
        }
        return $availableUserAccounts;
    }

    public function assignInquiry(Request $request) {
        $inquiry = Inquiry::find($request->input('inquiryID', false));
        $inquiryAgent = UserAccount::find($request->input('inquiryAgentID', false));
        if ($inquiry && $inquiryAgent) {
            $this->InquiryRepository->assign($inquiry, $inquiryAgent);
        }

        $inquiry = Inquiry::find($request->input('inquiryID', false));
        $user = $this->TeamAuth->get_user();
        $currentDatetime = new \DateTime();
        $currentDate = $currentDatetime->format('Y-m-d H:i:s');

        return \Response::json(\View::make('inquiryCustomer.inquiryAccordionBarSnippet', array('agents' => false, 'selectedInquiry' => $inquiry, 'current_date' => $currentDate, 'logged_in_id' => $user->id))->render());
//        return \Response::json(\View::make('inquiryCustomer.inquiryAccordionBarSnippet', array('agents' => $this->getInquiryAgents(), 'selectedInquiry' => $inquiry, 'current_date' => $currentDate, 'currentUser' => $currentUser))->render());
//        return \Response::view('inquiryCustomer.inquiryAccordionBarSnippet', array('agents' => $this->getInquiryAgents(), 'selectedInquiry' => $inquiry, 'current_date' => $currentDate));
    }
}
