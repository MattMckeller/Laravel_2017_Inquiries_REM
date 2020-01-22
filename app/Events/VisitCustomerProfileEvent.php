<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\Customers\Customer;

class VisitCustomerProfileEvent
{
    /**
     * Create a new event instance.
     * @param \App\Models\Customers\Customer the customer whose profile was viewed
     * @return void
     */
    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }
    
}
