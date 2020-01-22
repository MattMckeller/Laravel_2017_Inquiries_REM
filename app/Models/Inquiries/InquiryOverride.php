<?php

namespace App\Models\Inquiries;

use \App\Models\AccessControlledModel as AccessControlledModel;

class InquiryOverride extends AccessControlledModel
{
    public $table = 'inquiry_overrides';
    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    public $fillable = [
        'timestamp',
        'reason'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
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
    public function inquiryOverrideType()
    {
        return $this->belongsTo(InquiryOverrideType::class);
    }
}
