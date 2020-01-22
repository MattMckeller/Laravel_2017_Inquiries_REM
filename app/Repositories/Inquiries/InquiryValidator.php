<?php
namespace App\Repositories\Inquiries;

trait InquiryValidator
{
    private $validStatuses = array(
        'cold',
        'warm',
        'hot'
    );
    
    function isValidLeadStatus($status){
        return (in_array($status, $this->validStatuses))?(true):(false);
    }
}