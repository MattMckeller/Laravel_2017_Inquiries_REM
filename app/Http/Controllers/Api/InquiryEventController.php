<?php

namespace App\Http\Controllers\Api;

use App\Models\Auth\Team\UserAccount;
use App\Models\Customers\Customer;
use App\Models\Inquiries\Inquiry;
use App\Models\Inquiries\InquiryEvent;
use App\Models\Inquiries\InquirySource;
use App\Models\Inquiries\InquirySourceOption;
use App\Models\Inquiries\InquirySourceSelection;
use App\Events\InquiryContactEvent;
use App\Models\Timers\Timer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use DateTime;

use Illuminate\Support\Facades\Session;

class InquiryEventController extends Controller
{

    private $InquiryRepository;
    private $AccessControl;
    private $TeamAuth;
    private $TimerRepository;

    function __construct(){
        $this->AccessControl = App::make('AccessControl');
        $this->TeamAuth = App::make('TeamAuth');
        $this->InquiryRepository = App::make('InquiryRepository');
        $this->TimerRepository = App::make('TimerRepository');
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
        $inquiryEvent = new InquiryEvent();
        if(!empty($request->input("inquiryTimestamp"))) {
            $inquiryEvent->provided_timestamp = date("Y-m-d H:i:s", strtotime($request->input("inquiryTimestamp")));
        }
        $inquiryEvent->saveOrFail();

        return response($inquiryEvent, 201);
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

    public function getInquiryEvents(Request $request)
    {
        $inquiryEventInformation = array();

        $inquiryId = $request->id;
        $inquiryEvents = InquiryEvent::where('inquiry_id', $inquiryId)->get();
        //Retrieve each inquiry source selection for the inquiry event
        foreach($inquiryEvents as $inquiryEvent) {
            $inquiryEventInformation[$inquiryEvent->id] = array(
                'provided_timestamp' => $inquiryEvent->provided_timestamp,
                'agent_contacted' => $inquiryEvent->agent_contacted,
            );

            if($inquiryEvent->agent_contacted == true) {
                //Retrieve the user account information for the event
                $userAccount = UserAccount::where('id', $inquiryEvent->user_account_id)->firstOrFail()->load('userAccountInformation');
                $userAccountInformation = $userAccount->userAccountInformation;
                $inquiryEventInformation[$inquiryEvent->id]['user'] = $userAccountInformation->first_name . ' ' . $userAccountInformation->last_name;
            }
            else {
                $inquiryEventInformation[$inquiryEvent->id]['user'] = 'customer';
            }

            $inquirySourceSelections = InquirySourceSelection::where('id', $inquiryEvent->inquiry_source_selection_id)->get();
            foreach($inquirySourceSelections as $inquirySourceSelection) {
                $inquirySources = InquirySource::where('id', $inquirySourceSelection->inquiry_source_id)->get();
                foreach($inquirySources as $inquirySource) {
                    $inquiryEventInformation[$inquiryEvent->id]['inquirySourceSelection'] = $inquirySource->display_name;
                }
            }
        }
        return json_encode($inquiryEventInformation);
    }

    public function storeInquiryEvent(Request $request) {

        $inquiryID = $request->input('inquiryID', false);
        $inquirySourceID = $request->input('inquirySource', false);
        $inquirySourceOption = $request->input('inquirySourceOption', false);
        $customerInitiatedContact = $request->input('customerInitiatedContact', false);
        $currentStarRating = $request->input('currentStarRating', false);
        $inquiryNote = $request->input('inquiryNote', false);
        $successfulContact = $request->input('successfulContact', true);


        $agentContacted = true;
        //Retrieve the logged in user
        $loggedInId = $this->TeamAuth->get_user()->id;

        //Retrieve inquiry event to determine if new user is claiming inquiry
        $inquiry = Inquiry::where('id', $inquiryID)->firstOrFail();

        //Update inquiry heat index based on provided current star rating
        $heatIndex = $this->convertHeatIndex($currentStarRating);
        $inquiry->heat_index = $heatIndex;
        $inquiry->save();

        //Claim inquiry if available to be claimed
        $currentDatetime = new DateTime();
        $currentDate = $currentDatetime->format('Y-m-d H:i:s');

        if(empty($inquiry->user_account_id) || $inquiry->agent_claim_expiration < $currentDate) {
            $this->InquiryRepository->claim($inquiry);
        }

        if($customerInitiatedContact == true) {
            $agentContacted = false;
        }

        $inquiryEvent = new InquiryEvent();
        if(!empty($request->input("inquiryTimestamp"))) {
            $inquiryEvent->provided_timestamp = date("Y-m-d H:i:s", strtotime($request->input("inquiryTimestamp")));
        }

        $inquiryEvent->inquiry_id = $inquiryID;
        $inquiryEvent->user_account_id = $loggedInId;
        $inquiryEvent->agent_contacted = $agentContacted;
        $inquiryEvent->successful_contact = (boolean)$successfulContact;

        //Find inquiry source selection id from inquiry source and inquiry source option
        $inquirySource = InquirySource::where('id', $inquirySourceID)->firstOrFail();
        $inquirySourceSelection = InquirySourceSelection::where('inquiry_source_id', $inquirySource->id)->where('inquiry_source_option_id', $inquirySourceOption)->first()->load('inquirySource');
        $inquiryEvent->inquiry_source_selection_id = $inquirySourceSelection->id;

        //Save the inquiry event
        $inquiryEvent->save();

        //Add the inquiry note to the inquiry event
        if(!empty($inquiryNote)) {
            $noteInformation = array(
                'inquiryID' => $inquiryID,
                'inquiryEventID' => $inquiryEvent->id,
                'text' => $inquiryNote
            );
            //Attempt to save and attach the inquiry note to the inquiry event
            $saveAttempt = InquiryNoteController::addInquiryNoteToInquiryEvent($noteInformation);
            if($saveAttempt != true) {
                return response("Unable to associate note for the customer", "500");
            }
        }

        //Retrieve current timer that can be satisfied by the inquiry event
        $inquiryTimers = $this->TimerRepository->retrieveTimersForInquiry($inquiry);
        foreach($inquiryTimers as $inquiryTimer) {
            //Retrieve the timer config type from the id
            $inquiryTimerContactTypes = $inquiryTimer->timerContactType()->get();

            foreach($inquiryTimerContactTypes as $inquiryTimerContactType) {
                //If inquiry source provided matches contact type required by timer, complete
                if($inquirySourceSelection->inquirySource->type == $inquiryTimerContactType->type) {
                    //Complete the timer
                    $completeTimer = $this->TimerRepository->completeTimer($inquiryTimer, $inquiryEvent);
                    if($completeTimer != true) {
                        return response("Unable to complete timer for customer", "500");
                    }
                    $nextTimer = $this->TimerRepository->getNextTimer($inquiry);
                    if($nextTimer != true) {
                        return response("Unable to create new timer for customer", "201");
                    }
                }
            }
        }

        //Retrieve the customer for the inquiry
        $customerInquiry = Inquiry::where('id', $inquiryID)->first()->load('customer');
        event(new InquiryContactEvent($customerInquiry));
        return response($inquiryEvent, 201);
    }

    public function convertHeatIndex($currentStarRating) {
        $convertedStarRating = NULL;
        switch($currentStarRating) {
            case "5":
                $convertedStarRating = '100';
                break;
            case "4":
                $convertedStarRating = '80';
                break;
            case "3":
                $convertedStarRating = '60';
                break;
            case "2":
                $convertedStarRating = '40';
                break;
            case "1":
                $convertedStarRating = '20';
                break;
            case "0":
                break;
        }
        return $convertedStarRating;
    }

    public function getLatestEvents(Request $request) {
        $eventCount = $request->input('eventCount');
        $inquiryEvents = InquiryEvent::orderBy('id', 'desc')->take($eventCount)->get()->load('inquiry.customer')->load('inquirySourceSelection.inquirySource');
        return json_encode($inquiryEvents);
    }
}
