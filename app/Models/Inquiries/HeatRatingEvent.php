<?php

namespace App\Models\Inquiries;

use App\Models\Auth\Team\UserAccount;
use \App\Models\AccessControlledModel as AccessControlledModel;

class HeatRatingEvent extends AccessControlledModel
{
    public $table = 'heat_rating_events';
    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    public $fillable = [
        'id',
        'event_timestamp',
        'old_heat_index',
        'new_heat_index'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function inquiry()
    {
        return $this->belongsTo(Inquiry::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function userAccount() {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }
}
