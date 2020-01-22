<?php

namespace App\Models\Documents;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class lease_classifiers
 * @package App\Models
 * @version November 23, 2016, 9:28 pm UTC
 */
class lease_classifiers extends AccessControlledModel
{

    public $table = 'lease_classifiers';
    
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function leaseClassifiersLeases()
    {
        return $this->hasMany(\App\Models\LeaseClassifiersLease::class);
    }
}
