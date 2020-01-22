<?php

namespace App\Http\Controllers\Api;

use App\Models\Customers\Customer;
use App\Models\Other\School;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SchoolController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

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
        //
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

    public function getAllSchools() {
        $schools = School::all();
        return response($schools, 201);
    }

    public function updateCustomerSchools(Request $request) {
        $customerInformation = $request->input('customerInformation');
        $customerID = $customerInformation['customerID'];
        $schoolID = $customerInformation['schoolID'];
        $yearInSchool = $customerInformation['yearInSchool'];

        //If the year in school has been provided, update the application
        if(!empty($yearInSchool)) {
            $customerApplication = Customer::findOrFail($customerID)->applications()->firstOrFail();
            $customerApplication->year_in_school = $yearInSchool;
            $customerApplication->save();
        }
        //If the school ID has been provided, update the customer's schools
        if(!empty($schoolID)) {
            $customer = Customer::findOrFail($customerID);
            $customer->schools()->sync([$schoolID]);
        }
        $updatedCustomer = Customer::findOrFail($customerID)->load('applications')->load('schools');
        return response($updatedCustomer, 201);
    }
}
