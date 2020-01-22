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

trait CustomerAppointments
{
    public function getAppointmentCustomers($customerInformation)
    {
        $with_appointments = array_key_exists('with_appointments', $customerInformation) ? $customerInformation['with_appointments'] : false;
        $id = array_key_exists('id', $customerInformation) ? $customerInformation['id'] : false;
        $first_name = array_key_exists('first_name', $customerInformation) ? $customerInformation['first_name'] : false;
        $middle_initial = array_key_exists('middle_initial', $customerInformation) ? $customerInformation['middle_initial'] : false;
        $last_name = array_key_exists('last_name', $customerInformation) ? $customerInformation['last_name'] : false;
        $gender = array_key_exists('gender', $customerInformation) ? $customerInformation['gender'] : false;
        $birthday = array_key_exists('birthday', $customerInformation) ? $customerInformation['birthday'] : false;
        $order = array_key_exists('order', $customerInformation) ? $customerInformation['order'] : false;
        $offset = array_key_exists('offset', $customerInformation) ? $customerInformation['offset'] : false;
        $limit = array_key_exists('limit', $customerInformation) ? $customerInformation['limit'] : false;

        return Customer::
              with('emailAddresses')
            ->with('phoneNumbers')
            ->when($with_appointments, function ($query) {
                return $query->with('appointments');
            })
            ->when($id, function($query) use($id) {
                return $query->where('id', $id);
            })
            ->when($first_name, function($query) use($first_name) {
                return $query->where('first_name', $first_name);
            })
            ->when($middle_initial, function($query) use($middle_initial) {
                return $query->where('middle_initial', $middle_initial);
            })
            ->when($last_name, function($query) use($last_name) {
                return $query->where('last_name', $last_name);
            })
            ->when($gender, function($query) use($gender) {
                return $query->where('gender', $gender);
            })
            ->when($birthday, function($query) use($birthday) {
                return $query->where('birthday', $birthday);
            })
            ->when($order, function ($query) use ($order) {
                return $query->orderby($order->sort, $order->direction);
            })
            ->when($offset, function ($query) use ($offset) {
                return $query->offset($offset);
            })
            ->when($limit, function ($query) use ($limit) {
                return $query->limit($limit);
            })
            ->get();
    }

    public function getCustomerById($cid) {
        $customer = Customer::where('id', $cid)->load('emailAddresses')->load('phoneNumbers')->get();
        return $customer;
    }

    public function getCustomersList($firstName = false, $lastName = false) {
        $customers = Customer::with('emailAddresses', 'phoneNumbers')
            ->when($firstName, function ($query) use ($firstName) {
                return $query->where('first_name', 'like', $firstName . '%');
            })
            ->when($lastName, function ($query) use ($lastName) {
                return $query->where('last_name', 'like', $lastName . '%');
            })
            ->OrderBy('first_name')
            ->paginate(15);
        return $customers;
    }

    public function getCustomerUpdateProfile($cid) {
        $customerInfo = $this->getCustomerById($cid);
        return $customerInfo;
    }
}