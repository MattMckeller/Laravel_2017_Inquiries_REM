<?php

namespace App\Models\Appointments;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\Team\WorkingLocation;
use App\Models\AccessControlledModel;
//THESE ARE TIME PERIODS THAT PROPERTIES ARE TYPICALLY OPEN.
class DefaultTimes extends AccessControlledModel
{
    public $table = 'default_times';

    public $fillable = [
        'working_location_id',
        'day_of_week',
        'start',
        'end'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function workingLocation() {
        return $this->belongsTo(WorkingLocation::class);
    }
}
