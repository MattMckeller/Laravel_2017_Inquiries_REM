<?php

namespace App\Models;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class parking_certificates_of_completion
 * @package App\Models
 * @version November 28, 2016, 3:10 pm UTC
 */
class parking_certificates_of_completion extends AccessControlledModel
{

    public $table = 'parking_certificates_of_completion';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'accepted_electronic_agreement_datetime',
        'unsigned_datetime',
        'unsigned_hash',
        'signed_datetime',
        'tenant_ip',
        'signed_hash',
        'completed_datetime',
        'completed_hash',
        'agent_ip',
        'initial_email_datetime',
        'completed_email_datetime'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'unsigned_hash' => 'string',
        'tenant_ip' => 'string',
        'signed_hash' => 'string',
        'completed_hash' => 'string',
        'agent_ip' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    
}
