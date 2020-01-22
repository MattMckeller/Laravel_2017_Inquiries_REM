<?php

namespace App\Http\Controllers\Api\LiveAtBrookside;

use App\Models\Auth\Customer\CustomerAccount;
use App\Models\Customers\Address;
use App\Models\Customers\Customer;
use App\Models\Customers\EmailAddress;
use App\Models\Customers\PhoneNumber;
use App\Models\Customers\RequestedRoommateGroup;
use App\Models\Inquiries\Answer;
use App\Models\Inquiries\Inquiry;
use App\Models\Inventory\UnitStyle;
use App\Models\Marketing\BrandExposure;
use App\Models\Other\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Libraries\DoubleMetaPhone;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use App\Models\Inquiries\InquirySource;
use App\Models\Inquiries\Question;
use App\Models\Inquiries\QuestionOptionSelection;
use App\Models\Inquiries\InquirySourceSelection;
use App\Models\Inquiries\InquirySourceOption;
use App\Models\Inventory\Location;
use App\Models\Auth\Team\WorkingLocation;

use App\Http\Controllers\Controller;

class LiveAtBrooksideController extends Controller
{
    private $CustomerRepository;
    private $InquiryRepository;
    private $CustomerAccountRepository;
    private $errors;

    function __construct(){
        $this->CustomerRepository = App::make('CustomerRepository');
        $this->InquiryRepository = App::make('InquiryRepository');
        $this->CustomerAccountRepository = App::make('CustomerAccountRepository');
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createCustomer(Request $request)
    {
        //Make sure customer does not already exist as an exact match.
        $exactMatchResult = $this->CustomerRepository->searchExactCustomerMatches($request->all());
        if($exactMatchResult['foundMatch'] === true){
            return response(['success'=>'false', 'message'=>'Customer already exists in our system.', 'matches'=>$exactMatchResult['matches']], 400);
        }
        //Make sure required information is provided.
        //todo add requirements if requested

        //Wrap functions in a transaction.
        $response = DB::transaction(function() use ($request) {
            //Attempt to create the customer
            $createdCustomer = $this->InquiryRepository->createQualifiedInquiry($request);

            //Create the customer account
            //todo add customer account to the response and check to make sure its actually an account that is returned
            $customerAccountResponse = $this->CustomerAccountRepository->createAccount($createdCustomer);
            $customerAccountResponseContent = json_decode($customerAccountResponse->getContent(), true);
            if($customerAccountResponseContent['success'] == false || $customerAccountResponseContent == false)
            {
                throw new \Exception('Failed to create the customer, or customer account.');
            }
            $customerAccount = $customerAccountResponseContent['account'];
            $response = [
                'customerID' => $createdCustomer->id,
                'customerAccountID' => $customerAccount['id'],
                'customerAccountActivated'=>$customerAccount['activated'],
                'customerPin'=>($customerAccount['activated'])?($customerAccount['pin']):(false),
                'customerAccountEmail'=>($customerAccount['email_address'])?($customerAccount['email_address']):(false),
                'firstName'=>($createdCustomer->first_name)?($createdCustomer->first_name):(false),
                'lastName'=>($createdCustomer->last_name)?($createdCustomer->last_name):(false),
                'success'=>true
            ];
            return $response;
        });
        return response($response, 201);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createCustomerWithoutAccount(Request $request)
    {
        //Make sure customer does not already exist as an exact match.
        $exactMatchResult = $this->CustomerRepository->searchExactCustomerMatches($request->all());
        if($exactMatchResult['foundMatch'] === true){
            return response(['success'=>'false', 'message'=>'Customer already exists in our system.', 'matches'=>$exactMatchResult['matches']], 400);
        }
        //Make sure required information is provided.
        //todo add requirements if requested

        //Wrap functions in a transaction.
        $response = DB::transaction(function() use ($request) {
            //Attempt to create the customer
            $createdCustomer = $this->InquiryRepository->createQualifiedInquiry($request);
            $response = [
                'customerID' => $createdCustomer->id,
                'firstName'=>($createdCustomer->first_name)?($createdCustomer->first_name):(false),
                'lastName'=>($createdCustomer->last_name)?($createdCustomer->last_name):(false),
                'success'=>true
            ];
            return $response;
        });
        return response($response, 201);
    }

    public function createAccount(Request $request)
    {
        $customerID = $request->input('customerID');
        $customer = new Customer();
        $customer = $customer::where('id', $customerID)->firstOrFail();

        $response = $this->CustomerAccountRepository->createAccount($customer);
        $responseContent = json_decode($response->getContent(), true);
        $responseContent['customer'] = $customer;

        return Response($responseContent, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateCustomer(Request $request)
    {
        //Collect info
        $customerInformation = $request->input('customerInformation');
        //Format address for inconsistencies
        if(empty($customerInformation['streetAddress']) && !empty($customerInformation['streetAddress1'])){
            $customerInformation['streetAddress'] = $customerInformation['streetAddress1'];
        }elseif(empty($customerInformation['streetAddress1']) && !empty($customerInformation['streetAddress'])){
            $customerInformation['streetAddress1'] = $customerInformation['streetAddress'];
        }
        //Start Db Transactions
        $response = DB::transaction(function() use ($customerInformation){
            $result = $this->CustomerRepository->updateCustomer($customerInformation);
            if($result == false || $result['success'] == false)
            {
                throw new \Exception('Failed to update customer. Performing rollback.');
            }
            return $result;
        });
        return response($response, 201);
    }

    public function getCustomerInformation(Request $request) {
        $customerID = $request->input('customerID');
        $customer = $this->CustomerRepository->getCustomerFull($customerID);
        $acc = $customer->customerAccount()->first();
        $customer->customerAccount = $acc;
        return response($customer, 201);
    }

    public function getCustomerSSN(Request $request){
        $customerID = $request->input('customerID');
        $customer = new Customer();
        $customer = $customer::find($customerID);
        $customerSSN = $this->CustomerRepository->getCustomerSSN($customerID);
        return response(array(
            'ssn'=>$customerSSN
        ));
    }

    /*
     * Function: Check for any customer matches based on
     * the given input
     *
     * @param Request $request | form input provided with
     * customer information
     */
    public function searchExistingCustomers(Request $request) {
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

    public function getUnitStyles(){
        $unitStylesAndLocations = [];
        $unitStyles = (new UnitStyle())::all()->load('units')->load('units.building')->load('units.building.location');
        foreach($unitStyles as $unitStyle){
            $unitStyleId = $unitStyle->id;
            if(empty($unitStylesAndLocations[$unitStyle->id])) {
                $unitStylesAndLocations[$unitStyle->id] = array();
            }
            $unitStyleCopy = (new UnitStyle())::find($unitStyleId);
            $unitStylesAndLocations[$unitStyle->id]['unit_style'] = $unitStyleCopy;
            $unitStylesAndLocations[$unitStyle->id]['locations'] = [];
            foreach($unitStyle->units as $unit){
                foreach($unit->building as $building){
                    foreach($building->location as $location){
                        if(empty($unitStylesAndLocations[$unitStyleId]['locations'][$location->id])){
                            $unitStylesAndLocations[$unitStyleId]['locations'][$location->id] = $location;
                        }
                    }
                }
            }
        }
        return $unitStylesAndLocations;
    }


    function getInquirySources(){
        $inqSource = new InquirySource();
        return $inqSource::all();
    }

    function getInquirySourceOptions(){
        $inqSourceOpt = new InquirySourceOption();
        return $inqSourceOpt::all();
    }
    function getBrandExposures(){
        $brandExposure = new BrandExposure();
        return $brandExposure::all();
    }

    function getLocationPreferencesOptions(){
        $locations = new Location();
        return $locations::all();
    }

    function getRoommateMatchingOptions(){
        return Question::where('question_subtext','roommate_matching')->first()->load('inputType')->load('optionChoice');
    }

    function getHasPetOptions(){
        return Question::where('question_subtext','pet')->first()->load('inputType')->load('optionChoice');
    }

    function petTypeOptions() {
        return Question::where('question_subtext','pet_type')->first()->load('inputType')->load('optionChoice');
    }

    function petBreedOptions() {
        return Question::where('question_subtext','pet_breed')->first()->load('inputType')->load('optionChoice');
    }

    function housingFeatureOptions() {
        return Question::where('question_subtext','housing_features')->first()->load('inputType')->load('optionChoice');
    }

    function movingReasonOptions() {
        return Question::where('question_subtext','moving_reason')->first()->load('inputType')->load('optionChoice');
    }

    function getDesiredBedroomCountOptions(){
        return Question::where('question_subtext','desired_bedroom_count')->first()->load('inputType')->load('optionChoice');
    }

    public function getWorkingLocations(Request $request) {
        $locationID = $request->input('locationID', false);
        if($locationID) {
            $workingLocations = WorkingLocation::where('location_id', $locationID)->get();
            return $workingLocations;
        }
        $workingLocations = new WorkingLocation();
        return $workingLocations->all();
    }

    public function getSchools() {
        return School::all();
    }

    public function getFormOptions(){
        $response = array(
            'desired_bedroom_count'=>$this->getDesiredBedroomCountOptions(),
            'moving_reason_options' => $this->movingReasonOptions(),
            'has_pet_option'=>$this->getHasPetOptions(),
            'pet_type_options' => $this->petTypeOptions(),
            'pet_breed_options' => $this->petBreedOptions(),
            'housing_feature_options' => $this->housingFeatureOptions(),
            'roommate_matching_options'=>$this->getRoommateMatchingOptions(),
            'location_preference'=>$this->getLocationPreferencesOptions(),
            'brand_exposure'=>$this->getBrandExposures(),
            'inquiry_source_options'=>$this->getInquirySourceOptions(),
            'inquiry_sources'=>$this->getInquirySources(),
            'schools' => $this->getSchools()
        );

        return $response;
    }

    public static function addNewCustomerAnswers(Request $request) {
        $customerID = $request->input('customerID');
        $customerPreferences = $request->input('customerPreferences');

        foreach($customerPreferences as $customerPreference) {
            //Find the question based on the customer preference name
            $questionName = null;
            if($customerPreference['type'] == 'checkbox') {
                $questionNameArray = explode("[]", $customerPreference['name']);
                $questionName = $questionNameArray[0];
            }
            else {
                $questionName = $customerPreference['name'];
            }
            //Find the question from the provided preference name
            $optionChoiceFound = false;
            $question = Question::where('question_subtext', $questionName)->firstOrFail()->load('optionChoice');
            $questionOptionChoices = $question->optionChoice;
            foreach($questionOptionChoices as $questionOptionChoice) {
                //If a single value has been provided, create new answer
                if(!is_array($customerPreference['value'])) {
                    if($questionOptionChoice['option_choice_name'] == $customerPreference['value']) {
                        $optionChoiceFound = true;
                        //Retrieve question option choice id for answer
                        $questionOptionChoiceID = QuestionOptionSelection::where('question_id', $question->id)->where('option_choice_id', $questionOptionChoice->id)->firstOrFail();
                        //Store the new answer for the customer
                        $newAnswer = new Answer();
                        $newAnswer->customer_id = $customerID;
                        $newAnswer->question_option_id = $questionOptionChoiceID->id;
                        $newAnswer->save();
                    }
                }
                //Iterate through provided customer preference values
                else {
                    foreach($customerPreference['value'] as $providedValue) {
                        if($questionOptionChoice['option_choice_name'] == $providedValue) {
                            $optionChoiceFound = true;
                            //Retrieve question option choice id for answer
                            $questionOptionChoiceID = QuestionOptionSelection::where('question_id', $question->id)->where('option_choice_id', $questionOptionChoice->id)->firstOrFail();
                            //Store the new answer for the customer
                            $newAnswer = new Answer();
                            $newAnswer->customer_id = $customerID;
                            $newAnswer->question_option_id = $questionOptionChoiceID->id;
                            $newAnswer->save();
                        }
                    }
                }
            }
            //Check to see if no option choices have been found; integer or text input provided.
            if($optionChoiceFound == false) {
                foreach($customerPreference['value'] as $providedValue) {
                    //Retrieve the "option choice" for the provided question
                    $question = Question::where('question_subtext', $questionName)->firstOrFail()->load('optionChoice');
                    $questionOptionChoices = $question->optionChoice;
                    $questionOptionChoiceID = $questionOptionChoices[0];

                    //Save the new answer
                    $newAnswer = new Answer();
                    $newAnswer->customer_id = $customerID;
                    $newAnswer->question_option_id = $questionOptionChoiceID;

                    $valueType = gettype($providedValue);
                    switch($valueType) {
                        case "integer":
                            $newAnswer->answer_int = $providedValue;
                            break;
                        case "string":
                            $newAnswer->answer_text = $providedValue;
                            break;
                    }
                    $newAnswer->save();
                }
            }
        }
        return Customer::findOrFail($customerID);
    }


    function customerForgotPassword(Request $request){
        $provided_email_address = $request->input('email_address');
        $provided_last_two_characters = $request->input('last_name_first_two_characters');
        $response = $this->_handle_reset($provided_email_address, $provided_last_two_characters);
        if($response == false){
            return Response(array('success'=>false), 200);
        }else{
            return Response(array('forgot_password_token'=>$response), 200);

        }
    }

    function _handle_reset($provided_email_address, $provided_last_two_characters){
        if(empty($provided_email_address) || empty($provided_last_two_characters)){
            return false;
        }

        $customer_account = new CustomerAccount();
        $customer_account = $customer_account->where('activated', 1)->where('email_address', $provided_email_address)->first();
        if(!empty($customer_account) && $customer_account->exists)
        {
            //Do the email addresses match?
            if(empty($customer_account) || empty($customer_account->id) || strtolower($customer_account->email_address) != strtolower($provided_email_address)){
                return false;
            }

            //Does the last name's first two characters match?
            $expected_last_name = $customer_account->last_name;
            $expected_last_two = strtolower(substr($expected_last_name, 0,2));
            if($expected_last_two !== strtolower($provided_last_two_characters)){
                return false;
            }

            // Does the account have a forgot password token?
            if(empty($customer_account->forgot_password_token)){
                return false;
            }
            $current_time = new \Datetime("now");
            $current_time_string = $current_time->format('Y-m-d H:i:s');
            //When was the last forgot password email sent
            if(!empty($customer_account->last_forgot_password_datetime)){
                $last_forgot_password_datetime = \DateTime::createFromFormat('Y-m-d H:i:s',$customer_account['last_forgot_password_datetime']);
                if($last_forgot_password_datetime !== FALSE) {
                    $five_minute_dateinterval = new \DateInterval('PT5M'); //Five minutes
                    $acceptable_datetime = $current_time->sub($five_minute_dateinterval);

                    if ($acceptable_datetime < $last_forgot_password_datetime) {
                        return false;
                    }
                }
            }
            $customer_account->last_forgot_password_datetime = $current_time_string;
            $customer_account->save();
            return $customer_account->forgot_password_token;
        }
    }
}
