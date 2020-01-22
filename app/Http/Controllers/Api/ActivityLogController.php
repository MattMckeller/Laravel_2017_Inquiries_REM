<?php

namespace App\Http\Controllers\Api;

use App\Models\Customers\Customer;
use App\Models\Inquiries\Inquiry;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;

class ActivityLogController extends Controller
{
    private $EventSearchRepository;
    function __construct(){
        $this->EventSearchRepository = App::make('EventSearchRepository');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getRecentActivity()
    {
        return response($this->EventSearchRepository->getAllEvents(), 200);
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
    public static function store(Request $request)
    {
        $inquiry = new Inquiry();
        $inquiry->saveOrFail();

        return response($inquiry, 201);
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

    /*
     * Function: Retrieve all customer information related
     * for the provided inquiry
     *
     * @param $request Request | the provided inquiry id for customer querying
     */
    public function getCustomerInquiryInformation(Request $request){
        $inquiryID = $request->input('inquiryID');
        return $this->InquiryRepository->getCustomerInquiryInformation($inquiryID);
    }

    public function getInquiryStatus(Request $request)
    {
        $inquiryId = $request->id;
        $inquiryInformation = array(
            'id' => $inquiryId
        );
        return json_encode($inquiryInformation);
    }
}
