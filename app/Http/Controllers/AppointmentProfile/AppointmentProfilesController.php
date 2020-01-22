<?php

namespace App\Http\Controllers\AppointmentProfile;

use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Models\Customers\Customer;
use App\Repositories\Appointments\AppointmentEventsRepository;
use Illuminate\Http\Request;
use App\Repositories\Customers\CustomerRepository;
use App\Http\Controllers\Api\CustomerController;

class AppointmentProfilesController extends Controller
{
    private $AppointmentEventsRepository;
    private $CustomerController;
    private $CustomerRepository;

    function __construct()
    {
        /** @var  AppointmentEventsRepository */
        $this->AppointmentEventsRepository = App::make(AppointmentEventsRepository::class);
        /** @var  CustomerRepository */
        $this->CustomerRepository = App::make(CustomerRepository::class);
        /** @var  CustomerController */
        $this->CustomerController = App::make(CustomerController::class);
    }

    public function customerList(Request $request) {
        $firstName = $request->input('firstName', false);
        $lastName = $request->input('lastName', false);
        $customers = $this->CustomerRepository->getCustomersList($firstName, $lastName);
        if (\Request::ajax()) {
            return \Response::json(\View::make('appointmentCustomer.customersListTable', array('customers' => $customers))->render());
        }
        return view('appointmentCustomer/customersList', ['customers'=>$customers]);
    }

    public function getCustomer($CID) {
        $customer = Customer::findOrFail($CID);
        foreach ($customer->appointments as $appointment) {
            $appointment->status = $this->AppointmentEventsRepository->determineAppointmentStatus($appointment->id);
        }
        return view('appointmentCustomer/customerProfile')->with(['customer' => $customer]);
    }

    public function updateCustomerForm($CID) {
        $customer = Customer::where('id', $CID)->firstOrFail()->load('phoneNumbers')->load('emailAddresses');
        //Create new customer object with information that should be returned for update
        $customerInformation = (object) array(
            'id' => $customer->id,
            'first_name' => $customer->first_name,
            'middle_initial' => $customer->middle_initial,
            'last_name' => $customer->last_name,
            'email_address' => '',
            'phone_number' => ''
        );

        //Iterate through phone numbers looking for primary
        foreach($customer->phoneNumbers as $phoneNumber) {
            if($phoneNumber->is_primary == '1' && $phoneNumber->is_active == '1') {
                $customerInformation->phone_number = $phoneNumber->phone_number;
            }
        }
        //Iterate through email addresses looking for primary
        foreach($customer->emailAddresses as $emailAddress) {
            if($emailAddress->is_primary == '1' && $emailAddress->is_active == '1') {
                $customerInformation->email_address = $emailAddress->email_address;
            }
        }
        return view('appointmentCustomer/updateCustomer')->with(['customerInfo' => $customerInformation]);
    }

    public function submitCustomerUpdate(Request $request) {
        $customerInformation = array(
            'customerID' => $request->input('customerID'),
            'phoneNumber' => $request->input('phoneNumber'),
            'emailAddress' => $request->input('emailAddress'),
            'firstName' => $request->input('firstName'),
            'middleInitial' => $request->input('middleInitial'),
            'lastName' => $request->input('lastName')
        );

        $updatedCustomer = $this->CustomerRepository->updateCustomer($customerInformation);
        if(!empty($updatedCustomer)) {
            return redirect('/customers/' . $updatedCustomer->id);
        }
        return false;
    }
}