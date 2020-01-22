<?php

namespace App\Models\Auth\Customer;

use \App\Models\RestrictedModel as RestrictedModel;

/**
 * Class electronic_signature_passcodes
 * @package App\Models
 * @version November 23, 2016, 9:27 pm UTC
 */
class electronic_signature_passcodes extends RestrictedModel
{

    public $table = 'electronic_signature_passcodes';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'passcode',
        'timestamp',
        'contact_email_address',
        'is_manual_key'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'passcode' => 'string',
        'contact_email_address' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function electronicSignaturePasscodesSigningQueues()
    {
        return $this->hasMany(\App\Models\ElectronicSignaturePasscodesSigningQueue::class);
    }
}
