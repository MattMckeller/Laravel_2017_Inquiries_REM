<?php

namespace App\Models\Inventory;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class bedrooms
 * @package App\Models
 * @version November 23, 2016, 9:26 pm UTC
 */
class Bedroom extends AccessControlledModel
{

    public $table = 'bedrooms';
    public $timestamps = false;
    
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
    public function holds()
    {
        return $this->hasMany(\App\Models\Hold::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function leases()
    {
        return $this->hasMany(\App\Models\Lease::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function priceTags()
    {
        return $this->hasMany(\App\Models\PriceTag::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function unit()
    {
        return $this->belongsTo(\App\Models\Unit::class);
    }
}
