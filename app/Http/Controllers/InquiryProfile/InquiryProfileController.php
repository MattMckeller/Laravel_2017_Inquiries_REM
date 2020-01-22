<?php

namespace App\Http\Controllers\InquiryProfile;

use App\Http\Controllers\Api\InquiryController;
use App\Http\Controllers\Controller;
use App\Events\VisitCustomerProfileEvent;
use App\Models\Customers\Customer;
use App\Models\Customers\Note;
use App\Models\Inquiries\Inquiry;
use App\Models\Inquiries\InquiryNote;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use App\Http\Controllers\Api\AnswerController;
use DateTime;
use Illuminate\Support\Collection;
use App\Models\Auth\Team\UserAccountInformation;
use App\Models\Auth\Team\UserAccount;

class InquiryProfileController extends Controller
{
    private $InquiryRepository;
    private $TimerRepository;
    private $AccessControl;
    private $TeamAuth;
    private $EventSearchRepository;
    private $CustomerRepository;
    private $InquiryController;

    function __construct(){
        $this->AccessControl = App::make('AccessControl');
        $this->TeamAuth = App::make('TeamAuth');
        $this->InquiryRepository = App::make('InquiryRepository');
        $this->TimerRepository = App::make('TimerRepository');
        $this->EventSearchRepository = App::make('EventSearchRepository');
        $this->CustomerRepository = App::make('CustomerRepository');
        $this->InquiryController = App::make(InquiryController::class);
    }

    /*
     * Function: View display for a specific inquiry profile
     * for a provided customer
     */
    public function index(Customer $customer)
    {
        event(new VisitCustomerProfileEvent($customer));

        //Get user to return ID for saves
        $user = $this->TeamAuth->get_user();

        //Return current datetime for inquiry claim comparisons
        $currentDatetime = new DateTime();
        $currentDate = $currentDatetime->format('Y-m-d H:i:s');

        //Load inquiries for customer
        $inquiries = $customer->inquiries()->get()->load('userAccount.userAccountInformation');
        $appointments = $customer->appointments()->get()->load('appointmentEvents')->load('userAccountInformation');

        //Determine the status of the customer to redirect
        $customerStatus = $this->CustomerRepository->getCustomerStatus($customer->id);
        if ($customerStatus == 'lease' || $customerStatus == 'unknown') {
            return redirectEleaseProfile($customer);
        }

        $customer->inquiries = $inquiries;
        $customer->appointments = $appointments;

        $needsAgents = $inquiries->map(function($inquiry, $key) use ($currentDate) {
            if ($inquiry->user_account_id && ($inquiry->agent_claim_expiration > $currentDate)) {
                return false;
            }
            return true;
        });

        $inquiryAgents = $needsAgents ? $this->InquiryController->getInquiryAgents() : false;

        return view('/inquiryCustomer/inquiryCustomerProfile')
            ->with('customer', $customer)
            ->with('logged_in_id', $user->id)
            ->with('current_date', $currentDate)
            ->with('agents', $inquiryAgents);
    }

    public function claimInquiry(Request $request) {
        $inquiryID = $request->input('inquiryID');
        $inquiry = Inquiry::where('id', $inquiryID)->firstOrFail();
        $this->InquiryRepository->temporaryClaim($inquiry);
        //Load the updated inquiry after the claim
        $updatedInquiry = Inquiry::where('id', $inquiryID)->firstOrFail()->load('userAccount.userAccountInformation');

        return response($updatedInquiry, 201);
    }

    public function retrieveInquiryTimers(Request $request) {
        $currentDateTime = new DateTime();

        $inquiryID = $request->input('inquiryID');
        $inquiry = Inquiry::where('id', $inquiryID)->firstOrFail();
        $inquiryTimers = $this->TimerRepository->retrieveTimersForInquiry($inquiry);
        //Retrieve additional information for all inquiry timers
        foreach($inquiryTimers as $inquiryTimer) {
            //Load additional information for the timer
            $inquiryTimer->timerContactType;
            $inquiryTimer->timerConfig;
            //Load completed-specific timer information
            if($inquiryTimer->completed == 1) {
                $inquiryTimer->inquiryEvent;
                $inquiryTimer->inquiryEvent->inquirySourceSelection->inquirySource;
                //Determine days ago that the inquiry timer was completed
                $completedDateTime = new DateTime($inquiryTimer->inquiryEvent->provided_timestamp);
                $daysAgo = $currentDateTime->diff($completedDateTime)->format("%a");
                $inquiryTimer->daysAgo = $daysAgo;
            }
            else {
               $expirationDateTime = new DateTime($inquiryTimer->timer_expiration_datetime);
               $daysUntilExpiration = $expirationDateTime->diff($currentDateTime)->format("%a");
               $inquiryTimer->daysUntilExpiration = $daysUntilExpiration;
            }
        }
        return $inquiryTimers;
    }

    public function getAllEvents() {
        $allEvents = array();
        //Push all events throughout system onto all events array
        $events = $this->EventSearchRepository->getAllEvents();
        foreach($events as $event) {
            $eventArray = (array)$event;
            array_push($allEvents, $eventArray);
        }
        $timers = $this->TimerRepository->retrieveApproachingTimers();
        foreach($timers as $timer) {
            //Retrieve the customer for the corresponding timer
            $customer = Inquiry::findOrFail($timer['inquiry_id'])->customer;
            $modifiedTimerArray = array(
                'type' => "TimerEvent",
                'name' => 'TimerEvent',
                'id' => $timer['id'],
                'inquiry_id' => $timer['inquiry_id'],
                'customer' => $customer,
                'timestamp' => $timer['timer_expiration_datetime'],
                'valid_start_date' => $timer['valid_start_date'],
                'completed' => $timer['completed'],
                'is_active' => $timer['is_active'],
                'display_name' => $timer['display_name'],
                'inquiry_event_id' => $timer['inquiry_event_id'],
                'timer_config_id' => $timer['timer_config_id'],
                'timer_contact_type_id' => $timer['timer_contact_type_id']
            );
            array_push($allEvents, $modifiedTimerArray);
        }

        usort($allEvents, function($a, $b) {
            $t1 = strtotime($a['timestamp']);
            $t2 = strtotime($b['timestamp']);
            return $t2 - $t1;
        });
        return $allEvents;
    }

    public function getCustomerEvents(Request $request) {
        $customer_id = $request->input('customer_id', false);
        $inquiry_id = $request->input('inquiry_id', false);

        $events = $this->EventSearchRepository->getCustomerEvents($customer_id);
        $allEvents = array();

        $phoneContactCount = 0;
        $successfulPhoneContact = 0;
        $unsuccessfulPhoneContact = 0;
        $emailContactCount = 0;
        $successfulEmailContact = 0;
        $unsuccessfulEmailContact = 0;

        foreach($events['inquiryEvents'] as $inquiryEvent) {
            $event['eventType'] = 'InquiryEvent';
            $event['timestamp'] = $inquiryEvent->provided_timestamp;
            $event['name'] = $inquiryEvent->inquirySourceSelection->inquirySource->display_name;
            $event['agent_contacted'] = $inquiryEvent->agent_contacted;
            $event['note'] = $inquiryEvent->inquiryNote ? $inquiryEvent->inquiryNote->text: null;

            $agent = UserAccountInformation::find($inquiryEvent->user_account_id);
            $event['user_account_id'] = $agent ? $agent->id: '';
            $event['user_account_name'] = $agent ? $agent->first_name . " " . $agent->last_name : '';

            array_push($allEvents, $event);

            //Determine successful/unsuccessful contact percentages
            switch($event['name']) {
                case "Email":
                    //Increment the count of total email contacts
                    $emailContactCount++;
                    if($inquiryEvent['successful_contact'] == true) {
                        $successfulEmailContact++;
                    }
                    else {
                        $unsuccessfulEmailContact++;
                    }
                    break;
                case "Phone":
                    $phoneContactCount++;
                    if($inquiryEvent['successful_contact'] == true) {
                        $successfulPhoneContact++;
                    }
                    else {
                        $unsuccessfulPhoneContact++;
                    }
                    break;
            }
        }
        unset($event);

        //Get all inquiry notes associated with a customer that have been created without an event
        $inquiryNotes = InquiryNote::where('inquiry_id', $inquiry_id)->whereNull('inquiry_event_id')->orderBy('created_at', 'DESC')->get()->load('userAccountInformation');
        foreach($inquiryNotes as $inquiryNote) {
            $event['eventType'] = 'InquiryNote';
            $event['timestamp'] = (string)$inquiryNote->created_at;
            $event['user_account_id'] = (!empty($inquiryNote['agent_id'])) ? $inquiryNote['agent_id'] : '';
            $event['user_account_name'] = (!empty($inquiryNote->userAccountInformation)) ? $inquiryNote->userAccountInformation->first_name . ' ' . $inquiryNote->userAccountInformation->last_name : '';
            $event['name'] = 'Note';
            $event['note'] = $inquiryNote->text;

            array_push($allEvents, $event);
        }
        unset($event);

        //Get all standard notes for the customer
        $generalNotes = Note::where('customer_id', $customer_id)->get()->load('userAccount.userAccountInformation');
        foreach($generalNotes as $generalNote) {
            $event['eventType'] = 'GeneralNote';
            $event['name'] = 'Note';
            $event['timestamp'] = (string)$generalNote->created_at;
            $event['user_account_id'] = $generalNote['user_account_id'];
            $event['note'] = $generalNote->text;

            array_push($allEvents, $event);
        }

        //inquiry claiming events
        foreach($events['inquiryClaimingEvents'] as $inquiryClaimingEvent) {
            $event['eventType'] = 'InquiryClaimingEvent';
            $event['valid_start_date'] = $inquiryClaimingEvent->timestamp;
            $event['timestamp'] = $inquiryClaimingEvent->expiration_timestamp;
            $event['name'] = $inquiryClaimingEvent->is_claim ? "Claimed" : "Not claimed";

            $agent = UserAccount::find($inquiryClaimingEvent->user_account_id)->userAccountInformation;
            $event['user_account_id'] = $agent ? $agent->id: '';
            $event['user_account_name'] = $agent ? $agent->first_name . " " . $agent->last_name : '';

            array_push($allEvents, $event);
        }
        unset($event);

        //Appointment events
        foreach($events['appointmentEvents'] as $appointmentEvent) {
            $event['eventType'] = 'AppointmentEvent';
            $event['timestamp'] = $appointmentEvent->created_at;
            $event['name'] = $appointmentEvent->event_type;

            $agent = UserAccountInformation::find($appointmentEvent->user_account_information_id);
            $event['user_account_id'] = $agent ? $agent->id: '';
            $event['user_account_name'] = $agent ? $agent->first_name . " " . $agent->last_name : '';

            array_push($allEvents, $event);
        }
        unset($event);

        //Timers
        foreach($events['timerEvents'] as $timer) {
            $event['eventType'] = 'TimerEvent';
            $event['valid_start_date'] = $timer->valid_start_date;
            $event['timestamp'] = $timer->timer_expiration_datetime;
            $event['name'] = $timer->display_name;

            array_push($allEvents, $event);
        }
        unset($event);

        $allEvents['successfulPhonePercentage'] = ($phoneContactCount != 0) ? round(($successfulPhoneContact / $phoneContactCount) * 100, 0) : 'N/A';
        $allEvents['successfulEmailPercentage'] = ($emailContactCount != 0) ? round(($successfulEmailContact / $emailContactCount) * 100, 0) : 'N/A';

        return $allEvents;
    }
}
