<?php

namespace App\Models\Inquiries;
use App\Models\Inquiries\Inquiry;
use App\Models\Unused\InquirySessionInfo;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class Inquiry
 * @package App\Models
 * @version November 23, 2016, 9:28 pm UTC
 */
class InquiryLabel extends AccessControlledModel
{
    public $table = 'inquiry_labels';
    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    public $fillable = [
        'name'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function inquiry()
    {
        return $this->belongsToMany(Inquiry::class, "_inquiry_labels_inquiries");
    }
}
