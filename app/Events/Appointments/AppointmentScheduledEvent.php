<?php

namespace App\Events\Appointments;

use App\Models\Appointments\AppointmentEvent;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class AppointmentScheduledEvent
{
    use InteractsWithSockets, SerializesModels;

    public $appointmentEvent;
    /**
     * Create a new event instance.
     * @param $appointmentEvent AppointmentEvent for the appointment which was created
     * @return void
     */
    public function __construct(AppointmentEvent $appointmentEvent)
    {
        $this->appointmentEvent = $appointmentEvent;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('appointment_events');
    }
}
