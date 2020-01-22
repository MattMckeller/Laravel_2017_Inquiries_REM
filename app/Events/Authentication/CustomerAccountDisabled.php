<?php

namespace App\Events\Authentication;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\Inquiries\Inquiry;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Listeners\Subscribers\InquiryUpdateSubscriber;
use App\Models\Auth\Customer\CustomerAccount;

class CustomerAccountDisabled
{
    public $customerAccountID;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(CustomerAccount $customerAccount)
    {
        $this->customerAccountID = $customerAccount->id;
    }




}
