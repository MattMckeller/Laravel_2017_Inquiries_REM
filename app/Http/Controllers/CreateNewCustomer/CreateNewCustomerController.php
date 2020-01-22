<?php

namespace App\Http\Controllers\CreateNewCustomer;

use App\Http\Controllers\Controller;
use App\Models\Customers\Customer;
use App\Models\Customers\EmailAddress;
use App\Models\Customers\Guarantor;
use App\Models\Customers\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class CreateNewCustomerController extends Controller {
    private $InquiryRepository;
    private $CustomerRepository;

    function __construct(){
        $this->InquiryRepository = App::make('InquiryRepository');
        $this->CustomerRepository = App::make('CustomerRepository');
    }

    /*
     * Function: Based on the selected contact type,
     * a specific form is presented to the user
     *
     * @param Request $request | inputs to determine
     * the appropriate form
     * @return redirect $redirectionURL | the url that is
     * redirected to based on input
     */
    public function directStartForm(Request $request) {
        $inquirySource = $request->input('inquirySource');
        switch ($inquirySource) {
            //Inquiry Customers
            case "email":
            case "phone":
            case "specialEvent":
            case "website":
                $inquiryCustomer = $request->input('inquiryCustomer');
                //Determine if the customer has an inquiry
                if ($inquiryCustomer == 'yes') {
                    //Redirect to specific form contact input
                    $redirectionUrl = $this->findInquirySourceRedirectionURL($inquirySource);
                    return redirect($redirectionUrl);
                }
                break;
            //Leasing Customers
            case "walkIn":
                $leasingCustomerAppointment = $request->input('leasingCustomerAppointment');
                //Determine if the customer has an appointment
                if($leasingCustomerAppointment == "no") {
                    //Redirect to specific form contact input
                    $redirectionUrl = $this->findInquirySourceRedirectionURL($inquirySource);
                    return redirect($redirectionUrl);
                }
                //If the customer has an appointment, redirect to appointment list
                else {
                    return Redirect::action('Appointment\AppointmentsController@appointmentsList');
                }
                break;
            default:
                return back();
                break;
        }
        return back();
    }

    /*
     * Function: Determine the user's appropriate contact
     * information form based on contact type
     *
     * @param $inquirySource | string redirect user to appropriate form
     */
    private function findInquirySourceRedirectionURL($inquirySource) {
        //Determine contact type-specific form views
        switch ($inquirySource) {
            case "email":
                return '/createNewCustomer/emailInquiry';
                break;
            case "phone":
                return '/createNewCustomer/phoneInquiry';
                break;
            case "specialEvent":
                return '/createNewCustomer/specialEventInquiry';
                break;
            case "website":
                return '/createNewCustomer/websiteInquiry';
            case "walkIn":
                return '/createNewCustomer/appointmentCustomer';
                break;
            default:
                return '/';
                break;
        }
    }

    /*
     * Function: Check for any customer matches based on
     * the given input
     *
     * @param Request $request | form input provided with
     * customer information
     */
    public function checkCustomerMatches(Request $request) {
        //Check customer name, email, and phone for existing inquiries
        $customerInformation = array(
            'firstName' => $request->input('firstName'),
            'lastName' => $request->input('lastName'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone')
        );

        $matchedCustomers = $this->CustomerRepository->searchCustomers($customerInformation, '.95');
        return $matchedCustomers;
    }

    /*
     * Function: Based on the provided contact form,
     * verify all required input is provided and redirect
     * to customer preferences with data
     *
     * @param Request $request | form input provided from
     * contact form
     * @return redirect $redirectionURL | the url containing the
     * customer preferences form for the user. Previous form input
     * also included
     */
    public function directCustomerPreferences(Request $request) {
        //Determine the customer type provided
        $appointmentCustomer = $request->input('appointmentCustomer', false);
        if($appointmentCustomer) {
            $contactInformationRequest = new CreateAppointmentRequest();
            $this->validate($request, $contactInformationRequest->contactInformationRules());
        }
        else {
            //Validate the provided contact information against custom request rules
            $contactInformationRequest = new CreateInquiryRequest();
            $this->validate($request, $contactInformationRequest->contactInformationRules());
        }

        return view('/createNewCustomer/customerPreferences', $request);
    }

    /*
     * Function: After all input for a customer has been provided,
     * submit information for save
     *
     * @param Request $request | form input from contact info and
     * customer preferences
     * @return $success array | success of customer save attempt
     * and any errors that may be returned
     */
    public function submitQualifiedInquiry(Request $request) {
        //Validate the provided customer preferences against custom request rules
        $customerPreferenceRequest = new CreateInquiryRequest();
        $validationAttempt = Validator::make($request->all(), $customerPreferenceRequest->qualifiedInquiryRules());
        if($validationAttempt->fails()) {
            return redirect('createNewCustomer/' . $request->input('inquirySource') . 'Inquiry')->withErrors($validationAttempt)->withInput();
        }

        //Attempt to create the inquiry and associated information in the DB through the inquiry repository
        $customer = $this->InquiryRepository->createQualifiedInquiry($request);
        //Redirect to create new customer with success flash data
        return redirect('/createNewCustomer')->with([
            'newCustomerAdded' => 'true',
            'firstName' => $customer['first_name'],
            'lastName' => $customer['last_name']
        ]);
    }

    function submitUnqualifiedInquiry(Request $request) {
        //Validate the provided unqualified inquiry information against custom request rules
        $unqualifiedRequest = new CreateInquiryRequest();
        $this->validate($request, $unqualifiedRequest->unqualifiedInquiryRules());

        $customer = $this->InquiryRepository->createUnqualifiedInquiry($request);

        //Redirect to create new customer with success flash data
        return redirect('/createNewCustomer')->with([
            'newCustomerAdded' => 'true',
            'firstName' => $customer['first_name'],
            'lastName' => $customer['last_name']
        ]);
    }

    public function addGuarantorToCustomer(Request $request) {
        $saveAttempt = array(
            'errors' => array(),
            'success' => true
        );

        $guarantorInformation = $request->input('guarantorInformation');
        $customerID = $guarantorInformation['customerID'];
        $firstName = $guarantorInformation['firstName'];
        $lastName = $guarantorInformation['lastName'];
        $guarantorEmailAddress = $guarantorInformation['emailAddress'];
        $guarantorPhoneNumber = $guarantorInformation['phoneNumber'];

        //Remove any previously associated guarantors
        Customer::find($customerID)->guarantors()->detach();

        $guarantor = new Guarantor();
        $guarantor->first_name = $firstName;
        $guarantor->last_name = $lastName;
        $guarantor->is_active = 1;
        $guarantor->save();

        if(!empty($guarantorEmailAddress)) {
            $emailAddress = new EmailAddress();
            $emailAddress->email_address = $guarantorEmailAddress;
            $emailAddress->is_active = 1;
            $emailAddress->is_primary = 1;
            $emailAddress->guarantor()->associate($guarantor)->save();
        }
        if(!empty($guarantorPhoneNumber)) {
            $phoneNumber = new PhoneNumber();
            $phoneNumber->phone_number = $guarantorPhoneNumber;
            $phoneNumber->is_active = 1;
            $phoneNumber->is_primary = 1;
            $phoneNumber->guarantor()->associate($guarantor)->save();
        }

        //Associate guarantor to customer
        $customer = Customer::where('id', $customerID)->firstOrFail();
        $guarantor->customers()->save($customer);

        $createdGuarantor = Guarantor::where('id', $guarantor->id)->get()->load('phoneNumbers')->load('emailAddresses');
        $saveAttempt['data'] = $createdGuarantor;
        return $saveAttempt;
    }
}

/*
 * Class: Extends the request class to
 * include rules associated with each type of
 * request to create a new inquiry
 */
class CreateInquiryRequest extends Request {
    public function contactInformationRules() {
        return [
            'email' => "required_if:inquirySource,email | email",
            'phone' => "required_if:inquirySource,phone | phoneRegex",
            'firstName' => "required_if:inquirySource,specialEvent | nameRegex",
            'lastName' => "required_if:inquirySource,specialEvent | nameRegex",
            "middleInitial" => "regex: /[a-zA-Z]/",
            "streetAddress" => "addressRegex",
            "streetAddress2" => "addressRegex",
            "city" => "addressRegex",
            "state" => "state",
            "country" => "country",
            "zip" => "zip",
            "birthday" => "date",
            "gender" => "regex: /^[a-zA-Z0-9 ,.'-]+$/",
            "starRatingSelected" => "required | integer | between: 1,5"
        ];
    }

    public function qualifiedInquiryRules() {
        return [
            "inquirySource" => "required | nameRegex",
            "email" => "required_if:inquirySource,email | email",
            "phone" => "required_if:inquirySource,phone | phoneRegex",
            "firstName" => "required_if:inquirySource,specialEvent | nameRegex",
            "lastName" => "required_if:inquirySource,specialEvent | nameRegex",
            "middleInitial" => "regex: /[a-zA-Z]/",
            "streetAddress" => "addressRegex",
            "streetAddress2" => "addressRegex",
            "city" => "addressRegex",
            "state" => "state",
            "country" => "country",
            "zip" => "zip",
            "birthday" => "date",
            "gender" => "regex: /^[a-zA-Z0-9 ,.'-]+$/",
            'locationPreferences.*' => 'integer',
            'brandExposure.*' => 'integer',
            'desired_bedroom_count.*' => 'integer',
            'roommate_matching' => 'integer',
            'has_pet' => 'integer',
            'wants_furniture' => 'integer',
            'wants_utilities' => 'integer',
            'customerAppointment' => 'integer',
            'max_budget' => 'numeric'
        ];
    }

    public function unqualifiedInquiryRules() {
        return [
            'email' => "required_without_all:phone,firstName,lastName",
            'phone' => "required_without_all:email,firstName,lastName",
            'firstName' => "required_without_all:phone,email,lastName",
            'lastName' => "required_without_all:phone,email,firstName",
            'inquirySource' => 'required',
            'reason' => 'required',
            'noteText' => 'required|min:10'
        ];
    }
}

class CreateAppointmentRequest extends Request {
    public function contactInformationRules()
    {
        return [
            'email' => "required | email",
            'phone' => "phoneRegex",
            'firstName' => "nameRegex",
            'lastName' => "nameRegex",
            "middleInitial" => "regex: /[a-zA-Z]/",
            "streetAddress" => "addressRegex",
            "streetAddress2" => "addressRegex",
            "city" => "addressRegex",
            "state" => "state",
            "country" => "country",
            "zip" => "zip",
            "birthday" => "date",
            "gender" => "regex: /^[a-zA-Z0-9 ,.'-]+$/",
        ];
    }
}