<?php
namespace App\Repositories\Inquiries;
use App\Events\InquiryClaimEvent;
use App\Models\Inquiries\Inquiry;
use App\Models\Auth\Team\UserAccount;
use App\Models\Customers\Customer;
use App\Models\Inquiries\InquiryClaimingEvent;
use \DateTime;
use \DateInterval;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\App;

trait InquiryClaiming
{
    function claim(Inquiry $inquiry){
        $userAccount = $this->getCurrentUserAccount();
        if(!$this->verifyInquiryEligibility($inquiry)
            || !$this->verifyAccountEligibility($userAccount, 'claim')){
            $this->releaseHold($inquiry);
            return false;
        };
        $this->placeHold($inquiry);
        if($this->checkForExistingClaims($userAccount)){
            $this->handleExistingClaims($userAccount);
        };
        return $this->_claim($inquiry, $userAccount);
    }

    function forceClaim(Inquiry $inquiry){
        $userAccount = $this->getCurrentUserAccount();
        if(!$this->verifyAccountEligibility($userAccount, 'forceClaim')){
            return false;
        }
        $this->placeHold($inquiry);
        if($this->checkForExistingClaims($userAccount)){
            $this->handleExistingClaims($userAccount);
        };
        return $this->_claim($inquiry, $userAccount);
    }

    function temporaryClaim(Inquiry $inquiry){
        $userAccount = $this->getCurrentUserAccount();
        if(!$this->verifyInquiryEligibility($inquiry)
            || !$this->verifyAccountEligibility($userAccount, 'claim')){
            $this->releaseHold($inquiry);
            return false;
        };
        $this->placeHold($inquiry);
        if($this->checkForExistingClaims($userAccount)){
            $this->handleExistingClaims($userAccount);
        };
        return $this->_claim($inquiry, $userAccount, $this->getTemporaryExpiration());
    }

    function assign(Inquiry $inquiry, UserAccount $userAccount){
        $actingUser = $this->getCurrentUserAccount();
        if(!$this->verifyAccountEligibility($actingUser, 'assign')){return false;}
        $this->placeHold($inquiry);
        if($this->checkForExistingClaims($userAccount)){
            $this->handleExistingClaims($userAccount);
        };
        return $this->_claim($inquiry, $userAccount);
    }

    function release(Inquiry $inquiry){
        $actingUser = $this->getCurrentUserAccount();
        $activeAgent = $this->getActiveAgent($inquiry);
        if(!$this->verifyAccountEligibility($actingUser, 'release')){return false;}
        if($actingUser != $activeAgent){
            trigger_error('Cannot release another users inquiry. To accomplish this you must use force release.');
            return false;
        }
        $this->_release($inquiry, $actingUser);
    }

    function forceRelease(Inquiry $inquiry){
        $actingUser = $this->getCurrentUserAccount();
        if(!$this->verifyAccountEligibility($actingUser, 'forceRelease')){return false;}

        $this->_release($inquiry, $actingUser);
    }

    function cleanAvailableInquiries(){
        $actingUser = $this->getCurrentUserAccount();
        $inquiriesToReset = $this->findInquiriesToReset();
        foreach($inquiriesToReset as $inquiry){
            $this->_release($inquiry, $actingUser);
        }
    }


    private function placeHold(Inquiry $inquiry){
        $queryInquiry = new Inquiry;
        $queryInquiry->where('id', $inquiry->id)->where('is_held', 0)->update(['is_held'=>1]);
        $verificationInquiry = new Inquiry;
        $verificationInquiry = $verificationInquiry->where('id', $inquiry->id)->first();
        return ($verificationInquiry->is_held == 1);
    }

    private function releaseHold(Inquiry $inquiry){
        $queryInquiry = new Inquiry;
        $queryInquiry->where('id', $inquiry->id)->where('is_held', 1)->update(['is_held'=>0]);
        $verificationInquiry = new Inquiry;
        $verificationInquiry = $verificationInquiry->where('id', $inquiry->id)->first();
        return ($verificationInquiry->is_held == 0);
    }

    private function checkForExistingClaims(UserAccount $userAccount){
        $inquiries = $userAccount->inquiries()->first();
        return (!empty($inquiries) && $inquiries->exists);
    }

    /**
     * @param UserAccount $userAccount the account for which inquiries will be disassociated
     * @return bool
     */
    private function handleExistingClaims(UserAccount $userAccount){
        //Do Nothing
//        $inquiries = $userAccount->inquiries()->get();
//        foreach($inquiries as $i){
//            $this->_release($i, $this->getCurrentUserAccount());
//        }
        return true;
    }

    private function verifyInquiryEligibility(Inquiry $inquiry){
        //Check if inquiry has active agent that is not expired
        //Check if inquiry is held
        //Check if the inquiry belongs to the current user
        $DBInquiry = new Inquiry();
        $userAccountForeign = $DBInquiry->userAccount()->getForeignKey();
        $user = $this->getCurrentUserAccount();
        $DBInquiry = $DBInquiry::where(function($query) use ($inquiry){
            $query->where('id', $inquiry->id)->where('is_held', 0)->where('agent_claim_expiration', null);
        })->orWhere(function($query)  use ($inquiry){
            $query->where('id', $inquiry->id)->where('is_held', 0)->where('agent_claim_expiration', '<=', nowTimestamp());
        })->orWhere(function($query)  use ($inquiry, $userAccountForeign, $user){
            $query->where('id', $inquiry->id)->where($userAccountForeign, $user->id);
        })->first();
        $eligible = (!empty($DBInquiry) && $DBInquiry->exists);
        if(!$eligible){trigger_error('The inquiry is not available for claiming at this time.');}
        return $eligible;
    }

    private function verifyAccountEligibility(UserAccount $userAccount, $action){
        $eligible = true;
        //todo verify account role and responsibilities
        //Now accounts can have multiple inquiries
//        $DBUserAccount = new UserAccount();
//        $inquiries = $DBUserAccount->where('id', $userAccount->id)->first()->inquiries()->first();
        //Check if the user already has an inquiry?
        if(!$eligible){trigger_error('Permission to claim an inquiry denied.');}
        return $eligible;
    }

    private function getCurrentUserAccount(){
        $auth = App::make('TeamAuth');
        return $auth->get_user();
    }

    private function getActiveAgent(Inquiry $inquiry){
        return $inquiry->userAccount()->first();

    }

    private function _claim(Inquiry $inquiry, UserAccount $userAccount, $expiration = null){
        //Determine the acting user account
        //Set active agent id to the provided user account
        //Set timestamp of claim to now
        //Set the expiration of this claim
        //Create log of the claim
        //Release hold
        $actingUser = $this->getCurrentUserAccount();
        $now = nowTimestamp();
        $expiration = (empty($expiration))?($this->getStandardExpiration()):($expiration);
        $inquiry = $inquiry->fresh();
        $inquiry->is_held = 0;
        $inquiry->agent_claim_timestamp = $now;
        $inquiry->agent_claim_expiration = $expiration;
        $inquiry->userAccount()->associate($userAccount);

        $claimEvent = new InquiryClaimingEvent();
        $claimEvent->userAccount()->associate($userAccount);
        $claimEvent->actingUser()->associate($actingUser);
        $claimEvent->expiration_timestamp = $expiration;
        $claimEvent->timestamp = $now;
        $claimEvent->is_claim = 1;
        $claimEvent->save();

        $inquiry->save();
        $inquiry->inquiryClaimingEvents()->save($claimEvent);
        $this->releaseHold($inquiry);
        event(new InquiryClaimEvent($inquiry));
        return $inquiry;
    }

    private function _shortClaim(Inquiry $inquiry, UserAccount $userAccount){
        //Determine the acting user account
        //Set active agent id to the provided user account
        //Set timestamp of claim to now
        //Set the expiration of this claim
        //Create log of the claim
        //Release hold
        $actingUser = $this->getCurrentUserAccount();
        $now = nowTimestamp();
        $expiration = $this->getExpiration();

        $inquiry = $inquiry->fresh();
        $inquiry->is_held = 0;
        $inquiry->agent_claim_timestamp = $now;
        $inquiry->agent_claim_expiration = $expiration;
        $inquiry->userAccount()->associate($userAccount);

        $claimEvent = new InquiryClaimingEvent();
        $claimEvent->userAccount()->associate($userAccount);
        $claimEvent->actingUser()->associate($actingUser);
        $claimEvent->expiration_timestamp = $expiration;
        $claimEvent->timestamp = $now;
        $claimEvent->is_claim = 1;
        $claimEvent->save();

        $inquiry->save();
        $inquiry->inquiryClaimingEvents()->save($claimEvent);
        $this->releaseHold($inquiry);
        return $inquiry;
    }

    private function _release(Inquiry $inquiry, UserAccount $actingUser){
        //Copy active agent id
        //Determine the acting user account
        //Set active agent id to null
        //Set timestamp to null
        //Set the expiration to null
        //Create log of the release with active and current users
        $userAccount = $inquiry->userAccount()->first();
        $now = nowTimestamp();
        $inquiry->is_held = 0;
        $inquiry->userAccount()->dissociate();
        $inquiry->agent_claim_timestamp = null;
        $inquiry->agent_claim_expiration = null;

        $claimEvent = new InquiryClaimingEvent();
        $claimEvent->userAccount()->associate($userAccount);
        $claimEvent->actingUser()->associate($actingUser);
        $claimEvent->expiration_timestamp = null;
        $claimEvent->timestamp = $now;
        $claimEvent->is_claim = 0;
        $claimEvent->save();

        $inquiry->save();
        $inquiry->inquiryClaimingEvents()->save($claimEvent);
    }

    private function findInquiriesToReset(){
        $inquiries = new Inquiry();
        $inquiries = $inquiries->where('agent_claim_expiration', '<', nowTimestamp())->get();
        return $inquiries;
    }

    private function getStandardExpiration(){
        $now = new \DateTime("now");
        $expiration = $now->add(new \DateInterval('P1Y'));
        return $expiration->format('Y-m-d H:i:s');
    }

    private function getTemporaryExpiration(){
        $now = new \DateTime("now");
        $expiration = $now->add(new \DateInterval('PT30M'));
        return $expiration->format('Y-m-d H:i:s');
    }

}