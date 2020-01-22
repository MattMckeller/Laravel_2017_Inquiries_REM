<?php

namespace App\Http\Controllers\Api;

use App\Models\Inquiries\InquirySource;
use Illuminate\Http\Request;
use App\Models\Inquiries\InquirySourceOption;

use App\Http\Controllers\Controller;

class InquirySourceOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Response(InquirySourceOption::all(), 200);
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

    public function getInquirySourceInquirySourceOptions(Request $request) {
        $inquirySourceID = $request->inquirySourceID;
        $inquirySource = InquirySource::where('id', $inquirySourceID)->firstOrFail()->load('inquirySourceOption');
        return $inquirySource;
    }

    public function getInquirySourceOptionsFromInquirySourceText(Request $request) {
        $inquirySourceName = $request->input('inquirySourceName');
        $inquirySource = InquirySource::where('type', $inquirySourceName)->firstOrFail()->load('inquirySourceOption');
        return $inquirySource->inquirySourceOption;
    }

    public function addInquirySourceOption(Request $request) {
        $inquirySourceID = $request->input('inquirySourceID', false);
        $inquirySourceOptionText = $request->input('inquirySourceOptionText', false);

        if($inquirySourceID != false && $inquirySourceOptionText != false) {
            //Create the inquiry source option name from the provided text
            $inquirySourceOptionName = strtolower(str_replace(" ", "_", $inquirySourceOptionText));

            //Create new inquiry source option
            $inquirySourceOption = new InquirySourceOption();
            $inquirySourceOption->name = $inquirySourceOptionName;
            $inquirySourceOption->display_name = ucwords($inquirySourceOptionText);
            $inquirySourceOption->save();

            //Find the inquiry source from the provided ID
            $inquirySource = InquirySource::find($inquirySourceID);
            $inquirySource->inquirySourceOption()->attach($inquirySourceOption->id);

            return response(array('success' => 'true'), 201);
        }
        return response(array('success' => 'false', 'errors' => ['Unable to save option. Data not provided properly.']), 201);
    }
}
