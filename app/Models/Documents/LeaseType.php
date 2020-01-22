<?php

namespace App\Models\Documents;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class lease_types
 * @package App\Models
 * @version November 23, 2016, 9:28 pm UTC
 */
class LeaseType extends AccessControlledModel
{

    public $table = 'lease_types';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'name',
        'short_name'
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
    public function documentSetsLeaseTypes()
    {
        return $this->hasMany(\App\Models\DocumentSetsLeaseType::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function leaseTypesLeases()
    {
        return $this->hasMany(\App\Models\LeaseTypesLease::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function leaseTypesLocations()
    {
        return $this->hasMany(\App\Models\LeaseTypesLocation::class);
    }
}
