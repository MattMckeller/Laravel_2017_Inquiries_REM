<?php

namespace App\Models\Inventory;

use \App\Models\AccessControlledModel as AccessControlledModel;
use App\Models\Customers\Customer;
use App\Models\Inventory\Unit;
use App\Models\Inventory\Location;

/**
 * Class unit_styles
 * @package App\Models
 * @version November 23, 2016, 9:29 pm UTC
 */
class UnitStyle extends AccessControlledModel
{

    public $table = 'unit_styles';

    public $timestamps = false;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'name',
        'bedrooms',
        'bathrooms',
        'stories',
        'finish_level',
        'rentable_area'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'stories' => 'integer',
        'finish_level' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function customersWhoLike()
    {
        return $this->belongsToMany(Customer::class, "customer_likes");
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function location()
    {
        return $this->hasMany(Location::class);
    }


//    /**
//     * @return \Illuminate\Database\Eloquent\Relations\HasMany
//     **/
//    public function furniturePricing()
//    {
//        return $this->hasMany(\App\Models\FurniturePricing::class);
//    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function units()
    {
        return $this->belongsToMany(Unit::class, '_unit_styles_units');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     **/
//    public function utilitiesPricing()
//    {
//        return $this->hasOne(\App\Models\UtilitiesPricing::class);
//    }
}
