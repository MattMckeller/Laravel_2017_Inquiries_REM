<?php
namespace App\Repositories\Customers;

use Illuminate\Http\Request;

//Models
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

class CustomerRepository
{
    use CustomerSearch, CustomerUpdating, CustomerRetrieval, CustomerValidation, CustomerNotes, CustomerLikes, CustomerAppointments;
}