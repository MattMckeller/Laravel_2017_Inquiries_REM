<?php

namespace App\Models;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class furniture_pricing
 * @package App\Models
 * @version November 28, 2016, 3:07 pm UTC
 */
class furniture_pricing extends AccessControlledModel
{

    public $table = 'furniture_pricing';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'full_furniture_price',
        'partial_living_area_price',
        'partial_bedroom_price',
        'furniture_pricing_type'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'furniture_pricing_type' => 'string'
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
    public function furniturePricingUnitStyles()
    {
        return $this->hasMany(\App\Models\FurniturePricingUnitStyle::class);
    }
}
