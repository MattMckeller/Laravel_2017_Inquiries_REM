<?php

namespace App\Models\Appointments;

use App\Models\Auth\Team\UserAccountInformation;
use App\Models\Auth\Team\WorkingLocation;
use App\Models\AccessControlledModel;

class AgentSchedule extends AccessControlledModel
{
    public $table = 'agent_schedules';

    public $fillable = [
        'start',
        'end',
        'user_account_information_id',
        'working_location_id'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function userAccountInformation() {
        return $this->belongsTo(UserAccountInformation::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function workingLocation()
    {
        return $this->belongsTo(WorkingLocation::class);
    }
}
