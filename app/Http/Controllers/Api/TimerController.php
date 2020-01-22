<?php

namespace App\Http\Controllers\Api;

use App\Models\Timers\Timer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DateInterval;
use Illuminate\Support\Facades\App;

class TimerController extends Controller
{
    private $TimerRepository;

    function __construct()
    {
        $this->TimerRepository = App::make('TimerRepository');
    }

    public function delayTimer(Request $request) {
        $updateStatus = array('errors' => array(), 'success' => true);
        $timerID = $request->input('timerID', false);
        $durationOfTimerDelay = $request->input('durationOfTimerDelay', null);
        $timer = Timer::findOrFail($timerID);
        switch ($durationOfTimerDelay) {
            case 1:
                $timerDelayDV = new DateInterval('PT24H');
                break;
            case 2:
                $timerDelayDV = new DateInterval('PT48H');
                break;
            default:
                $updateStatus['errors'][] = 'Must provide a valid delay.';
                $updateStatus['success'] = false;
                return response($updateStatus, 500);
        }
        if ($this->TimerRepository->addDelay($timer, $timerDelayDV)) {
            return response(array('Errors' => array(), 'Success' => true), 200);
        }
        return response(array('Errors' => array('Timer was unable to be delayed.'), 'Success' => false), 500);
    }
}