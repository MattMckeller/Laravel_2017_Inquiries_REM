<?php
namespace App\Repositories\Inquiries;

trait InquiryStatusResponses
{
    function noAppointment(){
        return false;
    }

    function appointmentScheduled(){
        return true;
    }

    function notConfirmed(){
        return false;
    }

    function confirmed(){
        return true;
    }
}