<?php

namespace App\Models\Inventory;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class holds
 * @package App\Models
 * @version November 23, 2016, 9:28 pm UTC
 */
class Hold extends AccessControlledModel
{

    public $table = 'holds';
    public $timestamps = false;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'start_datetime',
        'end_datetime',
        'comment',
        'user_id',
        'creation_datetime',
        'removal_datetime',
        'persist_until_datetime',
        'is_active'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'comment' => 'string'
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function holdType()
    {
        return $this->hasMany(\App\Models\HoldType::class);
    }
}
