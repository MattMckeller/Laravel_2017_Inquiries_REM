<?php
namespace App\Repositories\Customers;
use App\Libraries\DoubleMetaPhone;
use App\Models\Customers\EmailAddress;
use App\Models\Customers\PhoneNumber;
use Illuminate\Support\Facades\DB;
use App\Models\Customers\Customer;

trait CustomerSearch
{
    public function searchCustomers($customerInformation, $similarityMatchRating = '.85') {
        $allMatchedCustomers = array();

        $firstName = $customerInformation['firstName'];
        $lastName = $customerInformation['lastName'];

        //Search for existing customers matched on name
        $doubleMetaPhone = new DoubleMetaPhone();
        //First Name MetaPhones
        $doubleMetaPhoneFirstNameResult = $doubleMetaPhone->CalculateDoubleMetaPhone($firstName);
        $customerFirstNameMetaPhonePrimary = $doubleMetaPhoneFirstNameResult['primary'];
        $customerFirstNameMetaPhoneSecondary = $doubleMetaPhoneFirstNameResult['secondary'];

        //Last Name MetaPhones
        $doubleMetaPhoneLastNameResult = $doubleMetaPhone->CalculateDoubleMetaPhone($lastName);
        $customerLastNameMetaPhonePrimary = $doubleMetaPhoneLastNameResult['primary'];
        $customerLastNameMetaPhoneSecondary = $doubleMetaPhoneLastNameResult['secondary'];

        unset($doubleMetaPhone);

        //Run query on DB for Jaro Winkler calculation on customers
        $firstNameCustomersPrimary = DB::select('SELECT *, jaro_winkler(:metaphone, `first_name_dm_first`) AS matchRating FROM customers HAVING matchRating >' . $similarityMatchRating, ['metaphone' => $customerFirstNameMetaPhonePrimary]);
        $firstNameCustomersSecondary = DB::select('SELECT *, jaro_winkler(:metaphone, `first_name_dm_second`) AS matchRating FROM customers HAVING matchRating >' . $similarityMatchRating, ['metaphone' => $customerFirstNameMetaPhoneSecondary]);

        $lastNameCustomersPrimary = DB::select('SELECT *, jaro_winkler(:metaphone, `last_name_dm_first`) AS matchRating FROM customers HAVING matchRating >' . $similarityMatchRating, ['metaphone' => $customerLastNameMetaPhonePrimary]);
        $lastNameCustomersSecondary = DB::select('SELECT *, jaro_winkler(:metaphone, `last_name_dm_second`) AS matchRating FROM customers HAVING matchRating >' . $similarityMatchRating, ['metaphone' => $customerLastNameMetaPhoneSecondary]);

        foreach($firstNameCustomersPrimary as $foundCustomer) {
            $allMatchedCustomers[$foundCustomer->id] = Customer::find($foundCustomer->id);
        }
        foreach($firstNameCustomersSecondary as $foundCustomer) {
            $allMatchedCustomers[$foundCustomer->id] = Customer::find($foundCustomer->id);
        }
        foreach($lastNameCustomersPrimary as $foundCustomer) {
            $allMatchedCustomers[$foundCustomer->id] = Customer::find($foundCustomer->id);
        }
        foreach($lastNameCustomersSecondary as $foundCustomer) {
            $allMatchedCustomers[$foundCustomer->id] = Customer::find($foundCustomer->id);
        }

        //Check for email match
        $email = (!empty($customerInformation['email']) ? $customerInformation['email'] : false);
        if(!empty($email)) {
            $foundEmails = EmailAddress::where('email_address', $email)->get()->load('customer');
            foreach($foundEmails as $foundEmail) {
                //Manually set the match rating to 1 because it is a direct match
                $customer = $foundEmail->customer()->first();
                if($customer && $customer->exists){
                    $customer->matchRating = 1;
                    $allMatchedCustomers[$customer->id] = $customer;
                }
            }
        }

        //Check for phone match
        $phone = (!empty($customerInformation['phone']) ? $customerInformation['phone'] : false);
        if(!empty($phone)) {
            $foundPhones = PhoneNumber::where('phone_number', $phone)->get()->load('customer');
            foreach($foundPhones as $foundPhone) {
                //Manually set the match rating to 1 because it is a direct match
                $customer = $foundPhone->customer()->first();
                if($customer && $customer->exists){
                    $customer->matchRating = 1;
                    $allMatchedCustomers[$customer->id] = $customer;
                }
            }
        }

        //Iterate through all matched customers and retrieve secondary information
        foreach($allMatchedCustomers as $customerID => $customer) {
            //Attach phone numbers to response
            $matchedCustomerPhone = PhoneNumber::where('customer_id', $customerID)->get();
            $customer->phoneNumbers = $matchedCustomerPhone;
            //Attach email addresses to response
            $matchedCustomerEmail = EmailAddress::where('customer_id', $customerID)->get();
            $customer->emailAddresses = $matchedCustomerEmail;
        }

        usort($allMatchedCustomers, function($a, $b) {
            return strcmp($b->matchRating, $a->matchRating);
        });

        return $allMatchedCustomers;
    }

    public function getCustomerStatus($customerID = false) {
        if(!$customerID) {
            return response('Customer ID must be provided to retrieve the status', 500);
        }
        //Retrieve the customer from the provided ID
        $customer = Customer::findOrFail($customerID)->load('leases')->load('inquiries')->load('appointments');

        //Check to see if a lease exists for the customer
        if(count($customer->leases)) {
            //todo Check to see if the customer does not previously have an inquiry
            if(count($customer->inquiries)) {
                foreach($customer->leases as $lease) {
                    if(!empty($lease->lease_start_year) && $lease->lease_start_year != "2017" && $lease->lease_start_year != "2018") {
                        $customerStatus = 'inquiry';
                    }
                    else {
                        $customerStatus = 'lease';
                    }
                }
            }
            else {
                $customerStatus = 'lease';
            }
        }
        //Check to see if an appointment exists for the customer
        else if(count($customer->appointments)) {
            $customerStatus = 'appointment';
        }
        //Check to see if an inquiry exists for the customer
        else if(count($customer->inquiries)) {
            $customerStatus = 'inquiry';
        }
        else {
            $customerStatus = 'unknown';
        }
        return $customerStatus;
    }

    function searchExactCustomerMatches($customerInformation){
        $foundExactMatch = false;
        $foundExactPhone = false;
        $foundExactEmail = false;
        $exactMatches = array();
        //Get partial matches
        $foundCustomers = $this->searchCustomers($customerInformation);
        foreach($foundCustomers as $foundCustomer){
            //Check for exact matches on phone
            $phoneMatch = (!empty($customerInformation['phone']))?($this->hasMatchingPhone($foundCustomer, $customerInformation['phone'])):(false);
            //Check for exact matches on email
            $emailMatch = (!empty($customerInformation['email']))?($this->hasMatchingEmail($foundCustomer, $customerInformation['email'])):(false);
            //Check for exact matches on name
            $firstMatch = (!empty($customerInformation['firstName']))?($this->hasMatchingFirstName($foundCustomer, $customerInformation['firstName'])):(false);
            $lastMatch = (!empty($customerInformation['lastName']))?($this->hasMatchingLastName($foundCustomer, $customerInformation['lastName'])):(false);
            if($phoneMatch === true || $emailMatch === true ){
                $exactMatches[] = $foundCustomer;
                $foundExactMatch = true;
                $foundExactEmail = ($foundExactEmail === false)?($emailMatch):($foundExactEmail);
                $foundExactPhone = ($foundExactPhone === false)?($phoneMatch):($foundExactPhone);
            }
        }
        return [
            'foundMatch'=>$foundExactMatch,
            'exactPhoneMatch'=>$foundExactPhone,
            'exactEmailMatch'=>$foundExactEmail,
            'matches'=>$exactMatches
        ];

    }

    private function hasMatchingPhone(Customer $customer, $expectedValue){
        $foundExact = false;
        $currentVals = $customer->phoneNumbers;
        foreach($currentVals as $currentVal){
            $val = $currentVal->phone_number;
            if(!empty($val) &&
                preg_replace("[^0-9]","",$val) == preg_replace("[^0-9]","",$expectedValue)
            ){
                $foundExact = true;
            }
        }
        return $foundExact;
    }

    private function hasMatchingEmail(Customer $customer, $expectedValue){
        $foundExact = false;
        $currentVals = $customer->emailAddresses;
        foreach($currentVals as $currentVal){
            $val = $currentVal->email_address;
            if(!empty($val) &&
                strtolower($val) == strtolower($expectedValue)
            ){
                $foundExact = true;
            }
        }
        return $foundExact;
    }

    private function hasMatchingFirstName(Customer $customer, $expectedValue){
        $foundExact = false;
        $currentVal = $customer->first_name;
        if(!empty($currentVal) && strtolower($currentVal) == strtolower($expectedValue)){
            $foundExact = true;
        }
        return $foundExact;
    }

    private function hasMatchingLastName(Customer $customer, $expectedValue){
        $foundExact = false;
        $currentVal = $customer->last_name;
        if(!empty($currentVal) && strtolower($currentVal) == strtolower($expectedValue)){
            $foundExact = true;
        }
        return $foundExact;
    }
}