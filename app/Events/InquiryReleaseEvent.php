<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\Inquiries\Inquiry;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Listeners\Subscribers\InquiryUpdateSubscriber;

class InquiryReleaseEvent
{
    public $inquiry;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Inquiry $inquiry)
    {
        $this->inquiry = $inquiry;
    }
}
