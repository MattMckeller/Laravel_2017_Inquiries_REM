<?php

namespace App\Models;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class accepted_electronic_disclosure_logs
 * @package App\Models
 * @version November 23, 2016, 9:26 pm UTC
 */
class accepted_electronic_disclosure_logs extends AccessControlledModel
{

    public $table = 'accepted_electronic_disclosure_logs';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'disclosure_reference_id',
        'accepted',
        'accepted_datetime',
        'verification_number',
        'correct_verification_number',
        'disclosure_html_base64',
        'user_agent',
        'ip_address'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'disclosure_reference_id' => 'string',
        'verification_number' => 'string',
        'disclosure_html_base64' => 'string',
        'user_agent' => 'string',
        'ip_address' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    
}
