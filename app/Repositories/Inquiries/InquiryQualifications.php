<?php
namespace App\Repositories\Inquiries;

use App\Models\Customers\EmergencyContact;
use App\Models\Customers\PhoneNumber;
use App\Models\Inquiries\InquiryEvent;
use App\Models\Inquiries\InquiryNote;
use App\Models\Other\School;
use Illuminate\Http\Request;

//Models
use App\Models\Inquiries\Answer;
use App\Models\Other\Application;
use App\Models\Inquiries\InquiryLabel;
use App\Models\Inquiries\InquirySource;
use App\Models\Inquiries\InquirySourceOption;
use App\Models\Inquiries\InquirySourceSelection;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AnswerController;
use App\Models\Customers\Organization;
use App\Models\Inquiries\OptionChoice;
use App\Models\Inquiries\Question;
use App\Models\Inquiries\QuestionOptionSelection;
use App\Models\Inventory\Location;
use App\Models\Marketing\BrandExposure;

//Restful API Controllers
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\EmailAddressController;
use App\Http\Controllers\Api\InquiryController;
use App\Http\Controllers\Api\InquiryEventController;
use App\Http\Controllers\Api\InquiryNoteController;
use App\Http\Controllers\Api\PhoneNumbersController;
use App\Models\Customers\Customer;
use Illuminate\Support\Facades\App;

trait InquiryQualifications
{
    private $AccessControl;
    private $TeamAuth;

    function __construct(){
        $this->AccessControl = App::make('AccessControl');
        $this->TeamAuth = App::make('TeamAuth');
    }

    public function createQualifiedInquiry(Request $request) {
        //Store the customer
        $customer = CustomerController::store($request)->getOriginalContent();
        //Store the inquiry
        $inquiry = InquiryController::store($request)->getOriginalContent();
        //Associate the inquiry with the customer
        $inquiry->customer()->associate($customer)->save();

        //Save the inquiry as qualified
        $inquiryLabel = InquiryLabel::where('name', 'qualified')->firstOrFail();
        $inquiry->inquiryLabel()->attach($inquiryLabel);
        //Find the inquiry source to be saved from the inquiry source selection provided
        $inquirySource = InquirySource::where('type', $request->input("inquirySource"))->firstOrFail();
        //Find the inquiry source option from the inquiry source option provided
        $inquirySourceOption = InquirySourceOption::where('id', $request->input("inquirySourceOption"))->firstOrFail();

        //Based on the inquiry source and inquiry source option provided, find the inquiry source seRequest::merge(['New Key' => 'New Value']); selection to associate with the inquiry
        $inquirySourceSelection = InquirySourceSelection::where('inquiry_source_id', $inquirySource->id)->where('inquiry_source_option_id', $inquirySourceOption->id)->firstOrFail();

        //Add the inquiry id to the request for the inquiry event creation
        $request->merge(array('inquiryID' => $inquiry->id));
        //Replace the inquiry source type with id for inquiry event creation
        $request->merge(array('inquirySource' => $inquirySource->id));

        //Store a new inquiry event with the inquiry
        $inquiryEventController = new InquiryEventController();
        $inquiryEventResponse = $inquiryEventController->storeInquiryEvent($request);
        //Retrieve the inquiry event that has been stored for the inquiry
        $inquiryEventContent = json_decode($inquiryEventResponse->getContent(), true);
        $inquiryEventID = $inquiryEventContent['id'];
        $inquiryEvent = InquiryEvent::where('id', $inquiryEventID)->firstOrFail();
        //Save the inquiry event to the inquiry
        $inquiry->inquiryEvents()->save($inquiryEvent);

        //Associate the inquiry source selection found with the inquiry event
        $inquiryEvent->inquirySourceSelection()->associate($inquirySourceSelection)->save();

        //Associate any organization that has been provided
        if($request->has("organization") && $request->input("organization") != "0") {
            foreach($request->input("organization") as $org) {
                if ($organization = Organization::find($org)) {
                    $customer->organizations()->attach($organization);
                }
            }
//            $organization = Organization::where('id', $request->input('organization'))->firstOrFail();
//            $customer->organizations()->attach($organization);
        }

        if($request->has("phone")) {
            $phone = PhoneNumbersController::store($request)->getOriginalContent();
            $phone->customer()->associate($customer)->save();
        }
        if($request->has("email")) {
            $email = EmailAddressController::store($request)->getOriginalContent();
            $email->customer()->associate($customer)->save();
        }

        if($request->has("streetAddress")
            || $request->has("streetAddress2")
            || $request->has("city")
            || $request->has("state")
            || $request->has("zip")
            || $request->has("country")
        ) {
            $address = AddressController::store($request)->getOriginalContent();
            $address->customer()->associate($customer)->save();
        }

        if($request->has('brandExposure')) {
            foreach($request->input("brandExposure") as $brandExposure_id) {
                $brandExposure = BrandExposure::findOrFail($brandExposure_id);
                $inquiry->brandExposures()->attach($brandExposure);
            }
        }

        if($request->has('locationPreferences')) {
            foreach($request->input('locationPreferences') as $location_id) {
                $location = Location::findOrFail($location_id);
                $customer->locationPreferences()->attach($location);
            }
        }

        if($request->has('emergencyContactFirstName') && $request->has('emergencyContactLastName')) {
            //Create new emergency contact for the customer
            $emergencyContact = new EmergencyContact();
            $emergencyContact->first_name = $request->input('emergencyContactFirstName');
            $emergencyContact->last_name = $request->input('emergencyContactLastName');
            $emergencyContact->relationship = $request->input('emergencyContactRelationship', false);
            $emergencyContact->is_active = 1;
            $emergencyContact->is_primary = 1;
            $emergencyContact->save();
            //Create the phone number for the emergency contact if provided
            if($request->has('emergencyContactPhoneNumber')) {
                $emergencyContactPhoneNumber = new PhoneNumber();
                $emergencyContactPhoneNumber->phone_number = $request->input('emergencyContactPhoneNumber', false);
                $emergencyContactPhoneNumber->is_primary = 1;
                $emergencyContactPhoneNumber->is_active = 1;
                $emergencyContactPhoneNumber->emergency_contact_id = $emergencyContact->id;
                $emergencyContactPhoneNumber->save();
            }
            //Relate the emergency contact with the customer
            $emergencyContact->customers()->attach($customer);
        }

        $this->saveQuestionsAndAnswers($request, $customer, $inquiryEvent);

        //Create application for the customer
        $yearInSchool = $request->input('yearInSchool', false);
        $DL_Number = $request->input('DL_Number', false);
        $Car_Make = $request->input('Car_Make', false);
        $Car_Model = $request->input('Car_Model', false);
        $Car_Year = $request->input('Car_Year', false);
        $License_Plate_Number = $request->input('License_Plate_number', false);
        $Current_Employer = $request->input('Current_Employer', false);
        $Current_Employer_Supervisor_Name = $request->input('Current_Employer_Supervisor_name', false);
        $Current_Employer_Phone = $request->input('Current_Employer_Phone', false);
        $Previous_Landlord_Name = $request->input('Previous_Landlord_Name', false);
        $Previous_Landlord_Phone = $request->input('Previous_Landlord_Phone', false);

        $application = new Application();
        $application->year_in_school = $yearInSchool;
        $application->employer_supervisor = $Current_Employer_Supervisor_Name;
        $application->supervisor_phone = $Current_Employer_Phone;
        $application->landlord = $Previous_Landlord_Name;
        $application->landlord_phone = $Previous_Landlord_Phone;
        $application->employer = $Current_Employer;

        $application->license_plate_number = $License_Plate_Number;
        $application->car_make = $Car_Make;
        $application->car_year = $Car_Year;
        $application->car_model = $Car_Model;
        $application->drivers_license_number = $DL_Number;
//        Parent Current Employer
//        Parent Supervisor Name
//        Parent Supervisor Phone
//        Parent Resident/Landlord
        $application->save();
        $application->customers()->sync([$customer->id]);

        //Associate the customer with a school if it has been provided
        $schoolIDs = $request->input('schools', false);
        if($schoolIDs) {
            $customer->schools()->sync($schoolIDs);
        }

        if($request->has("ssn")){
            App::make('CustomerRepository')->updateCustomerSSN($customer, $request->input("ssn"));
        }

        //Determine the user that should be associated with the inquiry
        $loggedInId = $this->TeamAuth->get_user()->id;
        $user_account_id = ($request->input('selectedInquiryAgent') !== "0" && !empty($request->input('selectedInquiryAgent'))) ? $request->input('selectedInquiryAgent') : $loggedInId;
        $inquiry->user_account_id = $user_account_id;
        $inquiry->save();

        return $customer;
    }

    public function createUnqualifiedInquiry(Request $request) {
        //Retrieve the logged in user
        $loggedInId = $this->TeamAuth->get_user()->id;

        $customer = CustomerController::store($request)->getOriginalContent();
        $inquiry = InquiryController::store($request)->getOriginalContent();
        $inquiry->customer()->associate($customer);

        //Save the inquiry as qualified
        $inquiryLabel = InquiryLabel::where('name', 'unqualified')->firstOrFail();
        $inquiry->inquiryLabel()->attach($inquiryLabel);

        if($request->has("noteText")) {
            $inquiryNote = new InquiryNote();
            $inquiryNote->text = $request->input('noteText');
            $inquiryNote->save();
            //Save the inquiry note to the inquiry
            $inquiry->inquiryNote()->save($inquiryNote);
        }

        //Store a new inquiry event with the inquiry
        $inquiryEvent = new InquiryEvent();
        $inquiryEvent->inquiry_id = $inquiry->id;
        $inquiryEvent->inquiry_source_selection_id = $request->input('inquirySource', false);
        //Store the user account that created the unqualified inquiry
        $inquiryEvent->user_account_id = $loggedInId;
        $inquiryEvent->agent_contacted = 0;
        $inquiryEvent->successful_contact = 1;
        $inquiry->inquiryEvents()->save($inquiryEvent);
        $inquiry->save();

        if ($request->has("phone")) {
            $phone = PhoneNumbersController::store($request)->getOriginalContent();
            $phone->customer()->associate($customer)->save();
        }
        if ($request->has("email")) {
            $email = EmailAddressController::store($request)->getOriginalContent();
            $email->customer()->associate($customer)->save();
        }

        return $customer;
    }

    function saveQuestionsAndAnswers(Request $request, Customer $customer, InquiryEvent $inquiryEvent = null){
        $optionChoices = array();
        //Collect all question option choices selected
        $request->has('roommate_matching') ? $optionChoices['roommate_matching'] = $request->input('roommate_matching') : "";
        $request->has('has_pet') ? $optionChoices['has_pet'] = $request->input('has_pet') : "";
        $request->has('wants_furniture') ? $optionChoices['wants_furniture'] = $request->input('wants_furniture') : "";
        $request->has('wants_utilities') ? $optionChoices['wants_utilities'] = $request->input('wants_utilities') : "";
        $request->has('overall_interest') ? $optionChoices['overall_interest'] = $request->input('overall_interest') : "";
        $request->has('price_importance') ? $optionChoices['price_importance'] = $request->input('price_importance') : "";
        $request->has('amenity_interest') ? $optionChoices['amenity_interest'] = $request->input('amenity_interest') : "";
        $request->has('max_budget') ? $maxBudget = $request->input('max_budget') : "";
        $request->has('price_range_minimum') ? $priceRangeMinimum = $request->input('price_range_minimum') : "";
        $request->has('price_range_maximum') ? $priceRangeMaximum = $request->input('price_range_maximum') : "";
        //desired_bedroom_count
        //question subtext => id of option
        if($request->has('desired_bedroom_count')) {
            //Store the bedroom count separately from other questions because it is a multi-part answer
            $question = Question::where('question_subtext', 'desired_bedroom_count')->firstOrFail();
            //Iterate through each desired bedroom count key provided to associate
            foreach($request->input('desired_bedroom_count') as $optionChoiceID) {
                $optionChoice = OptionChoice::where('id', $optionChoiceID)->firstOrFail();
                $questionOptionSelection = QuestionOptionSelection::where('question_id', $question->id)->where('option_choice_id', $optionChoice->id)->firstOrFail();

                $answer = AnswerController::store($request)->getOriginalContent();
                $answer->questionOptionSelection()->associate($questionOptionSelection)->save();
                $answer->customer()->associate($customer)->save();
                if($inquiryEvent) {
                    $inquiryEvent->answer()->save($answer);
                }
            }
        }
        //Save option choices selected
        if(!empty($optionChoices)) {
            foreach($optionChoices as $questionSubtext => $optionChoiceID) {
                //Find the question with the associated question name
                $question = Question::where('question_subtext', $questionSubtext)->firstOrFail();
                //Find the option choice with the associated id
                $optionChoice = OptionChoice::where('id', $optionChoiceID)->firstOrFail();

                $questionOptionSelection = QuestionOptionSelection::where('question_id', $question->id)->where('option_choice_id', $optionChoice->id)->firstOrFail();

                //Store the customer ID for use with the answers storage
                $answer = AnswerController::store($request)->getOriginalContent();
                $answer->questionOptionSelection()->associate($questionOptionSelection)->save();
                $answer->customer()->associate($customer)->save();
                if($inquiryEvent) {
                    $inquiryEvent->answer()->save($answer);
                }
            }
        }
        //Save any text inputs provided
        if(!empty($maxBudget) && $maxBudget != '') {
            $question = Question::where('question_subtext', 'max_budget')->firstOrFail()->load('optionChoice');
            $optionChoices = $question->optionChoice;
            $optionChoiceID = NULL;
            foreach($optionChoices as $optionChoice) {
                $optionChoiceID = $optionChoice->id;
            }
            //Retrieve question option selection
            $questionOptionSelection = QuestionOptionSelection::where('question_id', $question->id)->where('option_choice_id', $optionChoiceID)->firstOrFail();

            //Save minimum price range answer with number provided
            $answer = new Answer();
            $answer->answer_int = $maxBudget;
            $answer->questionOptionSelection()->associate($questionOptionSelection)->save();
            $answer->customer()->associate($customer)->save();
            if($inquiryEvent) {
                $inquiryEvent->answer()->save($answer);
            }
        }

        if(!empty($priceRangeMinimum) && $priceRangeMinimum != '') {
            $question = Question::where('question_subtext', 'price_range_minimum')->firstOrFail()->load('optionChoice');
            $optionChoices = $question->optionChoice;
            $optionChoiceID = NULL;
            foreach($optionChoices as $optionChoice) {
                $optionChoiceID = $optionChoice->id;
            }
            //Retrieve question option selection
            $questionOptionSelection = QuestionOptionSelection::where('question_id', $question->id)->where('option_choice_id', $optionChoiceID)->firstOrFail();

            //Save minimum price range answer with number provided
            $answer = new Answer();
            $answer->answer_int = $priceRangeMinimum;
            $answer->questionOptionSelection()->associate($questionOptionSelection)->save();
            $answer->customer()->associate($customer)->save();
            if($inquiryEvent) {
                $inquiryEvent->answer()->save($answer);
            }
        }

        if(!empty($priceRangeMaximum) && $priceRangeMaximum != '') {
            $question = Question::where('question_subtext', 'price_range_maximum')->firstOrFail()->load('optionChoice');
            $optionChoices = $question->optionChoice;
            $optionChoiceID = NULL;
            foreach($optionChoices as $optionChoice) {
                $optionChoiceID = $optionChoice->id;
            }
            //Retrieve question option selection
            $questionOptionSelection = QuestionOptionSelection::where('question_id', $question->id)->where('option_choice_id', $optionChoiceID)->firstOrFail();

            //Save maximum price range answer with number provided
            $answer = new Answer();
            $answer->answer_int = $priceRangeMaximum;
            $answer->questionOptionSelection()->associate($questionOptionSelection)->save();
            $answer->customer()->associate($customer)->save();
            if($inquiryEvent) {
                $inquiryEvent->answer()->save($answer);
            }
        }
    }

    function getClosestOptionFromValue($question_subtext, $value){
        if($value == null){return null;}
        $options = $this->getOptionsForQuestion($question_subtext);
        $selectedOptionID = null;
        foreach($options as $option) {
            $optName = $option->option_choice_name;
            if ($value == 0 && $optName == 'no') {
                $selectedOptionID = $option->id;
                break;
            } elseif ($value == 1 && $optName == 'yes') {
                $selectedOptionID = $option->id;
                break;
            }
        }
        return $selectedOptionID;
    }
    function getOptionsForQuestion($question_subtext){
        $question = Question::where('question_subtext', $question_subtext)->firstOrFail();
        $options = $question->optionChoice()->get();
        return $options;
    }
}