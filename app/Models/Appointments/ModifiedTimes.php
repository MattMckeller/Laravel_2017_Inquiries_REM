<?php

namespace App\Models\Appointments;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\Team\WorkingLocation;
use App\Models\AccessControlledModel;

//THESE ARE SPECIAL TIMES THAT THE OFFICES HAVE DIFFERENT FROM NORMAL SCHEDULES. I.E. HOLIDAYS
class ModifiedTimes extends AccessControlledModel
{
    public $table = 'modified_times';

    public $fillable = [
        'working_location_id',
        'start_datetime',
        'end_datetime'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function workingLocation() {
        return $this->belongsTo(WorkingLocation::class);
    }
}
