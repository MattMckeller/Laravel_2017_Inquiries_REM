<?php
namespace App\Repositories\Inquiries;
use App\Models\Auth\Team\UserAccount;
use \App\Models\Inquiries\Inquiry;
use \App\Models\Customers\Customer;
use Illuminate\Support\Facades\App;

class InquiryRepository
{
    use InquiryQualifications, InquirySearching, InquiryHeatIndex, InquiryClaiming;

    function getCustomerForInquiries($inquiry){
        if($inquiry == null){return false;}
        $customers = $inquiry->customer()->first();
        return $customers;
    }

    public static function getCustomerInquiryInformation($inquiryID) {
        //Retrieve inquiry to determine the customer id for the inquiry
        $inquiry = Inquiry::where('id', $inquiryID)->firstOrFail();
        $userAccountRepository = App::make('UserAccountRepository');
        $timerRepository = App::make('TimerRepository');
        $associatedAgent = $inquiry->userAccount()->first();
        $inquiry->active_agent_name = (!empty($associatedAgent))?($userAccountRepository->getAccountName($associatedAgent)):("Available");
        $inquiry->next_contact_timer = $timerRepository->getNextTimer($inquiry);

        $customerId = $inquiry->customer_id;
        //Retrieve the customer from the inquiry
        $customer = Customer::where('id', $customerId)->firstOrFail()->load('addresses')->load('emailAddresses')->load('organizations')->load('phoneNumbers');

        //Store inquiry labels
        $customer->inquiryLabels = $inquiry->inquiryLabel;
        //Store inquiry events
        $customer->inquiryEvents = $inquiry->inquiryEvents;

        $inquiryPreferences = array();
        foreach($inquiry->inquiryEvents as $inquiryEvent) {
            foreach($inquiryEvent->answer as $answer) {
                //Retrieve supporting information for each answer provided
                $questionOptionSelection = $answer->questionOptionSelection;
                $question = $questionOptionSelection->question;
                $optionChoice = $questionOptionSelection->optionChoice;

                //Push question and answer onto array for return
                $inquiryPreferences[] = array(
                    'question' => $question->question_name,
                    'answer' => $optionChoice->option_choice_name
                );
            }
        }
        //Store all inquiry preferences
        $customer->inquiryPreferences = $inquiryPreferences;
        //Store all associated inquiry locations
        $customer->locations = $inquiry->locations;
        //Store brand exposures
        $customer->brandExposures = $inquiry->brandExposures;
        //Store inquiry notes
        $customer->inquiryNotes = $inquiry->inquiryNotes;
        //Store inquiry
        $customer->inquiries = array($inquiry);
        return $customer;
    }
}