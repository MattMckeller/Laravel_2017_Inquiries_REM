<?php

namespace App\Models\Templates;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class document_sets
 * @package App\Models
 * @version November 23, 2016, 9:27 pm UTC
 */
class DocumentSet extends AccessControlledModel
{

    public $table = 'document_sets';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'name',
        'short_name',
        'creation_timestamp'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'short_name' => 'string'
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
    public function documentSetsDocumentTemplates()
    {
        return $this->hasMany(\App\Models\DocumentSetsDocumentTemplate::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function documentSetsLeaseTypes()
    {
        return $this->hasMany(\App\Models\DocumentSetsLeaseType::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function documentSetsLeases()
    {
        return $this->hasMany(\App\Models\DocumentSetsLease::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function documentSetsLocations()
    {
        return $this->hasMany(\App\Models\DocumentSetsLocation::class);
    }
}
