<?php

namespace App\Models\Auth\Team;

use App\Models\AccessControlledModel;
use App\Models\Inventory\Location;
use App\Models\Appointments\DefaultTimes;
use App\Models\Appointments\ModifiedTimes;
use App\Models\Appointments\AgentSchedule;

class WorkingLocation extends AccessControlledModel
{
    public $table = 'working_locations';

    public $guarded = array();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function location(){
        return $this->belongsTo(Location::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function modifiedTimes() {
        return $this->hasMany(ModifiedTimes::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function defaultTimes() {
        return $this->hasMany(DefaultTimes::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function agentSchedules() {
        return $this->hasMany(AgentSchedule::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function userAccountInformation() {
        return $this->belongsToMany(UserAccountInformation::class, 'user_account_information_working_locations');
    }
}
