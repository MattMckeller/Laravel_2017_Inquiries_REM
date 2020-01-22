<?php

namespace App\Models\Documents;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class signing_queues
 * @package App\Models
 * @version November 23, 2016, 9:29 pm UTC
 */
class signing_queues extends AccessControlledModel
{

    public $table = 'signing_queues';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'user_id',
        'user_type',
        'has_signed',
        'queue_position'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_type' => 'string',
        'queue_position' => 'integer'
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
    public function documentsSigningQueues()
    {
        return $this->hasMany(\App\Models\DocumentsSigningQueue::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function electronicSignaturePasscodesSigningQueues()
    {
        return $this->hasMany(\App\Models\ElectronicSignaturePasscodesSigningQueue::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function leasesSigningQueues()
    {
        return $this->hasMany(\App\Models\LeasesSigningQueue::class);
    }
}
