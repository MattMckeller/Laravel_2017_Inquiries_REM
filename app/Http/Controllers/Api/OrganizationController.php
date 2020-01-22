<?php

namespace App\Http\Controllers\Api;

use App\Models\Customers\Customer;
use App\Models\Customers\Organization;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Libraries\DoubleMetaPhone;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $organization = new Organization();
        return response($organization->all(), 201);
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

    public function searchOrganizations(Request $request) {
        //Organizations matched from DB queries stored for return
        $allMatchedOrganizations = array();

        //create double metaphone from user input
        $doubleMetaPhone = new DoubleMetaPhone();
        $organizationDoubleMetaPhone = $doubleMetaPhone->CalculateDoubleMetaPhone($request->input('organizationInput'));
        unset($doubleMetaPhone);

        $organizationDoubleMetaPhonePrimary = $organizationDoubleMetaPhone['primary'];
        $organizationDoubleMetaPhoneSecondary = $organizationDoubleMetaPhone['secondary'];

        //Run query on DB for Jaro Winkler calculation for organizations
        $matchedOrganizationsPrimary = DB::select('SELECT *, jaro_winkler(:metaphone, `organization_dm_first`) AS partialName FROM organizations HAVING partialName > .85 ORDER BY partialName DESC', ['metaphone' => $organizationDoubleMetaPhonePrimary]);
        $matchedOrganizationsSecondary = DB::select('SELECT *, jaro_winkler(:metaphone, `organization_dm_second`) AS partialName FROM organizations HAVING partialName > .85 ORDER BY partialName DESC', ['metaphone' => $organizationDoubleMetaPhoneSecondary]);

        //Iterate through all matched organizations for primary and secondary calculation
        foreach($matchedOrganizationsPrimary as $matchedOrganization) {
            $allMatchedOrganizations[$matchedOrganization->id] = $matchedOrganization;
        }

        foreach($matchedOrganizationsSecondary as $matchedOrganization) {
            $allMatchedOrganizations[$matchedOrganization->id] = $matchedOrganization;
        }

        //Return all matched organizations
        return $allMatchedOrganizations;
    }

    public function addCustomerToOrganization(Request $request) {
        $saveStatus = array(
            'errors' => array(),
            'success' => true
        );

        $customerID = $request->input('customerID');
        $organizationID = $request->input('organizationID');

        $customer = Customer::where('id', $customerID)->firstOrFail();
        $customer->organizations()->attach($organizationID);
        $saveStatus['data'] = $customer->organizations()->get();

        return response($saveStatus, 201);
    }

    public function removeCustomerFromOrganization(Request $request) {
        $saveStatus = array(
            'errors' => array(),
            'success' => true
        );

        $customerID = $request->input('customerID');
        $organizationID = $request->input('organizationID');

        $organization = Organization::where('id', $organizationID)->firstOrFail();
        $organization->customer()->detach($customerID);
        $customer = Customer::where('id', $customerID)->firstOrFail()->load('organizations');
        $saveStatus['data'] = $customer->organizations;

        return $saveStatus;
    }

    public function addNewOrganization(Request $request) {
        $saveStatus = array(
            'errors' => array(),
            'success' => true
        );

        $organizationName = $request->input('name', false);
        if($organizationName) {
            //Create Double MetaPhone from user input
            $doubleMetaPhone = new DoubleMetaPhone();
            $organizationDoubleMetaPhone = $doubleMetaPhone->CalculateDoubleMetaPhone($organizationName);
            unset($doubleMetaPhone);

            $organizationDoubleMetaPhonePrimary = $organizationDoubleMetaPhone['primary'];
            $organizationDoubleMetaPhoneSecondary = $organizationDoubleMetaPhone['secondary'];

            //Store the new organization
            $organization = new Organization();
            $organization->name = $organizationName;
            $organization->organization_dm_first = $organizationDoubleMetaPhonePrimary;
            $organization->organization_dm_second = $organizationDoubleMetaPhoneSecondary;
            $organizationSaveAttempt = $organization->save();
            if($organizationSaveAttempt) {
                return $saveStatus;
            }
        }
        //Add error message to save status
        $saveStatus['success'] = false;
        array_push($saveStatus['errors'], 'Unable to create the new organization');
        return $saveStatus;
    }
}
