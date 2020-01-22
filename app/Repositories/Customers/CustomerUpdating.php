<?php
namespace App\Repositories\Customers;
use App\Libraries\DoubleMetaPhone;
use App\Models\Customers\EmailAddress;
use App\Models\Customers\EmergencyContact;
use App\Models\Customers\PhoneNumber;
use App\Models\Other\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Customers\Customer;
use App\Models\Customers\Address;
use Illuminate\Support\Facades\App;

trait CustomerUpdating
{
    function updateCustomer($customerInformation){
        $saveStatus = array(
            'errors' => array(),
            'success' => true
        );

        //Run validation rules against the provided data
        $validationAttempt = $this->minimalValidation($customerInformation);
        if($validationAttempt == false){
            array_push($saveStatus['errors'], 'Data failed to validate');
            $saveStatus['success'] = false;
        }

        $customerID = $customerInformation['customerID'];
        //Retrieve existing information
        $customer = $this->getCustomerStandard($customerID);
        if(!$customer || !$customer->exists){
            array_push($saveStatus['errors'], 'There was an error retrieving the customer.');
            $saveStatus['success'] = false;
            return $saveStatus;
        }

        if(!empty($customerInformation['firstName']) || !empty($customerInformation['lastName'])|| !empty($customerInformation['birthday'])|| !empty($customerInformation['middleInitial'])|| !empty($customerInformation['gender'])){
            $success = $this->updateBaseInformation($customer, $customerInformation);
            if($success !== true) {
                array_push($saveStatus['errors'], 'Customer base information failed to save.');
                $saveStatus['success'] = false;
            }
        }
        if(!empty($customerInformation['emailAddress'])) {
            $success = $this->updateEmailAddress($customer, $customerInformation);
            if($success !== true) {
                array_push($saveStatus['errors'], 'email address save failed');
                $saveStatus['success'] = false;
            }
        }

        if(!empty($customerInformation['phoneNumber'])) {
            $success = $this->updatePhoneNumber($customer, $customerInformation);
            if($success !== true) {
                array_push($saveStatus['errors'], 'phone number save failed');
                $saveStatus['success'] = false;
            }
        }

        if(!empty($customerInformation['locations'])) {
            $success = $this->updateLocationPreferences($customer, $customerInformation);
            if($success !== true) {
                array_push($saveStatus['errors'], 'Location preference failed to save');
                $saveStatus['success'] = false;
            }
        }

        if(!empty($customerInformation['streetAddress1'])) {
            $success = $this->updateAddress($customer, $customerInformation);
            if($success!== true){
                array_push($saveStatus['errors'], 'Address save failed');
                $saveStatus['success'] = false;
            };
        }

        if(
            !empty($customerInformation['DL_Number'] ) ||
            !empty($customerInformation['Car_Make'] ) ||
            !empty($customerInformation['Car_Model'] ) ||
            !empty($customerInformation['Car_Year'] ) ||
            !empty($customerInformation['License_Plate_number'] ) ||
            !empty($customerInformation['Current_Employer'] ) ||
            !empty($customerInformation['Current_Employer_Supervisor_name'] ) ||
            !empty($customerInformation['Current_Employer_Phone'] ) ||
            !empty($customerInformation['Previous_Landlord_Name'] ) ||
            !empty($customerInformation['Previous_Landlord_Phone']) ||
            !empty($customerInformation['Year_In_School'])
        ){
            $this->updateCustomerApplication($customer, $customerInformation);
        }

        if(!empty($customerInformation['emergencyContactFirstName']) || !empty($customerInformation['emergencyContactLastName']) || !empty($customerInformation['emergencyContactRelationship']) || !empty($customerInformation['emergencyContactPhoneNumber'])) {
            $success = $this->updateEmergencyContact($customer, $customerInformation);
            if($success!== true){
                array_push($saveStatus['errors'], 'Emergency contact save failed');
                $saveStatus['success'] = false;
            };
        }
        if(!empty($customerInformation['ssn'])){
            $updatedCustomer = $this->getCustomerStandard($customerID);
            $this->updateCustomerSSN($updatedCustomer, $customerInformation['ssn']);
        }

        //Associate the customer with a school if it has been provided
        if(!empty($customerInformation['schools'])) {
            $customer->schools()->sync($customerInformation['schools']);
        }

        $guarantor = $customer->guarantors()->first();

        if ($guarantorInformationJson = array_key_exists('guarantorInformation', $customerInformation) ? $customerInformation['guarantorInformation'] : null) {
            $guarantorInformation = json_decode($guarantorInformationJson, true);
            $firstName = array_key_exists('firstName', $guarantorInformation) ? $guarantorInformation['firstName'] : null;
            $lastName = array_key_exists('lastName', $guarantorInformation) ? $guarantorInformation['lastName'] : null;
            $phoneNumber = array_key_exists('phoneNumber', $guarantorInformation) ? $guarantorInformation['phoneNumber'] : null;
            $emailAddress = array_key_exists('emailAddress', $guarantorInformation) ? $guarantorInformation['emailAddress'] : null;
            $address = array_key_exists('address', $guarantorInformation) ? $guarantorInformation['address'] : null;

            $guarantorRepository = new GuarantorRepository();
            if (!empty($guarantor)) {
                $guarantor = $guarantorRepository->updateGuarantor($firstName, $lastName, $guarantor);
            }
            else {
                $guarantor = $guarantorRepository->saveGuarantor($firstName, $lastName);
                $customer->guarantors()->attach($guarantor);
            }
            $guarantorRepository->attachPhone($phoneNumber, $guarantor);
            $guarantorRepository->attachEmail($emailAddress, $guarantor);
            $guarantorRepository->attachAddress($address, $guarantor);
        }

        //Retrieve customer after information has been updated
        $updatedCustomer = $this->getCustomerStandard($customerID);
        $saveStatus['data'] = $updatedCustomer;
        return $saveStatus;

    }

    function updateBaseInformation(Customer $customer, $customerInformation){
        //Process Double MetaPhones for storage
        $doubleMetaPhone = new DoubleMetaPhone();
        $doubleMetaPhoneFirstNameResult = (!empty($customerInformation['firstName']))?($doubleMetaPhone->CalculateDoubleMetaPhone($customerInformation['firstName'])):($doubleMetaPhone->CalculateDoubleMetaPhone($customer->first_name));
        $doubleMetaPhoneLastNameResult = (!empty($customerInformation['lastName']))?($doubleMetaPhone->CalculateDoubleMetaPhone($customerInformation['lastName'])):($doubleMetaPhone->CalculateDoubleMetaPhone($customer->last_name));
        unset($doubleMetaPhone);

        $customerUpdated = Customer::findOrFail($customer->id);
        $customerUpdated->first_name = (!empty($customerInformation['firstName']))?($customerInformation['firstName']):($customer->first_name);
        $customerUpdated->last_name = (!empty($customerInformation['lastName']))?($customerInformation['lastName']):($customer->last_name);
        $customerUpdated->first_name_dm_first = $doubleMetaPhoneFirstNameResult['primary'];
        $customerUpdated->first_name_dm_second = $doubleMetaPhoneFirstNameResult['secondary'];
        $customerUpdated->last_name_dm_first = $doubleMetaPhoneLastNameResult['primary'];
        $customerUpdated->last_name_dm_second = $doubleMetaPhoneLastNameResult['secondary'];
        $customerUpdated->middle_initial = (!empty($customerInformation['middleInitial']))?($customerInformation['middleInitial']):($customer->middle_initial);
        $customerUpdated->gender = (!empty($customerInformation['gender']))?($customerInformation['gender']):($customer->gender);
        $customerUpdated->birthday = (!empty($customerInformation['birthday']))?($customerInformation['birthday']):($customer->birthday);
        $saved = $customerUpdated->save();

        return $saved;
    }

    function updateCustomerApplication(Customer $customer, $customerInformation){
        $application = $customer->applications()->first();
        if(empty($application) || !$application->exists){
            $application = new Application();
        }
        $application->year_in_school = (!empty($customerInformation['yearInSchool']))?($customerInformation['yearInSchool']):(null);
        $application->employer_supervisor = (!empty($customerInformation['Current_Employer_Supervisor_Name']))?($customerInformation['Current_Employer_Supervisor_Name']):(null);
        $application->supervisor_phone = (!empty($customerInformation['Current_Employer_Phone']))?($customerInformation['Current_Employer_Phone']):(null);
        $application->landlord = (!empty($customerInformation['Previous_Landlord_Name']))?($customerInformation['Previous_Landlord_Name']):(null);
        $application->landlord_phone = (!empty($customerInformation['Previous_Landlord_Phone']))?($customerInformation['Previous_Landlord_Phone']):(null);
        $application->employer = (!empty($customerInformation['Current_Employer']))?($customerInformation['Current_Employer']):(null);
        $application->year_in_school = (!empty($customerInformation['Year_In_School']))?($customerInformation['Year_In_School']):(null);

        $application->license_plate_number = (!empty($customerInformation['License_Plate_Number']))?($customerInformation['License_Plate_Number']):(null);
        $application->car_make = (!empty($customerInformation['Car_Make']))?($customerInformation['Car_Make']):(null);
        $application->car_year = (!empty($customerInformation['Car_Year']))?($customerInformation['Car_Year']):(null);
        $application->car_model = (!empty($customerInformation['Car_Model']))?($customerInformation['Car_Model']):(null);
        $application->drivers_license_number = (!empty($customerInformation['DL_Number']))?($customerInformation['DL_Number']):(null);
        $application->save();
        $application->customers()->sync([$customer->id]);
    }

    function updateAddress(Customer $customer, $customerInformation){
        $customerAddresses = $customer->addresses;
        foreach($customerAddresses as $currentAddress) {
            if($currentAddress->is_active == 1 && $currentAddress->is_primary == 1) {
                $currentAddress->is_active = 0;
                $currentAddress->is_primary = 0;
                $currentAddress->save();
            }
        }
        //Save new address
        $newAddress = new Address();
        $newAddress->customer_id = $customer->id;
        $newAddress->is_primary = 1;
        $newAddress->is_active = 1;
        $newAddress->street_address_1 = $customerInformation['streetAddress1'];
        $newAddress->city = $customerInformation['city'];
        $newAddress->state = $customerInformation['state'];
        $newAddress->zip = $customerInformation['zip'];
        $newAddress->country = $customerInformation['country'];
        if(!empty($customerInformation['streetAddress2'])) {
            $newAddress->street_address_2 = $customerInformation['streetAddress2'];
        }
        $saved = $newAddress->save();
        return $saved;
    }

    function updateEmailAddress(Customer $customer, $customerInformation){
        $customerEmailAddresses = $customer->emailAddresses;
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
        $newEmailAddress->customer_id = $customer->id;
        $newEmailAddress->is_active = 1;
        $newEmailAddress->is_primary =1;
        $newEmailAddress->email_address = $customerInformation['emailAddress'];
        $saved = $newEmailAddress->save();
        return $saved;
    }

    function updatePhoneNumber(Customer $customer, $customerInformation){
        $customerPhoneNumbers = $customer->phoneNumbers;
        foreach($customerPhoneNumbers as $currentPhoneNumber) {
            if($currentPhoneNumber->is_active == 1 && $currentPhoneNumber->is_primary == 1) {
                $currentPhoneNumber->is_active = 0;
                $currentPhoneNumber->is_primary = 0;
                $currentPhoneNumber->save();
            }
        }
        //Save new phone number
        $newPhoneNumber = new PhoneNumber();
        $newPhoneNumber->customer_id = $customer->id;
        $newPhoneNumber->is_active = 1;
        $newPhoneNumber->is_primary = 1;
        $newPhoneNumber->phone_number = $customerInformation['phoneNumber'];
        $saved = $newPhoneNumber->save();
        return $saved;
    }

    function updateLocationPreferences(Customer $customer, $customerInformation){
        //Get all current location preferences for customer
        $customerNew = Customer::where('id', $customer->id)->firstOrFail();
        $locationDetachStatus = $customerNew->locationPreferences()->detach();
        $customerNew->locationPreferences()->attach($customerInformation['locations']);
        return true;
    }

    function updateCustomerSSN(Customer $customer, $ssnValue){
        $encryptionHandler = App::make('RemEncryption');
        $encryptionResponse = $encryptionHandler->api_encrypt($ssnValue);
        $customer->ssn_ciphertext = $encryptionResponse['ciphertext'];
        $customer->ssn_decryption_key_ciphertext = $encryptionResponse['key_ciphertext'];;
        $customer->save();
    }

    function getCustomerSSN(Customer $customer){
        $encryptionHandler = App::make('RemEncryption');
        $encryptionResponse = $encryptionHandler->api_decrypt($customer->ssn_ciphertext, $customer->ssn_decryption_key_ciphertext);
        return $encryptionResponse;
    }

    function updateEmergencyContact(Customer $customer, $customerInformation) {
        $emergencyContact = $customer->emergencyContacts()->where('is_active', '1')->where('is_primary', '1')->first();
        if(empty($emergencyContact) || !$emergencyContact->exists){
            $emergencyContact = new EmergencyContact();
        }
        if(!empty($customerInformation['emergencyContactFirstName'])) {
            $emergencyContact->first_name = $customerInformation['emergencyContactFirstName'];
        }
        if(!empty($customerInformation['emergencyContactLastName'])) {
            $emergencyContact->last_name = $customerInformation['emergencyContactLastName'];
        }
        if(!empty($customerInformation['emergencyContactRelationship'])) {
            $emergencyContact->relationship = $customerInformation['emergencyContactRelationship'];
        }
        if(!empty($customerInformation['emergencyContactPhoneNumber'])) {
            //Retrieve the phone number for the customer
            $emergencyContactPhone = $emergencyContact->phoneNumbers();
            $emergencyContactPhone->phone_number = $customerInformation['emergencyContactPhoneNumber'];
            $emergencyContactPhone->save();
        }
        $emergencyContact->save();
    }
}