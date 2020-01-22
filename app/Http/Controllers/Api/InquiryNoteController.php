<?php

namespace App\Http\Controllers\Api;

use App\Models\Inquiries\InquiryEvent;
use App\Models\Inquiries\InquiryNote;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;

class InquiryNoteController extends Controller
{
    private $TeamAuth;

    function __construct(){
        $this->TeamAuth = App::make('TeamAuth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Response(InquiryNote::all(), 200);
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
        $inquiryID = $request->input('inquiry_id', false);
        $agentID = $request->input('agent_id', false);

        $inquiryNote = new InquiryNote();
        $inquiryNote->inquiry()->associate($request->input("inquiry_id"));
        //Associate the agent ID with the inquiry note
        $loggedInId = $this->TeamAuth->get_user()->id;
        $inquiryNote->userAccountInformation()->associate($loggedInId);
        $inquiryNote->text = $request->input("noteText");
        $inquiryNote->saveOrFail();

        return response($inquiryNote, 201);
    }

    public static function addInquiryNoteToInquiryEvent($noteInformation) {
        $inquiryID = $noteInformation['inquiryID'];
        $inquiryEventID = $noteInformation['inquiryEventID'];
        $noteText = $noteInformation['text'];

        $inquiryNote = new InquiryNote();
        $inquiryNote->inquiry()->associate($inquiryID);
        $inquiryNote->text = $noteText;
        //Associate the inquiry event with the inquiry note
        $inquiryNote->inquiryEvent()->associate($inquiryEventID);
        $saveAttempt = $inquiryNote->save();

        return $saveAttempt;
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
}
