<?php

namespace App\Models\Inquiries;

use \App\Models\AccessControlledModel as AccessControlledModel;
use App\Models\Auth\Team\UserAccountInformation;

class InquiryNote extends AccessControlledModel
{
    public $table = 'inquiry_notes';
    public $timestamps = true;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    public $fillable = [
        'text'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'text' => 'string'
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
    public function inquiryEvent()
    {
        return $this->belongsTo(InquiryEvent::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function userAccountInformation()
    {
        return $this->belongsTo(UserAccountInformation::class, 'agent_id');
    }
}
