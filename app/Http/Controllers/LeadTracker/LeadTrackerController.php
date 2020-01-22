<?php
namespace App\Http\Controllers\LeadTracker;

use App\Events\VisitCustomerProfileEvent;
use App\Models\Inquiries\Inquiry;
use Faker\Provider\DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Models\Customers\Customer;
use App\Http\Controllers\Api\InquiryController;
use Illuminate\Support\Facades\Event;
use Laracasts\Utilities\JavaScript\JavaScriptFacade as JavaScript;
use DateTime as PHPDateTime;
use Carbon\Carbon;

class LeadTrackerController extends Controller
{
    private $InquiryRepository;
    private $TimerRepository;
    private $CustomerRepository;
    private $AccessControl;
    private $TeamAuth;
    private $InquiryController;

    function __construct(){
        $this->AccessControl = App::make('AccessControl');
        $this->TeamAuth = App::make('TeamAuth');
        $this->InquiryRepository = App::make('InquiryRepository');
        $this->TimerRepository = App::make('TimerRepository');
        $this->CustomerRepository = App::make('CustomerRepository');
        $this->InquiryController = App::make(InquiryController::class);
    }

    public function index() {
        $user = $this->TeamAuth->get_user();
        $userRole = $this->AccessControl->get_users_role();

        return view('inquiryCustomer/leadTracker')->with([
            'logged_in_id' => $user->id,
            'user_role' => $userRole
        ]);
    }

    /*
     * Function: View display for a specific inquiry profile
     * for a provided customer
     */
    public function profile(Customer $customer) {
        event(new VisitCustomerProfileEvent($customer));

        //Get user to return ID for saves
        $user = $this->TeamAuth->get_user();

        //Load inquiries for customer
        $inquiries = $customer->inquiries()->get();
        $customer->inquiries = $inquiries;
        return view('/inquiryCustomer/inquiryProfile')->with('customer', $customer)->with('logged_in_id', $user->id);
    }

    /*
     * Function: Retrieve all inquiries and customers from the
     * inquiry repository
     *
     * @return $allCustomerInfo array | all information needed for display
     * for each inquiry and customer
     */
    public function getAllInquiries(Request $request) {
        $startTimeInterval = $request->input('startTimeInterval');
        $userRole = $this->AccessControl->get_users_role();
        //If the user is not a manager, find the user's id for inquiry querying
        $user = $this->TeamAuth->get_user();
        $inquiriesToDisplay = $this->InquiryRepository->getInquiryList($userRole, $user->id, $startTimeInterval);
        $customersForInquiries = array();
        foreach($inquiriesToDisplay as $inquiry) {
            $customersForInquiries[] = $this->InquiryRepository->getCustomerForInquiries($inquiry);
        }

        $allCustomerInfo = array();
        foreach($customersForInquiries as $customer) {
            $allCustomerInfo[] = $this->getProfile($customer);
        }
        return $allCustomerInfo;
    }

    public function getAgentInquiries() {
        $currentDateTime = new PHPDateTime();
        $user = $this->TeamAuth->get_user();
        $agentTimers = $this->TimerRepository->retrieveTimersByAgent($user);
        foreach($agentTimers as $agentTimer) {
            if($agentTimer->completed == 1) {
                $agentTimer->inquiryEvent;
                $agentTimer->inquiryEvent->inquirySourceSelection->inquirySource;
                //Determine days ago that the inquiry timer was completed
                $completedDateTime = new PHPDateTime($agentTimer->inquiryEvent->provided_timestamp);
                $daysAgo = $currentDateTime->diff($completedDateTime)->format("%a");
                $agentTimer->daysAgo = $daysAgo;
            }
            else {
                //Load the inquiry and customer
                $agentTimer->inquiry;
                $agentTimer->inquiry->customer;
                $timerExpirationDateTime = new PHPDateTime($agentTimer->timer_expiration_datetime);
                $daysUntilExpiration = $timerExpirationDateTime->diff($currentDateTime)->format("%a");
                $agentTimer->daysUntil = $daysUntilExpiration;
            }
        }
        return $agentTimers;
    }

    /*
     * Function: Retrieve associated customer information that
     * will be displayed on the individual customer profile
     *
     * @param $customer Object | the provided customer used to
     * retrieve additional information for the return
     */
    private function getProfile(Customer $customer){

        foreach($customer->inquiries as $inquiry) {
            $inquiryInformation = $this->InquiryRepository->getCustomerInquiryInformation($inquiry->id);
            $customerStatus = $this->CustomerRepository->getCustomerStatus($inquiryInformation->id);
            $inquiryInformation->status = $customerStatus;
        }

        //Send PHP vars to JavaScript
        JavaScript::put([
            'customerId' => $customer['id'],
            'inquiryJSON' => $inquiryInformation
        ]);

        return $inquiryInformation;
    }

    public function getDashboardOverviews() {
        $dashboardInformation = array();
        //Get count of unclaimed inquiries
        $unclaimedInquiryCount = Inquiry::whereNull('user_account_id')->get()->count();
        //Get count of timers that are expiring today
        $carbonStartDate = new Carbon();
        $carbonEndDate = new Carbon();
        $carbonEndDate = $carbonEndDate->addDay(1);
        $upcomingTimersCount = $this->TimerRepository->retrieveTimersByTimePeriod($carbonStartDate->format('Y-m-d'), $carbonEndDate->format('Y-m-d'))->count();
        $dashboardInformation['unclaimedInquiryCount'] = $unclaimedInquiryCount;
        $dashboardInformation['contactToday'] = $upcomingTimersCount;
        $dashboardInformation['scheduledAppointments'] = '#';


        return json_encode($dashboardInformation);
    }

    public function releaseLead(Request $request) {
        $inquiryID = $request->input('inquiryID');
        $inquiry = Inquiry::findOrFail($inquiryID);
        $currentDatetime = new \DateTime();
        $currentDate = $currentDatetime->format('Y-m-d H:i:s');
        $inquiryAgents = $this->InquiryController->getInquiryAgents();

        try {
            $this->InquiryRepository->release($inquiry);
        } catch (\Exception $ex) {
            $inquiryAgents = false;
        }

        $inquiry = Inquiry::findOrFail($inquiryID);
        $user = $this->TeamAuth->get_user();

        if (!$inquiryAgents) {
            return \Response::json(false);
        }
        return \Response::json(\View::make('inquiryCustomer.inquiryAccordionBarSnippet', array('agents' => $inquiryAgents, 'selectedInquiry' => $inquiry, 'current_date' => $currentDate, 'logged_in_id' => $user->id))->render());
    }
}