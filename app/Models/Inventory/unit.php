<?php

namespace App\Models\Inventory;

use \App\Models\AccessControlledModel as AccessControlledModel;
use App\Models\Inventory\UnitStyle;
use App\Models\Inventory\Building;
/**
 * Class units
 * @package App\Models
 * @version November 23, 2016, 9:30 pm UTC
 */
class Unit extends AccessControlledModel
{

    public $table = 'units';
    public $timestamps = false;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'unit_number',
        'name',
        'pets_allowed',
        'unit_id_timberline'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'unit_number' => 'string',
        'name' => 'string',
        'unit_id_timberline' => 'string'
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
    public function bedrooms()
    {
        return $this->hasMany(\App\Models\Bedroom::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function building()
    {
        return $this->belongsToMany(Building::class, '_buildings_units');
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
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     **/
    public function unitStyle()
    {
        return $this->belongsToMany(UnitStyle::class, '_unit_styles_units');
    }
}
