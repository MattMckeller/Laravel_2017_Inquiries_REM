<?php
namespace App\Repositories\Customers;
use App\Libraries\DoubleMetaPhone;
use App\Models\Customers\EmailAddress;
use App\Models\Customers\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Customers\Customer;
use App\Models\Customers\Address;
use App\Models\Inventory\UnitStyle;

trait CustomerLikes
{
    function addLike(Customer $customer, UnitStyle $unitStyle){
        $customer->unitStyleLikes()->syncWithoutDetaching([$unitStyle->id]);
        return true;
    }

    function removeLike(Customer $customer, UnitStyle $unitStyle){
        $customer->unitStyleLikes()->detach($unitStyle);
        return true;
    }

    function getLikes(Customer $customer){
        return $customer->unitStyleLikes()->get();
    }

}