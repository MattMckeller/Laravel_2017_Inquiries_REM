<?php

namespace App\Models\Inventory;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class hold_types
 * @package App\Models
 * @version November 23, 2016, 9:28 pm UTC
 */
class HoldType extends AccessControlledModel
{

    public $table = 'hold_types';
    public $timestamps = false;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'type'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'type' => 'string'
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
    public function Holds()
    {
        return $this->hasMany(\App\Models\Hold::class);
    }
}
