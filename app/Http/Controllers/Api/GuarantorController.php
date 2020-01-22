<?php

namespace App\Http\Controllers\Api;

use App\Models\Customers\EmailAddress;
use App\Models\Customers\PhoneNumber;
use App\Repositories\Customers\GuarantorRepository;
use Illuminate\Http\Request;
use App\Models\Customers\Guarantor;
use Illuminate\Support\Facades\App;
use Validator;

use App\Http\Controllers\Controller;

class GuarantorController extends Controller
{
    private $GuarantorRepository;
    private $errors;

    public function __construct()
    {
        /** @var  GuarantorRepository */
        $this->GuarantorRepository = App::make(GuarantorRepository::class);
    }

    private $rules = array(
        'emailAddress' => 'email',
        'phoneNumber' => 'phoneRegex',
        'streetAddress1' => 'addressRegex',
        'streetAddress2' => 'addressRegex',
        'city' => 'addressRegex',
        'state' => 'state',
        'country' => 'country',
        'zip' => 'zip'
    );

    public function validateInput($data) {
        //Make a new Validator object
        $validator = Validator::make($data, $this->rules);
        //Check for a validation fail
        if($validator->fails()) {
            //Store errors and return
            $this->errors = $validator->errors();
            return false;
        }
        return true;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Response(Guarantor::all(), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $first_name = $request->input('firstName', null);
        $last_name = $request->input('lastName', null);
        $is_active = $request->input('isActive', 1);
        $is_verified = $request->input('isVerified', 0);
        $address = $request->input('address', false);
        $phoneNumber = $request->input('phoneNumber', false);
        $emailAddress = $request->input('emailAddress', false);

        $guarantor = $this->GuarantorRepository->saveGuarantor($first_name, $last_name, $is_active, $is_verified);
        if ($address) {$this->GuarantorRepository->attachAddress($address, $guarantor);}
        if ($emailAddress) {$this->GuarantorRepository->attachEmail($emailAddress, $guarantor);}
        if ($phoneNumber) {$this->GuarantorRepository->attachPhone($phoneNumber, $guarantor);}

        return response($guarantor, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function updateGuarantor(Request $request) {
        $saveAttempt = array(
            'errors' => array(),
            'success' => true
        );

        $guarantorInformation = $request->input('guarantorInformation');
        //Run validation rules against the provided data
        $validationAttempt = $this->validateInput($guarantorInformation);
        if($validationAttempt == true) {
            $guarantorID = $guarantorInformation['guarantorID'];
            $guarantor = Guarantor::where('id', $guarantorID)->firstOrFail()->load('phoneNumbers')->load('emailAddresses');
            $guarantorPhoneNumbers = $guarantor->phoneNumbers;
            $guarantorEmailAddresses = $guarantor->emailAddresses;

            if(!empty($guarantorInformation['guarantorFirstName'])) {
                $guarantor->first_name = $guarantorInformation['guarantorFirstName'];
            }
            if(!empty($guarantorInformation['guarantorLastName'])) {
                $guarantor->last_name = $guarantorInformation['guarantorLastName'];
            }
            if(!empty($guarantorInformation['guarantorEmailAddress'])) {
                foreach($guarantorEmailAddresses as $guarantorEmailAddress) {
                    if($guarantorEmailAddress->is_active == 1 && $guarantorEmailAddress->is_primary == 1) {
                        $guarantorEmailAddress->is_active = 0;
                        $guarantorEmailAddress->is_primary = 0;
                        $guarantorEmailAddress->save();
                    }
                }

                $emailAddress = new EmailAddress();
                $emailAddress->guarantor_id = $guarantorID;
                $emailAddress->is_primary = 1;
                $emailAddress->is_active = 1;
                $emailAddress->email_address = $guarantorInformation['guarantorEmailAddress'];
                $emailAddress->save();
            }
            if(!empty($guarantorInformation['guarantorPhoneNumber'])) {
                foreach($guarantorPhoneNumbers as $guarantorPhoneNumber) {
                    if($guarantorPhoneNumber->is_active == 1 && $guarantorPhoneNumber->is_primary == 1) {
                        $guarantorPhoneNumber->is_active = 0;
                        $guarantorPhoneNumber->is_primary = 0;
                        $guarantorPhoneNumber->save();
                    }
                }
                $phoneNumber = new PhoneNumber();
                $phoneNumber->guarantor_id = $guarantorID;
                $phoneNumber->is_primary = 1;
                $phoneNumber->is_active = 1;
                $phoneNumber->phone_number = $guarantorInformation['guarantorPhoneNumber'];
                $phoneNumber->save();
            }

            $saved = $guarantor->save();
            if(!$saved) {
                array_push($saveAttempt['errors'], 'address save failed');
                $saveAttempt['success'] = false;
            }
            $updatedGuarantor = Guarantor::where('id', $guarantorID)->get()->load('emailAddresses')->load('phoneNumbers');
            $saveAttempt['data'] = $updatedGuarantor;
            return $saveAttempt;
        }
        else {
            return response($this->errors, 500);
        }

    }
}
