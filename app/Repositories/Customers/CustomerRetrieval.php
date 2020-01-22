<?php
namespace App\Repositories\Customers;
use App\Libraries\DoubleMetaPhone;
use App\Models\Customers\EmailAddress;
use App\Models\Customers\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Customers\Customer;
use App\Models\Inquiries\Inquiry;

trait CustomerRetrieval
{
    function getCustomerStandard($CID){
        $customer = Customer::where('id', $CID)->firstOrFail()->load('emailAddresses')->load('addresses')->load('phoneNumbers');
        return $customer;
    }

    function getCustomerFull($CID){
        //Retrieve inquiry to determine the customer id for the inquiry
        $inquiry = Inquiry::where('customer_id', $CID)->first();

        //Retrieve the customer from the inquiry
        $customer = Customer::where('id', $CID)->firstOrFail()
            ->load('addresses')
            ->load('emailAddresses')
            ->load('organizations')
            ->load('applications')
            ->load('schools')
            ->load('phoneNumbers')
            ->load('schools')
            ->load('guarantors.emailAddresses', 'guarantors.phoneNumbers', 'guarantors.addresses');

        //Retrieve all roommates from the roommate group that are not the current customer
        $requestedRoommateGroupID = $customer->requested_roommate_group_id;
        $requestedRoommates = Customer::where('requested_roommate_group_id', $requestedRoommateGroupID)->whereNotNull('requested_roommate_group_id')->where('id', '!=', $CID)->get();
        $customer->requestedRoommates = $requestedRoommates;

        $inquiryPreferences = array();
        $brandExposures = array();
        $inquiryNote = array();
        $inquiryLocations = array();
        if(!empty($inquiry) && $inquiry->exists) {
            //Store inquiry labels
            $customer->inquiryLabels = $inquiry->inquiryLabel;
            //Store inquiry events
            $customer->inquiryEvents = $inquiry->inquiryEvents;

            foreach ($inquiry->inquiryEvents as $inquiryEvent) {
                foreach ($inquiryEvent->answer as $answer) {
                    //Retrieve supporting information for each answer provided
                    $questionOptionSelection = $answer->questionOptionSelection;
                    $question = $questionOptionSelection->question;
                    //Store the option choice or answer field if integer/text
                    $optionChoiceAnswer = NULL;
                    if (empty($answer->answer_int) && empty($answer->answer_text)) {
                        $optionChoice = $questionOptionSelection->optionChoice;
                        $optionChoiceAnswer = $optionChoice->option_choice_name;
                    } else {
                        //Check for integer or text provided
                        if (!empty($answer->answer_int)) {
                            $optionChoiceAnswer = $answer->answer_int;
                        } else if (!empty($answer->answer_text)) {
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
                }
            }
            $brandExposures = $inquiry->brandExposures;
            $inquiryNote = $inquiry->inquiryNote;
            $inquiryLocations = $inquiry->locations;
        }
        $customer->brandExposures = $brandExposures;
        //Store brand exposures
        //Store all inquiry preferences
        $customer->inquiryPreferences = $inquiryPreferences;
        //Store inquiry notes
        $customer->inquiryNotes = $inquiryNote;
        //Store inquiry
        $customer->inquiries = array($inquiry);
        //Store all associated inquiry locations
        $customer->locations = $inquiryLocations;
        return $customer;
    }
}