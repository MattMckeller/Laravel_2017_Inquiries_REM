<?php
namespace App\Repositories\Events;
use App\Models\Auth\Team\UserAccount;
use App\Models\Customers\Customer;
use App\Models\Inquiries\InquirySourceSelection;

class EventInstance
{
    var $type = null;
    var $name = null;
    var $timestamp = null;
    var $user = null;
    var $customer = null;
    var $data = null;

    function __construct(){
        return $this;
    }

    function getName(){
        return $this->name;
    }
    function getType(){
        return $this->type;
    }
    function getTimestamp(){
        return $this->type;
    }
    function getUser(){
        return $this->user;
    }
    function getCustomer(){
        return $this->customer;
    }
    function getData($key=null){
        return (!empty($key))? ( $this->data[$key] ) : ( $this-> data );
    }
    function setInquirySource(InquirySourceSelection $val) {
        $this->inquirySource = $val->inquirySource->type;
    }
    function setAgentContacted($val) {
        $this->agentContacted = $val;
    }
    function setName($val){
        $this->name = $val;
    }
    function setType($val){
        $this->type = $val;
    }
    function setTimestamp($val){
        $this->timestamp = $val;
    }
    function setUser(UserAccount $val){
        $this->user = (!empty($val->userAccountInformation()->first())) ? $val->userAccountInformation()->first()->toArray() : '';
    }
    function setCustomer(Customer $val){
        $val->emailAddresses;
        $val->addresses;
        $val->phoneNumbers;
        $this->customer = $val->toArray();
    }
    function setData($key, $val=null){
        if(is_array($key)) {
            $this->data = $key;
        }else{
            $this->data[$key] = $val;
        }
    }

}