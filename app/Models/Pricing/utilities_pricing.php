<?php

namespace App\Models;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class utilities_pricing
 * @package App\Models
 * @version November 28, 2016, 3:04 pm UTC
 */
class utilities_pricing extends AccessControlledModel
{

    public $table = 'utilities_pricing';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'utilities_pricing_type',
        'cost'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'utilities_pricing_type' => 'string'
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
    public function unitStylesUtilitiesPricings()
    {
        return $this->hasMany(\App\Models\UnitStylesUtilitiesPricing::class);
    }
}
