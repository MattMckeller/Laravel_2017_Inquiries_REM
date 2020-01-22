<?php

namespace App\Jobs;

use App\Http\Controllers\MailController;
use App\Models\Appointments\EmailMessage;
use App\Models\Customers\Customer;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\App;

class SendAppointmentCanceledEmail implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    private $MailController;
    protected $event;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($event)
    {
        $this->event = $event;
        /** @var  MailController */
        $this->MailController = App::make(MailController::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $event = $this->event;
        $appointmentEvent = $event->appointmentEvent;
        $appointment = $appointmentEvent->appointment()->first();
        $event_type = $appointmentEvent->event_type;
        $user_type = $appointmentEvent->user_type;

        $email = EmailMessage::ofUserEventType($user_type, $event_type)->first();

        if (!$email) {
            return;
        }
        /** @var  Customer */
        $apptAgent = $appointment->userAccountInformation()->first();
        $apptCustomers = $appointment->customers()->get();

        $startCarbon = new Carbon($appointment->start);
        $start = $startCarbon->format("l F jS, Y @ g:i a");
        $endCarbon = new Carbon($appointment->end);
        $end = $endCarbon->format("l F jS, Y @ g:i a");
        $workingLocation = $appointment->workingLocation()->first();
        $workingLocationName = $workingLocation->public_name;
        $streetAddress = (!empty($workingLocation))?($workingLocation->office_address . ', '  . $workingLocation->city.', '.$workingLocation->state.' '.$workingLocation->zip):(null);
        $agentAccount = $apptAgent->userAccount()->first();
        $agentEmail = $agentAccount->work_email_address;

        foreach($apptCustomers as $customer) {
            $to = $customer ? $customer->emailAddresses()->first()->email_address : $apptAgent->email_address;
            $name['first'] = $customer->first_name;
            $name['last'] = $customer->last_name;
            $customerEmailData = [
                'view_path'=>'email/customerCancelation',
                'workingLocation' => $workingLocationName,
                'officeAddress' => $streetAddress,
                'start' => $start,
                'end' => $end,
                'firstName' => $name['first'],
                'lastName' => $name['last'],
                'fullName' => $name['first'] . ' ' . $name['last'],
                'agentFirstName'=>$apptAgent->first_name,
                'agentLastName'=>$apptAgent->last_name,
                'name' =>$name,
                'eventType' => $event_type,
                'propertyEmail'=>$this->getPropertyEmailAddress($workingLocation)
            ];


            $this->MailController->emailCustomer($to, $email, $customerEmailData);

        }

        $agentEmailData = [
            'workingLocation' => $workingLocationName,
            'officeAddress' => $streetAddress,
            'start' => $start,
            'end' => $end,
            'agentFirstName'=>$apptAgent->first_name,
            'agentLastName'=>$apptAgent->last_name,
            'eventType' => $event_type,
            'propertyEmail'=>$this->getPropertyEmailAddress($workingLocation)
        ];

        $this->MailController->emailAgent($agentEmail, $email, $agentEmailData);
        //ADDRESSES



    }

    function getPropertyEmailAddress($workingLocation){
        $name = $workingLocation->short_name;
        switch($name){
            case 'dt':
                return 'dt_managers@liveatbrookside.com';
            case 'mt':
                return 'mt_managers@liveatbrookside.com';
            case 'th':
                return 'th_managers@liveatbrookside.com';
            case 'br':
                return 'br_managers@liveatbrookside.com';
            case 'mtwku':
                return 'wku_managers@liveatbrookside.com';
            case 'mtosu':
                return 'hcdemuth@midtownsw.com';
            default:
                return '';
        }
    }
}
