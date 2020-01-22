<?php
namespace App\Repositories\Auth;
use App\Events\Authentication\CustomerAccountCreated;
use App\Events\Authentication\CustomerAccountDisabled;
use App\Events\Authentication\CustomerAccountVerified;
use App\Models\Customers\EmailAddress;
use \App\Models\Inquiries\Inquiry;
use \App\Models\Customers\Customer;
use App\Models\Auth\Team\UserAccount;
use App\Models\Auth\Customer\CustomerAccount;

class CustomerAccountRepository
{
    /**
     * @param Customer $customer The customer to create the account for
     * @return bool|CustomerAccount false on error, otherwise returns the customer account
     */
    public function createAccount(Customer $customer){
        //Grab the customers primary email address. Make sure the customer has an email address
        $customerEmailAddress = $customer->emailAddresses()->where('is_primary', 1)->first();
        if(!$customerEmailAddress || !$customerEmailAddress->exists){
            return Response(['success'=>false, 'message'=>'No primary email address was found for this customer.'], 201);
        }
        //Check for an existing customer account
        $existingAccount = $customer->customerAccount()->first();
        if(!empty($existingAccount) && $existingAccount->exists){
            if($existingAccount->is_activated == true){
                if($existingAccount->email_address == $customerEmailAddress->email_address) {
                    //Acc is good to go
                    return Response(['success'=>true,'account'=>$existingAccount], 201);
                }else{
                    //Error, customer already has an account with a different email address
                    return Response(['success'=>false, 'message'=>'An account already exists for this customer with a different email address'], 201);
                }
            }else{
                //Existing account is not yet activated
                $this->disableCustomerAccount($existingAccount);
                return $this->_createCustomerAccount($customer, $customerEmailAddress);
            }
        }else{
            return $this->_createCustomerAccount($customer, $customerEmailAddress);
        }
    }

    /**
     * @param Customer $customer The customer to create the account for
     * @param EmailAddress $emailAddress The email address belonging to the customer for which we will use as the username
     * @return bool|CustomerAccount Returns whether or not the account was created
     */
    private function _createCustomerAccount(Customer $customer, EmailAddress $emailAddress){
        $customer_last_name = $customer->last_name;
        $account_email_address = $emailAddress->email_address;

        $existingEmailAccount = new CustomerAccount();
        $existingEmailAccount = $existingEmailAccount->where('email_address', $emailAddress->email_address)->first();
        if(!empty($existingEmailAccount) && $existingEmailAccount->exists){
            if($existingEmailAccount->is_activated == true){
                return Response(['success'=>false,'message'=>'An account with that email address already exists and is active.'], 201);
            }else{
                $customerTest = $existingEmailAccount->customer()->first();
                if($customerTest->id == $customer->id){
                    return Response(['success'=>true,'account'=>$existingEmailAccount], 201);
                }else{
                    return Response(['success'=>false,'message'=>'An account with that email address already exists for another customer.', 201]);
                }
            }
        }

        //Confirm the customer, and email address exist.
        if(!$customer->exists || !$emailAddress->exists){return false;}
        //Make sure the provided email address is associated with the provided customer.
        $email_address_customer = $emailAddress->customer()->first();
        if($email_address_customer->id != $customer->id){return false;}

        //A new account needs to be created for this customer.
        $existing_email_address_account = new CustomerAccount();
        $existing_email_address_account = $existing_email_address_account::where('email_address', $account_email_address)->first();
        $account_email_exists = (!empty($existing_email_address_account) && $existing_email_address_account->exists)?(true):(false);
        if($account_email_exists === true){
            //Cannot create an account because this email address is taken.
            return false;
        }
        //Create the customer's account
        $customer_account = new CustomerAccount();
        $customer_account->email_address = $account_email_address;
        $customer_account->pin = rand(1000, 9999);
        $customer_account->last_login_date = '1999-01-01 00:00:00';
        $customer_account->last_name = $customer_last_name;
        $customer_account->password = generate_random_string(20);
//        $customer_account->salt = generate_random_string(20);
        $customer_account->password = password_hash(generate_random_string(20), PASSWORD_BCRYPT);

        if (!empty($customer->birthday)) {
            $customer_account->birthday = $customer->birthday;
        }
        $customer_account->activated = 0;
        $customer_account->ban_expiration = '1999-01-01 00:00:00';
        event(new CustomerAccountCreated($customer_account));
        $saveSuccess = $customer_account->customer()->associate($customer)->save();
        return ( $saveSuccess == true)?(Response(['success'=>true,'account'=>$customer_account], 201)):(Response(['success'=>false,'message'=>'An error has occurred.', 201]));
    }

    /**
     * @param CustomerAccount $customerAccount The account to be disabled
     * @return bool whether or not the account was disabled
     */
    function disableCustomerAccount(CustomerAccount $customerAccount){
        $customerAccount->is_active = 0;
        event(CustomerAccountDisabled::class);
        return $customerAccount->delete();
    }
}