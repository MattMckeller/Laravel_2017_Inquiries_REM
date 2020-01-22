<?php

namespace App\Models\Marketing;

use App\Models\Inventory\Location;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class Market
 * @package App\Models
 * @version November 23, 2016, 9:29 pm UTC
 */
class Market extends AccessControlledModel
{

    public $table = 'markets';
    public $timestamps = false;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        
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
    public function locations()
    {
        return $this->hasMany(Location::class);
    }
}
