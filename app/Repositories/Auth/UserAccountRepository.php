<?php
namespace App\Repositories\Auth;
use \App\Models\Inquiries\Inquiry;
use \App\Models\Customers\Customer;
use App\Models\Auth\Team\UserAccount;

class UserAccountRepository
{
    public static function getAccountName(UserAccount $account){
        $accountName = "";
        if(!empty($account->id)){
            $userAccountInfo = $account->userAccountInformation()->first();
            $accountName = $userAccountInfo->first_name . " ". $userAccountInfo->last_name;
        }
        return $accountName;
    }
}