<?php

namespace App\Http\Controllers;

use App\Models\Customers\Customer;
use App\Libraries\DoubleMetaPhone;

class TestingController extends Controller
{
    public function index() {
        $this->addDoubleMetaPhoneToCustomers();
    }

    private function addDoubleMetaPhoneToCustomers() {
        echo "Begin: Adding double MetaPhones to the customers.";
        $this->echoNewline();
        $customers = Customer::all();
        foreach($customers as $customer) {
            $doubleMetaPhone = new DoubleMetaPhone();
            $customerFirstNameMetaPhone = $doubleMetaPhone->CalculateDoubleMetaPhone($customer->first_name);
            $customerLastNameMetaPhone = $doubleMetaPhone->CalculateDoubleMetaPhone($customer->last_name);
            $customerFirstNamePrimary = $customerFirstNameMetaPhone['primary'];
            $customerFirstNameSecondary = $customerFirstNameMetaPhone['secondary'];
            $customerLastNamePrimary = $customerLastNameMetaPhone['primary'];
            $customerLastNameSecondary = $customerLastNameMetaPhone['secondary'];
            unset($doubleMetaPhone);

            //Set the values of the MetaPhones to the customers
            $customer->first_name_dm_first = $customerFirstNamePrimary;
            $customer->first_name_dm_second = $customerFirstNameSecondary;
            $customer->last_name_dm_first = $customerLastNamePrimary;
            $customer->last_name_dm_second = $customerLastNameSecondary;
            $saveAttempt = $customer->save();
            if($saveAttempt != true) {
                echo "ERROR: Unable to update customer MetaPhone";
                $this->echoNewline();
            }
        }
        echo "Finished: Customer MetaPhones updated.";
        $this->echoNewline();
    }

    private function echoNewline($count=1)
    {
        if(is_numeric($count)){
            while($count > 0){
                echo "<br/>\n\r";
                $count--;
            }
        }else {
            echo "<br/>\n\r";
        }
    }

    private function formatDisplay($array) {
        echo "<pre>";
        print_r($array);
        echo "</pre>";
    }
}
