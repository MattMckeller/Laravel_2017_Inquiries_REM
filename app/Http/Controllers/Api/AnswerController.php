<?php

namespace App\Http\Controllers\Api;

use App\Models\Inquiries\Answer;
use App\Models\Inquiries\OptionChoice;
use App\Models\Inquiries\Question;
use App\Models\Inquiries\QuestionOptionSelection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Customers\Customer;
use Illuminate\Support\Facades\App;

class AnswerController extends Controller
{
    private $InquiryRepository;
    function __construct()
    {
        $this->InquiryRepository = App::make('InquiryRepository');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public static function store(Request $request)
    {
        $answer = new Answer();
        return response($answer, 201);
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

    public function getCustomerAnswers(Request $request = null, $customerID = false) {
        $customerAnswers = array();
        //If the customer ID has not been provided, set from request
        $customerID = $customerID ? $customerID : $request->input('customerID');

        $answers = Answer::where('customer_id', $customerID)->get()
            ->load('questionOptionSelection.optionChoice.question.optionChoice')
            ->load('questionOptionSelection.question.questionType')
            ->load('questionOptionSelection.question.inputType');
        foreach($answers as $answer) {
            $optionChoicesSelected = (!empty($answer->questionOptionSelection->optionChoice)) ? $answer->questionOptionSelection->optionChoice : null;
            $questionSubtext = $answer->questionOptionSelection->question->question_subtext;
            if(!array_key_exists($questionSubtext, $customerAnswers)) {
                $customerAnswers[$questionSubtext] = array(
                    'question' => $answer->questionOptionSelection->question->toArray(),
                    'customer_id' => $answer->customer_id,
                    'answer_int' => $answer->answer_int,
                    'answer_text' => $answer->answer_text,
                    'question_type' => $answer->questionOptionSelection->question->questionType->toArray(),
                    'question_input_type' => $answer->questionOptionSelection->question->inputType->toArray()
                );
            }

            //If no option choices have been selected, create index and push option choice selection
            if(!array_key_exists('option_choices_selected', $customerAnswers[$questionSubtext])) {
                $customerAnswers[$questionSubtext]['option_choices_selected'] = array();
            }
            array_push($customerAnswers[$questionSubtext]['option_choices_selected'], $optionChoicesSelected->toArray());
            $customerAnswers[$questionSubtext]['option_choices'] = $answer->questionOptionSelection->optionChoice->question[0]->optionChoice;
        }

        //Retrieve all questions to compare against existing answers
        $questions = Question::all()->load('optionChoice')->load('questionType')->load('inputType');
        foreach($questions as $question) {
            if(empty($customerAnswers[$question->question_subtext])) {
                $customerAnswers[$question->question_subtext] = array(
                    'id' => $question->id,
                    'question' => $question,
                    'customer_id' => $customerID,
                    'answer_int' => '',
                    'answer_text' => '',
                    'option_choices_selected' => '',
                    'option_choices' => $question->optionChoice,
                    'question_type' => $question->questionType,
                    'question_input_type' => $question->inputType
                );
            }
        }
        return $customerAnswers;
    }

    public static function addNewCustomerAnswers(Request $request) {
        $customerID = $request->input('customerID');
        $customerPreferences = $request->input('customerPreferences');

        foreach($customerPreferences as $customerPreference) {
            //Find the question based on the customer preference name
            $questionName = null;
            if($customerPreference['type'] == 'checkbox') {
                $questionNameArray = explode("[]", $customerPreference['name']);
                $questionName = $questionNameArray[0];
            }
            else {
                $questionName = $customerPreference['name'];
            }
            //Find the question from the provided preference name
            $optionChoiceFound = false;
            $question = Question::where('question_subtext', $questionName)->firstOrFail()->load('optionChoice');
            $questionOptionChoices = $question->optionChoice;
            foreach($questionOptionChoices as $questionOptionChoice) {
                //If a single value has been provided, create new answer
                if(!is_array($customerPreference['value'])) {
                    if($questionOptionChoice['option_choice_name'] == $customerPreference['value']) {
                        $optionChoiceFound = true;
                        //Retrieve question option choice id for answer
                        $questionOptionChoiceID = QuestionOptionSelection::where('question_id', $question->id)->where('option_choice_id', $questionOptionChoice->id)->firstOrFail();
                        //Store the new answer for the customer
                        $newAnswer = new Answer();
                        $newAnswer->customer_id = $customerID;
                        $newAnswer->question_option_id = $questionOptionChoiceID->id;
                        $newAnswer->save();
                    }
                }
                //Iterate through provided customer preference values
                else {
                    foreach($customerPreference['value'] as $providedValue) {
                        if($questionOptionChoice['option_choice_name'] == $providedValue) {
                            $optionChoiceFound = true;
                            //Retrieve question option choice id for answer
                            $questionOptionChoiceID = QuestionOptionSelection::where('question_id', $question->id)->where('option_choice_id', $questionOptionChoice->id)->firstOrFail();
                            //Store the new answer for the customer
                            $newAnswer = new Answer();
                            $newAnswer->customer_id = $customerID;
                            $newAnswer->question_option_id = $questionOptionChoiceID->id;
                            $newAnswer->save();
                        }
                    }
                }
            }
            //Check to see if no option choices have been found; integer or text input provided.
            if($optionChoiceFound == false) {
                foreach($customerPreference['value'] as $providedValue) {
                    //Retrieve the "option choice" for the provided question
                    $question = Question::where('question_subtext', $questionName)->firstOrFail()->load('optionChoice');
                    $questionOptionChoices = $question->optionChoice;
                    $questionOptionChoiceID = $questionOptionChoices[0]->id;
                    $questionOptionSelection = QuestionOptionSelection::where('question_id', $question->id)->where('option_choice_id', $questionOptionChoiceID)->first();
                    $questionOptionID = $questionOptionSelection->id;
                    //Save the new answer
                    $newAnswer = new Answer();
                    $newAnswer->customer_id = $customerID;
                    $newAnswer->question_option_id = $questionOptionID;

                    $valueType = gettype($providedValue);
                    switch($valueType) {
                        case "integer":
                            $newAnswer->answer_int = $providedValue;
                            break;
                        case "string":
                            $newAnswer->answer_text = $providedValue;
                            break;
                    }
                    $newAnswer->save();
                }
            }
        }
        return Customer::findOrFail($customerID);
    }

    public function updateCustomerAnswers(Request $request) {
        $customerID = $request->input('customerID');
        $inquiryEventID = $request->input('inquiryEventID');
        $customerPreferences = $request->input('customerPreferences');

        //Retrieve answers for a customer
        $customerAnswers = Answer::where('customer_id', $customerID)->get()->load('questionOptionSelection.question.optionChoice');
        foreach($customerAnswers as $answer) {
            $question = $answer->questionOptionSelection->question;
            $questionSubText = $question->question_subtext;
            foreach($customerPreferences as $customerPreference) {
                if($customerPreference['type'] == 'radio' && $customerPreference['name'] == $questionSubText) {
                    $optionChoiceID = null;
                    //Retrieve the question option choice for the option choice and question
                    $questionOptionChoices = $question->optionChoice;
                    //Iterate through all available option choices and remove previous answers
                    foreach($questionOptionChoices as $questionOptionChoice) {
                        //Delete any previous answers to the provided question for the customer that are not the current question option
                        Answer::where('question_option_id', $questionOptionChoice->id)->where('customer_id', $customerID)->delete();
                    }
                    //Iterate through all available option choices and store current answer
                    foreach($questionOptionChoices as $questionOptionChoice) {
                        if($questionOptionChoice['option_choice_name'] == $customerPreference['value']) {
                            $optionChoiceID = $questionOptionChoice->id;
                            $questionOptionSelection = QuestionOptionSelection::where('question_id', $question->id)->where('option_choice_id', $optionChoiceID)->firstOrFail();
                            $answer->inquiry_event_id = $inquiryEventID;
                            $answer->question_option_id = $questionOptionSelection->id;
                            $answer->save();
                        }
                    }
                    unset($customerPreference);
                }
                else if($customerPreference['type'] == 'text' && $customerPreference['name'] == $questionSubText) {
                    //Update based on the type of answer text that is provided
                    switch($customerPreference['textType']) {
                        case 'int':
                            if(!empty($customerPreference['value'])) {
                                $answer->answer_int = (int)$customerPreference['value'];
                                $answer->save();
                                unset($customerPreference);
                            }
                            break;
                        case 'string':
                            if(!empty($customerPreference['value'])) {
                                $answer->answer_text = (string)$customerPreference['value'];
                                $answer->save();
                                unset($customerPreference);
                            }
                            break;
                    }
                }
                //Empty any existing answers that have been provided for multi-checkboxes
                else if($customerPreference['type'] == 'checkbox' && $customerPreference['name'] == $questionSubText . '[]') {
                    //Retrieve all available option choices for the provided question
                    $questionOptionChoices = Question::where('question_subtext', $questionSubText)->first()->load('questionOptionSelection');
                    //Remove any answers provided by the customer for the given question option choices
                    foreach($questionOptionChoices->questionOptionSelection as $questionOptionChoice) {
                        $customerAnswer = Answer::where('customer_id', $customerID)->where('question_option_id', $questionOptionChoice->id)->first();
                        if($customerAnswer != NULL) {
                            //Remove answer if it already exists
                            $customerAnswer->delete();
                        }
                    }

                    foreach($customerPreference['value'] as $customerPreferenceValue) {
                        $questionOptionSelection = QuestionOptionSelection::where('question_id', $question->id)->where('option_choice_id', $customerPreferenceValue)->firstOrFail();
                        //Store new answer for inquiry event
                        $answer = new Answer();
                        $answer->customer_id = $customerID;
                        $answer->question_option_id = $questionOptionSelection->id;
                        $answer->inquiry_event_id = $inquiryEventID;
                        $answer->save();
                    }
                    unset($customerPreference);
                }
            }
        }
        //Iterate through customer preferences looking for questions that may not have an answer
        foreach($customerPreferences as $customerPreference) {
            switch($customerPreference['type']) {
                case "checkbox":
                    $customerPreferenceName = str_replace('[]', '', $customerPreference['name']);
                    $customerPreferenceQuestion = Question::where('question_subtext', $customerPreferenceName)->firstOrFail()->load('questionOptionSelection.optionChoice');
                    break;
                case "radio":
                    $customerPreferenceQuestion = Question::where('question_subtext', $customerPreference['name'])->firstOrFail()->load('questionOptionSelection.optionChoice');
                    break;
                default:
                    $customerPreferenceQuestion = Question::where('question_subtext', $customerPreference['name'])->firstOrFail()->load('questionOptionSelection.optionChoice');
                    break;
            }
            //Retrieve all answers with the question option selection and customer id
            $questionOptionsForQuestion = $customerPreferenceQuestion->questionOptionSelection;
            $foundAnswer = false;
            foreach($questionOptionsForQuestion as $questionOption) {
                //Find any answers that exist for the customer and option choice
                $answer = Answer::where('customer_id', $customerID)->where('question_option_id', $questionOption->id)->first();
                if(!empty($answer)) {
                    $foundAnswer = true;
                }
            }
            //If no answer has been found for the customer, associate and save a new answer
            if($foundAnswer == false) {
                $answer = new Answer();
                $answer->customer_id = $customerID;
                $answer->inquiry_event_id = $inquiryEventID;

                $matchedQuestionOptionSelectionID = NULL;
                //Retrieve question option from provided data
                foreach($customerPreferenceQuestion->questionOptionSelection as $questionOptionSelection) {
                    if($questionOptionSelection->id == $customerPreference['value']) {
                        $matchedQuestionOptionSelectionID = $questionOptionSelection->id;
                        //Associate teh found question option id and save new answer
                        $answer->question_option_id = $matchedQuestionOptionSelectionID;
                        $answer->save();
                    }
                    else if(!empty($customerPreference['textType']) && $customerPreference['textType'] == 'int') {
                        if($questionOptionSelection->optionChoice->option_choice_name == $customerPreference['name']) {
                            $matchedQuestionOptionSelectionID = $questionOptionSelection->id;
                            if(!empty($customerPreference['value'])) {
                                $answer->answer_int = (int)$customerPreference['value'];
                                //Associate teh found question option id and save new answer
                                $answer->question_option_id = $matchedQuestionOptionSelectionID;
                                $answer->save();
                            }
                        }
                    }
                }
            }
        }
        return $this->getCustomerAnswers(null, $customerID);
    }

    function saveQuestionsAndAnswers(Request $request){
        $customerID = $request->input('data.customerID');
        $customer = new Customer();
        $customer = $customer::find($customerID);
        $newInput = array();
        if(!$customer || !$customerID){
            return Response(['success'=>false, 'error_message'=>'No customer was found.']);
        }


        //Currently getting this:

        //Format to this:
        $request->has('data.roommate_matching') ? $newInput['roommate_matching'] = $this->InquiryRepository->getClosestOptionFromValue('roommate_matching', $request->input('data.roommate_matching')) : "";
        $request->has('data.wants_furniture') ? $newInput['wants_furniture'] = $this->InquiryRepository->getClosestOptionFromValue('wants_furniture', $request->input('data.wants_furniture')) : "";
        $request->has('data.wants_utilities') ? $newInput['wants_utilities'] = $this->InquiryRepository->getClosestOptionFromValue('wants_utilities', $request->input('data.wants_utilities')) : "";
        //find id for input => done

        $request->replace($newInput);
        $this->InquiryRepository->saveQuestionsAndAnswers($request, $customer);
//        $request->has('has_pet') ? $optionChoices['has_pet'] = $request->input('has_pet') : "";
//        $request->has('overall_interest') ? $optionChoices['overall_interest'] = $request->input('overall_interest') : "";
//        $request->has('price_importance') ? $optionChoices['price_importance'] = $request->input('price_importance') : "";
//        $request->has('amenity_interest') ? $optionChoices['amenity_interest'] = $request->input('amenity_interest') : "";
//        $request->has('max_budget') ? $maxBudget = $request->input('max_budget') : "";
//        $request->has('price_range_minimum') ? $priceRangeMinimum = $request->input('price_range_minimum') : "";
//        $request->has('price_range_maximum') ? $priceRangeMaximum = $request->input('price_range_maximum') : "";
        //desired_bedroom_count
        //question subtext => id of option
        return Response(['success'=>true]);
    }
}
