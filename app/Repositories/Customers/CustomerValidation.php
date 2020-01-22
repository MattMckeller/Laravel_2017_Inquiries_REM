<?php
namespace App\Repositories\Customers;
use App\Libraries\DoubleMetaPhone;
use App\Models\Customers\EmailAddress;
use App\Models\Customers\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Customers\Customer;
use App\Models\Customers\Address;
use Illuminate\Support\Facades\Validator;

trait CustomerValidation
{
    function minimalValidation($data)
    {
        $validator = Validator::make($data,CustomerValidationRequirements::onlySyntax());
        return !($validator->fails());
    }

}

class CustomerValidationRequirements{
    static function onlySyntax() {
        return [
            "inquirySource" => "nameRegex",
            "email" => "email",
            "phone" => "phoneRegex",
            "firstName" => "nameRegex",
            "lastName" => "nameRegex",
            "middleInitial" => "regex: /[a-zA-Z]/",
            "streetAddress" => "addressRegex",
            "streetAddress2" => "addressRegex",
            "city" => "addressRegex",
            "state" => "state",
            "country" => "country",
            "zip" => "zip",
            "birthday" => "date",
            "gender" => "regex: /^[a-zA-Z0-9 ,.'-]+$/",
            'locationPreferences.*' => 'integer',
            'brandExposure.*' => 'integer',
            'organization' => 'integer',
            'desired_bedroom_count.*' => 'integer',
            'roommate_matching' => 'integer',
            'has_pet' => 'integer',
            'wants_furniture' => 'integer',
            'wants_utilities' => 'integer',
            'customerAppointment' => 'integer'
        ];
    }

}