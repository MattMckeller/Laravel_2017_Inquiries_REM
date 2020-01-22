<?php
namespace App\Repositories\Inquiries;

use Illuminate\Http\Request;

//Models
use App\Models\Inquiries\Inquiry;
use App\Models\Appointments\Appointment;
use App\Models\Inquiries\InquiryLabel;
use App\Models\Inquiries\InquirySource;
use App\Models\Inquiries\InquirySourceOption;
use App\Models\Inquiries\InquirySourceSelection;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AnswerController;
use App\Models\Customers\Organization;
use App\Models\Inquiries\OptionChoice;
use App\Models\Inquiries\Question;
use App\Models\Inquiries\QuestionOptionSelection;
use App\Models\Inventory\Location;
use App\Models\Marketing\BrandExposure;


//Restful API Controllers
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\EmailAddressController;
use App\Http\Controllers\Api\InquiryController;
use App\Http\Controllers\Api\InquiryEventController;
use App\Http\Controllers\Api\InquiryNoteController;
use App\Http\Controllers\Api\PhoneNumbersController;

class InquiryStatusRepository
{
    use InquiryStatusResponses, AppointmentStatusChecks, InquiryValidator;

    public function getLeadStatus($inquiryID){
        $inquiry = new Inquiry;
        $inquiry = $inquiry::findOrFail($inquiryID);
        if(empty($inquiry->status)){
            return response('Error, no current status.'); 
        }else{
            return response($inquiry->status);
        }
    }

    public function setLeadStatus($inquiryID, $status){
        $inquiry = new Inquiry;
        $inquiry = $inquiry::findOrFail($inquiryID);
        if(!$this->isValidLeadStatus($status)){
            return response('Error, status provided improperly.');
        }
        $inquiry->status = $status;
        $inquiry->save();

        return response($inquiry);
    }

    public function getInquiryAppointmentStatus($inquiryID){
        $inquiry = new Inquiry;
        $inquiry = $inquiry::findOrFail($inquiryID);
        $appointments = $inquiry->appointment()->get();
        $scheduled = 0;
        $confirmed = 0;
        foreach($appointments as $appointment){
            $scheduled = ($this->isAppointmentScheduled($appointment))?($scheduled+1):($scheduled);
            $confirmed = ($this->isAppointmentConfirmed($appointment))?($confirmed+1):($confirmed);
        }

        $responseData = array('scheduledCount'=>$scheduled, 'confirmedCount'=>$confirmed);
        return response($responseData);
    }

    
}