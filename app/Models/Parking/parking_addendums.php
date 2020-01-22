<?php

namespace App\Models;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class parking_addendums
 * @package App\Models
 * @version November 23, 2016, 9:29 pm UTC
 */
class parking_addendums extends AccessControlledModel
{

    public $table = 'parking_addendums';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'passcode',
        'tenant_first_name',
        'tenant_last_name',
        'tenant_signature',
        'email_address',
        'birthday',
        'agent_signature',
        'parking_lot',
        'monthly_parking',
        'annual_parking',
        'company',
        'start_date',
        'end_date',
        'unsigned_id',
        'signed_id',
        'completed_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'passcode' => 'string',
        'tenant_first_name' => 'string',
        'tenant_last_name' => 'string',
        'tenant_signature' => 'string',
        'email_address' => 'string',
        'birthday' => 'string',
        'agent_signature' => 'string',
        'parking_lot' => 'string',
        'company' => 'string',
        'start_date' => 'string',
        'end_date' => 'string',
        'unsigned_id' => 'string',
        'signed_id' => 'string',
        'completed_id' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    
}
