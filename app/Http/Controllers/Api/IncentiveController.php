<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customers\Customer;
use App\Models\Marketing\Incentive;
use Carbon\Carbon;
use Illuminate\Http\Request;

class IncentiveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //Get current date to compare against expiration
        $currentDate = new Carbon();

        $incentives = Incentive::where('expiration_datetime', '>=', $currentDate)->get();
        return response($incentives, 201);
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

    public function addIncentive(Request $request) {
        $saveStatus = array(
            'errors' => array(),
            'success' => true
        );
        $customerID = $request->input('customerID');
        $incentiveID = $request->input('incentiveID');

        $customer = Customer::where('id', $customerID)->firstOrFail();
        $customer->incentives()->attach($incentiveID, ['incentive_offer_date' => date('Y-m-d h:i:s')]);

        $saveStatus['data'] = $customer->incentives()->get();
        return response($saveStatus, 201);
    }

    public function removeIncentive(Request $request) {
        $saveStatus = array(
            'errors' => array(),
            'success' => true
        );

        $customerID = $request->input('customerID');
        $incentiveID = $request->input('incentiveID');

        $incentive = Incentive::where('id', $incentiveID)->firstOrFail();
        $incentive->customers()->detach($customerID);
        $customer = Customer::where('id', $customerID)->firstOrFail()->load('incentives');
        $saveStatus['data'] = $customer->incentives;

        return response($saveStatus, 201);
    }

    public function addNewIncentive(Request $request) {
        $saveAttempt = array(
            'errors' => array(),
            'success' => true
        );

        //Verify that all request input has been provided
        $incentiveType = $request->input('incentiveType', false);
        $category = $request->input('category', false);
        $amount = $request->input('amount', false);

        //Determine the incentive type to be added
        switch($incentiveType) {
            case "dollar":
                //Format the name for the incentive
                $incentiveName = $amount . '_' . $category;
                $displayNameSubText = ($category == "rent") ? " off " : '';
                $incentiveDisplayName = "$" . $amount . $displayNameSubText . $category;
                break;
            case "percentage":
                $incentiveName = $amount . '_' . $category;
                //Format display name
                $incentiveDisplayNameArray = explode("_", $category);
                $incentiveDisplayNameCategory = '';
                foreach($incentiveDisplayNameArray as $incentiveDisplayNameText) {
                    $incentiveDisplayNameCategory .= ' ' . ucwords($incentiveDisplayNameText);
                }
                $incentiveDisplayName = $amount . '%' . ' off' . $incentiveDisplayNameCategory;
                break;
            case "waive":
                $incentiveName = "waive_" . $category;
                //Format display name
                $incentiveDisplayNameArray = explode("_", $category);
                $incentiveDisplayName = 'Waive';
                foreach($incentiveDisplayNameArray as $incentiveDisplayNameText) {
                    $incentiveDisplayName .= ' ' . ucwords($incentiveDisplayNameText);
                }
                break;
            default:
                array_push($saveAttempt['errors'], 'Error: Unable to add new incentive. Invalid Incentive type.');
                return $saveAttempt;
                break;
        }
        //Store the new incentive
        $incentive = new Incentive();
        $incentive->name = $incentiveName;
        $incentive->display_name = $incentiveDisplayName;
        $incentive->standard_duration = "48";
        $incentiveSaveAttempt = $incentive->save();
        //Return the status of the incentive save attempt
        if(!$incentiveSaveAttempt) {
            $saveAttempt['success'] = false;
            array_push($saveAttempt['errors'], 'Unable to add incentive. Data provided improperly');
        }
        return $saveAttempt;
    }
}
