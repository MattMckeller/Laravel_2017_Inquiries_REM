<?php

namespace App\Models\Documents;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class document_signing_events
 * @package App\Models
 * @version November 23, 2016, 9:27 pm UTC
 */
class document_signing_events extends AccessControlledModel
{

    public $table = 'document_signing_events';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'ip_address',
        'user_agent',
        'timestamp',
        'user_id',
        'user_type',
        'electronic_disclosure_reference_id',
        'event_type'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'ip_address' => 'string',
        'user_agent' => 'string',
        'user_type' => 'string',
        'electronic_disclosure_reference_id' => 'string',
        'event_type' => 'string'
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
    public function documentSigningEventsDocumentVersions()
    {
        return $this->hasMany(\App\Models\DocumentSigningEventsDocumentVersion::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function documentSigningEventsDocuments()
    {
        return $this->hasMany(\App\Models\DocumentSigningEventsDocument::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function documentSigningEventsLeases()
    {
        return $this->hasMany(\App\Models\DocumentSigningEventsLease::class);
    }
}
