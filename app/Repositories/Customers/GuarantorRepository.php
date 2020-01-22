<?php
/**
 * Created by PhpStorm.
 * User: KeanMattingly
 * Date: 8/9/17
 * Time: 11:16 AM
 */

namespace App\Repositories\Customers;


use App\Models\Customers\Address;
use App\Models\Customers\EmailAddress;
use App\Models\Customers\Guarantor;
use App\Models\Customers\PhoneNumber;

class GuarantorRepository
{
    /**
     * @param $addressInformation array
     * @param $guarantor Guarantor
     * @return bool
     */
    public function attachAddress($addressEncoded, $guarantor) {
        $addressInformation = json_decode($addressEncoded, true);
        $address = new Address();
        $address->street_address_1 = array_key_exists('streetAddress1', $addressInformation) ? $addressInformation['streetAddress1'] : null;
        $address->street_address_2 = array_key_exists('streetAddress2', $addressInformation) ? $addressInformation['streetAddress2'] : null;
        $address->is_active = array_key_exists('isActive', $addressInformation) ? $addressInformation['isActive'] : 1;
        $address->is_international = array_key_exists('isInternational', $addressInformation) ? $addressInformation['isInternational'] : 0;
        $address->is_primary = array_key_exists('isPrimary', $addressInformation) ? $addressInformation['isPrimary'] : 1;
        $address->city = array_key_exists('city', $addressInformation) ? $addressInformation['city'] : null;
        $address->state = array_key_exists('state', $addressInformation) ? $addressInformation['state'] : null;
        $address->zip = array_key_exists('zip', $addressInformation) ? $addressInformation['zip'] : null;
        $address->country = array_key_exists('country', $addressInformation) ? $addressInformation['country'] : null;
        if (!$address->guarantor()->associate($guarantor)) {
            return false;
        }
        if (!$address->save()) {
            return false;
        }
        $guarantor->addresses()->where('id', '!=', $address->id)->update(['is_primary' => 0]);
        return true;
    }

    /**
     * @param $emailAddressInformation string
     * @param $guarantor Guarantor
     * @return bool
     */
    public function attachEmail($emailAddressInformation, $guarantor) {
        $emailAddress = new EmailAddress();
        $emailAddress->email_address = $emailAddressInformation;
        if (!$emailAddress->guarantor()->associate($guarantor)) {
            return false;
        }
        if (!$emailAddress->save()) {
            return false;
        }
        $guarantor->emailAddresses()->where('id', '!=', $emailAddress->id)->update(['is_primary' => 0]);
        return true;
    }

    /**
     * @param $phoneNumberInformation  string
     * @param $guarantor Guarantor
     * @return bool
     * */
    public function attachPhone($phoneNumberInformation, $guarantor) {
        $phoneNumber = new PhoneNumber();
        $phoneNumber->phone_number = $phoneNumberInformation;
        $phoneNumber->guarantor()->associate($guarantor);
        $phoneNumber->save();


        if (!$phoneNumber->save()) {
            return false;
        }
        if (!$phoneNumber->guarantor()->associate($guarantor)) {
            return false;
        }
        $guarantor->phoneNumbers()->where('id', '!=', $phoneNumber->id)->update(['is_primary' => 0]);
        return true;
    }

    /**
     * @param $firstName string
     * @param $lastName string
     * @param $guarantor Guarantor
     * @param $isActive int
     * @param $isVerified int
     * @return Guarantor
     * */
    public function saveGuarantor($firstName, $lastName, $isActive = 1, $isVerified = 1, $guarantor = null) {
        if (!$guarantor) {
            $guarantor = new Guarantor();
        }
        $guarantor->first_name = $firstName;
        $guarantor->last_name = $lastName;
        $guarantor->is_active = $isActive;
        $guarantor->is_verified = $isVerified;
        $guarantor->saveOrFail();
        return $guarantor;
    }

    /**
     * @param $firstName string
     * @param $lastName string
     * @param $guarantor Guarantor
     * @param $isActive int
     * @param $isVerified int
     * @return Guarantor
     * */
    public function updateGuarantor($firstName, $lastName, $guarantor, $isActive = null, $isVerified = null) {
        $guarantorID = $guarantor->id;
        $guarantorPhoneNumbers = $guarantor->phoneNumbers;
        $guarantorEmailAddresses = $guarantor->emailAddresses;

        if(!empty($firstName)) {
            $guarantor->first_name = $firstName;
        }
        if(!empty($lastName)) {
            $guarantor->last_name = $lastName;
        }
        $guarantor->is_verified = 1;
        $guarantor->is_active = 1;
        if(!empty($emailAddress)) {
            foreach($guarantorEmailAddresses as $guarantorEmailAddress) {
                if($guarantorEmailAddress->is_active == 1 && $guarantorEmailAddress->is_primary == 1) {
                    $guarantorEmailAddress->is_active = 0;
                    $guarantorEmailAddress->is_primary = 0;
                    $guarantorEmailAddress->save();
                }
            }

            $emailAddress = new EmailAddress();
            $emailAddress->guarantor_id = $guarantorID;
            $emailAddress->is_primary = 1;
            $emailAddress->is_active = 1;
            $emailAddress->email_address = $emailAddress;
            $emailAddress->save();
        }
        if(!empty($phoneNumber)) {
            foreach($guarantorPhoneNumbers as $guarantorPhoneNumber) {
                if($guarantorPhoneNumber->is_active == 1 && $guarantorPhoneNumber->is_primary == 1) {
                    $guarantorPhoneNumber->is_active = 0;
                    $guarantorPhoneNumber->is_primary = 0;
                    $guarantorPhoneNumber->save();
                }
            }
            $phoneNumber = new PhoneNumber();
            $phoneNumber->guarantor_id = $guarantorID;
            $phoneNumber->is_primary = 1;
            $phoneNumber->is_active = 1;
            $phoneNumber->phone_number = $phoneNumber;
            $phoneNumber->save();
        }

        $saved = $guarantor->save();
        $updatedGuarantor = Guarantor::find($guarantorID)->load('emailAddresses')->load('phoneNumbers');
        Guarantor::where('id', '!=', $updatedGuarantor->id)->update(['is_active' => 0]);
        return $updatedGuarantor;
    }
}