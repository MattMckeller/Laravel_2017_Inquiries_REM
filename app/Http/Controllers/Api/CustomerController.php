<?php

namespace App\Http\Controllers\Api;

use App\Models\Customers\Address;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerStory;
use App\Models\Customers\EmailAddress;
use App\Models\Customers\Guarantor;
use App\Models\Customers\LeasingReason;
use App\Models\Customers\PhoneNumber;
use App\Models\Customers\RequestedRoommateGroup;
use App\Models\Inquiries\Answer;
use App\Models\Inquiries\Inquiry;
use App\Models\Inquiries\Question;
use App\Models\Inquiries\QuestionOptionSelection;
use App\Models\Inventory\UnitStyle;
use App\Repositories\Customers\GuarantorRepository;
use Illuminate\Http\Request;
use App\Libraries\DoubleMetaPhone;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\Controller;
use App\Models\Customers\Note;

class CustomerController extends Controller
{
    private $AccessControl;
    private $CustomerRepository;
    private $TeamAuth;
    private $errors;

    function __construct(){
        $this->AccessControl = App::make('AccessControl');
        $this->TeamAuth = App::make('TeamAuth');
        $this->CustomerRepository = App::make('CustomerRepository');
    }

    private $rules = array(
        'emailAddress' => 'email',
        'phoneNumber' => 'phoneRegex',
        'streetAddress1' => 'addressRegex',
        'streetAddress2' => 'addressRegex',
        'city' => 'addressRegex',
        'state' => 'state',
        'country' => 'country',
        'zip' => 'zip'
    );

    public function validateInput($data) {
        //Make a new validator object
        $validator = Validator::make($data, $this->rules);
        //Check for validation fail
        if($validator->fails()) {
            //Store errors and return
            $this->errors = $validator->errors();
            return false;
        }
        return true;
    }

    public function errors() {
        return $this->errors;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->getAppointmentCustomers($request);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAppointmentCustomers(Request $request) {
        $customerInformation = array();

        $customerInformation['id'] = $request->input('id', false);
        $customerInformation['first_name'] = $request->input('first_name', false);
        $customerInformation['last_name'] = $request->input('last_name', false);
        $customerInformation['middle_initial'] = $request->input('middle_initial', false);
        $customerInformation['gender'] = $request->input('gender', false);
        $customerInformation['birthday'] = $request->input('birthday', false);
        $customerInformation['with_appointments'] = $request->input('with_appointments', false);
        $customerInformation['offset'] = $request->input('offset', false);
        $customerInformation['limit'] = $request->input('limit', false);
        $customerInformation['order'] = $request->input('order', false);

        if ($customers = $this->CustomerRepository->getAppointmentCustomers($customerInformation)) {

            return response(json_encode(array('customers' => $customers)), 201);
        }
        else
            return response(json_encode(array('customers' => 'There were no customers matching that description')), 500);

    }

    //Return all customers to customerList.blade.php
    public function getCustomersList($firstName = false, $lastName = false) {
        $customers = $this->CustomerRepository->getCustomersList($firstName, $lastName);
        dd($firstName);
        return $customers;
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public static function store(Request $request)
    {
        //Process Double MetaPhones for storage
        $doubleMetaPhone = new DoubleMetaPhone();
        $doubleMetaPhoneFirstNameResult = $doubleMetaPhone->CalculateDoubleMetaPhone($request->input('firstName'));
        $doubleMetaPhoneLastNameResult = $doubleMetaPhone->CalculateDoubleMetaPhone($request->input('lastName'));
        unset($doubleMetaPhone);

        $customer = new Customer();
        $customer->first_name = $request->input('firstName', null);
        $customer->last_name = $request->input('lastName', null);
        $customer->first_name_dm_first = $doubleMetaPhoneFirstNameResult['primary'];
        $customer->first_name_dm_second = $doubleMetaPhoneFirstNameResult['secondary'];
        $customer->last_name_dm_first = $doubleMetaPhoneLastNameResult['primary'];
        $customer->last_name_dm_second = $doubleMetaPhoneLastNameResult['secondary'];
        $customer->middle_initial = $request->input('middleInitial', null);
        $customer->gender = $request->input('gender', null);
        $customer->birthday = $request->input('birthday', null);
        $customer->saveOrFail();

        if ($guarantorInformation = $request->input('guarantorInformation', null)) {
            $guarantorRepository = new GuarantorRepository();
            $guarantorFirstName = array_key_exists('firstName', $guarantorInformation) ? $guarantorInformation['firstName'] : null;
            $guarantorLastName = array_key_exists('firstName', $guarantorInformation) ? $guarantorInformation['lastName'] : null;
            $guarantorAddress = array_key_exists('addresses', $guarantorInformation) ? $guarantorInformation['addresses'] : null;
            $guarantorPhoneNumber = array_key_exists('phoneNumbers', $guarantorInformation) ? $guarantorInformation['phoneNumbers'] : null;
            $guarantorEmailAddress = array_key_exists('emailAddresses', $guarantorInformation) ? $guarantorInformation['emailAddresses'] : null;
            $guarantor = null;

            if ($guarantorFirstName && $guarantorLastName) {
                $guarantor = $guarantorRepository->saveGuarantor($guarantorFirstName, $guarantorLastName);
            }
            if ($guarantor) {
                $guarantorRepository->attachAddress($guarantorAddress, $guarantor);
                $guarantorRepository->attachPhone($guarantorPhoneNumber, $guarantor);
                $guarantorRepository->attachEmail($guarantorEmailAddress, $guarantor);
            }
        }


        return response($customer, 201);
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

    public function searchCustomers(Request $request) {
        $customerInformation = array(
            'firstName' => $request->input('firstName'),
            'lastName' => $request->input('lastName')
        );
        $matchedCustomers = $this->CustomerRepository->searchCustomers($customerInformation, .85);
        return $matchedCustomers;
    }

    public function getCustomerInformation(Request $request) {
        $customerID = $request->input('customerID');
        //Retrieve inquiry to determine the customer id for the inquiry
        $inquiry = Inquiry::where('customer_id', $customerID)->first();

        //Retrieve the customer from the inquiry
        $customer = Customer::where('id', $customerID)->firstOrFail()
            ->load('addresses')
            ->load(['appointments.appointmentEvents', 'appointments.userAccountInformation'])
            ->load('customerStory')
            ->load('emailAddresses')
            ->load('leasingReason')
            ->load('organizations')
            ->load('phoneNumbers')
            ->load('incentives')
            ->load('guarantors.emailAddresses', 'guarantors.phoneNumbers', 'guarantors.addresses')
            ->load('locationPreferences')
            ->load('schools')
            ->load('applications');

        //Retrieve all roommates from the roommate group that are not the current customer
        $requestedRoommateGroupID = $customer->requested_roommate_group_id;
        $requestedRoommates = Customer::where('requested_roommate_group_id', $requestedRoommateGroupID)->whereNotNull('requested_roommate_group_id')->where('id', '!=', $customerID)->get();
        $customer->requestedRoommates = $requestedRoommates;

        //Store inquiry labels
        if(!empty($inquiry)) {
            $customer->inquiryLabels = $inquiry->inquiryLabel;
            //Store inquiry events
            $customer->inquiryEvents = $inquiry->inquiryEvents;
        }

        $inquiryPreferences = array();
        $customerAnswers = Answer::where('customer_id', $customerID)->get();
        foreach($customerAnswers as $answer) {
            //Retrieve supporting information for each answer provided
            $questionOptionSelection = $answer->questionOptionSelection;
            $question = $questionOptionSelection->question;
            //Store the option choice or answer field if integer/text
            $optionChoiceAnswer = NULL;
            if(empty($answer->answer_int) && empty($answer->answer_text)) {
                $optionChoice = $questionOptionSelection->optionChoice;
                $optionChoiceAnswer = $optionChoice->option_choice_name;
            }
            else {
                //Check for integer or text provided
                if(!empty($answer->answer_int)) {
                    $optionChoiceAnswer = $answer->answer_int;
                }
                else if(!empty($answer->answer_text)) {
                    $optionChoiceAnswer = $answer->answer_text;
                }
            }

            //Push question and answer onto array for return
            $inquiryPreferences[] = array(
                'question' => $question->question_name,
                'question_subtext' => $question->question_subtext,
                'answer' => $optionChoiceAnswer,
                'option_choices' => $answer->questionOptionSelection->question->optionChoice,
                'question_type' => $question->questionType
            );
            //Store all inquiry preferences
            $customer->inquiryPreferences = $inquiryPreferences;
        }
        //Store brand exposures
        if(!empty($inquiry)) {
            $customer->brandExposures = $inquiry->brandExposures;
            //Store inquiry notes
            $customer->inquiryNotes = $inquiry->inquiryNote;
            //Store inquiry
            $customer->inquiries = array($inquiry);
            //Store all associated inquiry locations
            $customer->locations = $inquiry->locations;
        }

        //Add any additional notes associated with the customer
        $customer->generalNotes = $customer->notes;

        //Retrieve the current status of the customer
        $status = $this->getCustomerStatus($customer->id);
        $customer->status = $status;

        return $customer;
    }

    public function getCustomerStatus($customerID = false) {
        return $this->CustomerRepository->getCustomerStatus($customerID);
    }

    public function updateCustomer(Request $request) {
        $saveStatus = array(
            'errors' => array(),
            'success' => true
        );

        $customerInformation = $request->input('customerInformation');
        //Check for street address being provided differently. Inconsistencies exist.
        if(empty($customerInformation['streetAddress'])) {
            //Check for variations of the street address naming convention
            if(!empty($customerInformation['streetAddress1'])) {
                $customerInformation['streetAddress'] = $customerInformation['streetAddress1'];
                //Unset the incorrectly named street address
                unset($customerInformation['streetAddress1']);
            }
        }
        //Run validation rules against the provided data
        $validationAttempt = $this->validateInput($customerInformation);

        if($validationAttempt == true) {
            $customerID = $customerInformation['customerID'];
            //Retrieve existing information
            $customer = Customer::where('id', $customerID)->firstOrFail()->load('emailAddresses')->load('phoneNumbers')->load('addresses')->load('customerStory')->load('leasingReason');
            $customerEmailAddresses = $customer->emailAddresses;
            $customerPhoneNumbers = $customer->phoneNumbers;
            $customerAddresses = $customer->addresses;

            //Check for updated customer name
            if(!empty($customerInformation['firstName'])) {
                $customer->first_name = $customerInformation['firstName'];
                $customer->save();
            }
            if(!empty($customerInformation['lastName'])) {
                $customer->last_name = $customerInformation['lastName'];
                $customer->save();
            }

            //Check for updated email address
            if(!empty($customerInformation['emailAddress'])) {
                //Find current email address to change active and primary status
                foreach($customerEmailAddresses as $currentEmailAddress) {
                    if($currentEmailAddress->is_active == 1 && $currentEmailAddress->is_primary == 1) {
                        $currentEmailAddress->is_active = 0;
                        $currentEmailAddress->is_primary = 0;
                        $currentEmailAddress->save();
                    }
                }
                //Save new email address
                $newEmailAddress = new EmailAddress();
                $newEmailAddress->customer_id = $customerID;
                $newEmailAddress->is_active = 1;
                $newEmailAddress->is_primary =1;
                $newEmailAddress->email_address = $customerInformation['emailAddress'];
                $saved = $newEmailAddress->save();
                if(!$saved) {
                    array_push($saveStatus['errors'], 'email address save failed');
                    $saveStatus['success'] = false;
                }
            }
            //Check for updated phone number
            if(!empty($customerInformation['phoneNumber'])) {
                foreach($customerPhoneNumbers as $currentPhoneNumber) {
                    if($currentPhoneNumber->is_active == 1 && $currentPhoneNumber->is_primary == 1) {
                        $currentPhoneNumber->is_active = 0;
                        $currentPhoneNumber->is_primary = 0;
                        $currentPhoneNumber->save();
                    }
                }
                //Save new phone number
                $newPhoneNumber = new PhoneNumber();
                $newPhoneNumber->customer_id = $customerID;
                $newPhoneNumber->is_active = 1;
                $newPhoneNumber->is_primary = 1;
                $newPhoneNumber->phone_number = $customerInformation['phoneNumber'];
                $saved = $newPhoneNumber->save();
                if(!$saved) {
                    array_push($saveStatus['errors'], 'phone number save failed');
                    $saveStatus['success'] = false;
                }
            }

            if(!empty($customerInformation['streetAddress'])) {
                foreach($customerAddresses as $currentAddress) {
                    if($currentAddress->is_active == 1 && $currentAddress->is_primary == 1) {
                        $currentAddress->is_active = 0;
                        $currentAddress->is_primary = 0;
                        $currentAddress->save();
                    }
                }
                //Save new address
                $newAddress = new Address();
                $newAddress->customer_id = $customerID;
                $newAddress->is_primary = 1;
                $newAddress->is_active = 1;
                $newAddress->street_address_1 = $customerInformation['streetAddress'];
                $newAddress->city = $customerInformation['city'];
                $newAddress->state = $customerInformation['state'];
                $newAddress->zip = $customerInformation['zip'];
                $newAddress->country = $customerInformation['country'];
                if(!empty($customerInformation['streetAddress2'])) {
                    $newAddress->street_address_2 = $customerInformation['streetAddress2'];
                }
                $saved = $newAddress->save();
                if(!$saved) {
                    array_push($saveStatus['errors'], 'address save failed');
                    $saveStatus['success'] = false;
                }
            }

            //Check for new customer story
            if(!empty($customerInformation['customerStory'])) {
                //Detach the old customer story and save the new entry
                $customerStoryDetachStatus = $customer->customerStory()->dissociate();
                if(!$customerStoryDetachStatus) {
                    array_push($saveStatus['errors'], 'Unable to remove the previous customer story');
                    $saveStatus['success'] = false;
                }

                //Save new customer story
                $customerStory = new CustomerStory();
                $customerStory->description = $customerInformation['customerStory'];
                $customerStory->save();

                $customer->customerStory()->associate($customerStory);
                $customer->save();
            }

            //Check for new max budget
            if(!empty($customerInformation['maxBudget'])) {
                $answerExists = false;
                //Update the answers for the customer
                $customerAnswers = Answer::where('customer_id', $customerID)->get()->load('questionOptionSelection.question');
                foreach($customerAnswers as $customerAnswer) {
                    if($customerAnswer->questionOptionSelection->question->question_subtext == 'max_budget') {
                        $answerExists = true;
                        $budgetOptionChoiceID = $customerAnswer->questionOptionSelection->optionChoice->id;
                        //Delete the answer and store a new answer based on the provided input
                        $customerAnswer->delete();

                        //Create new answer for the customer
                        $newCustomerAnswer = new Answer();
                        $newCustomerAnswer->question_option_id = $budgetOptionChoiceID;
                        $newCustomerAnswer->answer_int = $customerInformation['maxBudget'];
                        $newCustomerAnswer->customer()->associate($customerID);
                        $newCustomerAnswer->save();
                    }
                }
                //If no customer answer exists for max budget, create new answer
                if($answerExists == false) {
                    //Retrieve the option choice id for the max budget
                    $budgetQuestion = Question::where('question_subtext', 'max_budget')->firstOrFail()->load('optionChoice');
                    $budgetOptionChoiceID = $budgetQuestion->optionChoice[0]['id'];
                    $questionOptionChoiceID = QuestionOptionSelection::where('question_id', $budgetQuestion->id)->where('option_choice_id', $budgetOptionChoiceID)->firstOrFail();

                    //Create new answer for the customer
                    $newCustomerAnswer = new Answer();
                    $newCustomerAnswer->question_option_id = $questionOptionChoiceID->id;
                    $newCustomerAnswer->answer_int = $customerInformation['maxBudget'];
                    $newCustomerAnswer->customer()->associate($customerID);
                    $newCustomerAnswer->save();
                }
            }

            //Check for new leasing reason quote
            if(!empty($customerInformation['leasingReasonQuote'])) {
                //Detach the old customer story and save the new entry
                $leasingReasonDetachStatus = $customer->leasingReason()->dissociate();
                if(!$leasingReasonDetachStatus) {
                    array_push($saveStatus['errors'], 'Unable to remove the previous leasing reason');
                    $saveStatus['success'] = false;
                }

                //Save new customer leasing reason
                $leasingReason = new LeasingReason();
                $leasingReason->description = $customerInformation['leasingReasonQuote'];
                $leasingReason->save();

                $customer->leasingReason()->associate($leasingReason);
                $customer->save();
            }

            if(!empty($customerInformation['locations'])) {
                //Get all current location preferences for customer
                $customer = Customer::where('id', $customerID)->firstOrFail();
                $locationDetachStatus = $customer->locationPreferences()->detach();

                if(!$locationDetachStatus) {
                    array_push($saveStatus['errors'], 'unable to remove location preference associations');
                    $saveStatus['success'] = false;
                }

                $customer->locationPreferences()->attach($customerInformation['locations']);
            }

            //Retrieve customer after information has been updated
            $updatedCustomer = Customer::where('id', $customerID)->firstOrFail()
                ->load('emailAddresses')
                ->load('addresses')
                ->load('phoneNumbers')
                ->load('customerStory')
                ->load('leasingReason')
                ->load('answers.questionOptionSelection.question')
                ->load('locationPreferences');
            $saveStatus['data'] = $updatedCustomer;

            //update guarantor information if passed in
            if ($guarantorInformation = array_key_exists('guarantorInformation', $customerInformation) ? $customerInformation['guarantorInformation'] : null) {
                $customerGuarantor = new Guarantor();
                $GuarantorRepository = new GuarantorRepository();
                $customerGuarantor = $updatedCustomer->guarantors()->where(['is_primary' => 1, 'is_active' => 1])->first();
                $customerGuarantor = $GuarantorRepository->saveGuarantor($guarantorInformation['firstName'], $guarantorInformation['lastName'], $customerGuarantor);
                $GuarantorRepository->attachEmail($guarantorInformation['emailAddress'], $customerGuarantor);
                $GuarantorRepository->attachPhone($guarantorInformation['phoneNumber'], $customerGuarantor);
                $GuarantorRepository->attachAddress($guarantorInformation['address'], $customerGuarantor);
            }

            return $saveStatus;
        }
        else {
            return response($this->errors, 500);
        }
    }

    public function addRoommateToGroup(Request $request) {
        $saveStatus = array(
            'errors' => array(),
            'success' => true
        );

        $customerID = $request->input('customerID');
        $roommateID = $request->input('roommateID');

        $customer = Customer::where('id', $customerID)->firstOrFail();
        $roommateGroupID = $customer->requested_roommate_group_id;

        //Create new roommate group for customer if a roommate is added to a customer without a roommate group
        if($roommateGroupID == NULL) {
            $newRoommateGroup = new RequestedRoommateGroup();
            $newRoommateGroup->save();
            $roommateGroupID = $newRoommateGroup->id;
            //Update the provided customer to be associated with a new roommate group
            $customer->requested_roommate_group_id = $roommateGroupID;
            $customer->save();
        }

        $roommate = Customer::where('id', $roommateID)->firstOrFail();
        $roommate->requested_roommate_group_id = $roommateGroupID;
        $saved = $roommate->save();

        //Get all roommates to return
        $allRequestedRoommates = Customer::where('requested_roommate_group_id', $roommateGroupID)->where('id', '!=', $customerID)->get();
        $saveStatus['data'] = $allRequestedRoommates;
        if(!$saved) {
            array_push($saveStatus['errors'], 'unable to add requested roommate to group');
            $saveStatus['success'] = false;
        }
        return response($saveStatus, 201);
    }

    public function removeRoommateFromGroup(Request $request) {
        $saveStatus = array(
            'errors' => array(),
            'success' => true
        );

        $customerID = $request->input('customerID');
        $roommateCustomerID = $request->input('roommateID');
        $roommateCustomer = Customer::where('id', $roommateCustomerID)->firstOrFail();
        $requestedRoommateGroupID = $roommateCustomer->requested_roommate_group_id;
        $roommateCustomer->requested_roommate_group_id = NULL;
        $saved = $roommateCustomer->save();
        //Get all roommates to return
        $allRequestedRoommates = Customer::where('requested_roommate_group_id', $requestedRoommateGroupID)->where('id', '!=', $customerID)->get();
        $saveStatus['data'] = $allRequestedRoommates;
        if(!$saved) {
            array_push($saveStatus['errors'], 'unable to remove associated roommate group from customer');
            $saveStatus['success'] = false;
        }
        return response($saveStatus, 201);
    }

    public function addLike(Customer $customer, UnitStyle $unitStyle){
        $likeResponse = $this->CustomerRepository->addLike($customer, $unitStyle);
        return Response(['success'=>$likeResponse], 201);
    }

    public function removeLike(Customer $customer, UnitStyle $unitStyle){
        $likeResponse = $this->CustomerRepository->removeLike($customer, $unitStyle);
        return Response(['success'=>$likeResponse], 201);
    }

    public function getLikes(Customer $customer){
        $likeResponse = $this->CustomerRepository->getLikes($customer);
        if(!$likeResponse->isEmpty()){
            return Response($likeResponse, 201);
        }else{
            return Response($likeResponse, 301);
        }
    }

    public function addProfileNote(Request $request) {
        $customerID = $request->input('customerID');
        $customer = Customer::find($customerID);
        $noteText = $request->input('noteText');
        $noteResponse = $this->CustomerRepository->addNote($customer, $noteText);
        if($noteResponse != false){
            return Response(['success'=>$noteResponse], 201);
        }else{
            return Response(['success'=>$noteResponse], 301);
        }
    }

    public function addNote(Request $request, Customer $customer){
        $message = $request->input('message');
        $noteResponse = $this->CustomerRepository->addNote($customer, $message);
        if($noteResponse != false){
            return Response(['success'=>$noteResponse], 201);
        }else{
            return Response(['success'=>$noteResponse], 301);
        }
    }

    public function editNote(Request $request, Note $note){
        $message = $request->input('message');
        $noteResponse = $this->CustomerRepository->editNote($note, $message);
        if($noteResponse != false && $noteResponse->exists){
            return Response($noteResponse, 201);
        }else{
            return Response($noteResponse, 301);
        }
    }

    public function removeNote(Note $note){
        $noteResponse = $this->CustomerRepository->removeNote($note);
        if($noteResponse != false){
            return Response(['success'=>$noteResponse], 201);
        }else{
            return Response(['success'=>$noteResponse], 301);
        }
    }

    public function getNotes(Customer $customer){
        $noteResponse = $this->CustomerRepository->getNotes($customer);
        if($noteResponse->isEmpty()){
            return Response($noteResponse, 201);
        }else{
            return Response($noteResponse, 301);
        }
    }
}
