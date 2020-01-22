<?php
namespace App\Repositories\Inquiries;
use App\Models\Auth\Team\UserAccountInformation;
use App\Models\Inquiries\Inquiry;
use \DateTime;
use \DateInterval;
use App\Models\Customers\Customer;

trait InquirySearching
{
    function getInquiryList($userRole, $userId, $inquiryListStartTime = false, $inquiryListEndTime = false){
        $inquiryList = null;
        $inquiryListEndTime = $this->getInquiryListEndTime($inquiryListEndTime);
        $inquiryListStartTime = $this->getInquiryListStartTime($inquiryListStartTime);
        //Find inquiries based on the provided user role/user id
        switch($userRole) {
            case "admin":
            case "inquiry_manager":
                $inquiryList = Inquiry::where('inquiry_timestamp', '>', $inquiryListStartTime)->where('inquiry_timestamp', '<=', $inquiryListEndTime)->has('Customer')->get();
                break;
            case "inquiry_representative":
                //Query for inquiries associated with the specified user
                $inquiryList = Inquiry::where('user_account_id', $userId)->orWhereNull('user_account_id')->where('inquiry_timestamp', '>', $inquiryListStartTime)->where('inquiry_timestamp', '<=', $inquiryListEndTime)->has('Customer')->get();
                break;
            default:
                return false;
        }
        foreach($inquiryList as $inquiry){
            $this->calculateIndex($inquiry);
        }

        return $inquiryList;
    }

    private function getInquiryListEndTime($endIntervalString = false){
        if($endIntervalString == false){
            $Now = new DateTime("now");
            return ($Now->format('Y-m-d H:i:s'));
        }else{
            $Now = new DateTime("now");
            $timeAgoInterval = new DateInterval($endIntervalString);
            $timeAgo = $Now->sub($timeAgoInterval);
            return ($timeAgo->format('Y-m-d H:i:s'));
        }
    }

    private function getInquiryListStartTime($startIntervalString = 'P2M'){
        $Now = new DateTime("now");
        $startIntervalString = $startIntervalString ?: 'P2M';
        $timeAgoInterval = new DateInterval($startIntervalString);
        $TwoMonthsAgo = $Now->sub($timeAgoInterval);
        return ($TwoMonthsAgo->format('Y-m-d H:i:s'));
    }

    function getAgentLeads($userAccountInformationID) {
        $userAccountInformation = UserAccountInformation::findOrFail($userAccountInformationID);
        if (!($userAccount = $userAccountInformation->userAccount)) {
            return null;
        }
        if(!$inquiries = $userAccount->inquiries) {
            return null;
        }
        return $inquiries;
    }
}