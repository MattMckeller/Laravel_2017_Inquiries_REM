<?php

namespace App\Models\Inventory;

use \App\Models\AccessControlledModel as AccessControlledModel;
use App\Models\Inventory\Unit;
use App\Models\Inventory\Location;
/**
 * Class buildings
 * @package App\Models
 * @version November 23, 2016, 9:27 pm UTC
 */
class Building extends AccessControlledModel
{

    public $table = 'buildings';
    public $timestamps = false;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'street_address',
        'city',
        'state',
        'zip',
        'zoning',
        'pets_allowed',
        'name'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'street_address' => 'string',
        'city' => 'string',
        'state' => 'string',
        'zip' => 'integer',
        'zoning' => 'string',
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
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     **/
    public function brand()
    {
        return $this->hasOne(\App\Models\Brand::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     **/
    public function company()
    {
        return $this->hasOne(\App\Models\Company::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function location()
    {
        return $this->belongsToMany(Location::class, '_buildings_locations');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function units()
    {
        return $this->belongsToMany(Unit::class, '_buildings_units');
    }
}
