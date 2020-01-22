<?php

namespace App\Models\Inventory;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class companies
 * @package App\Models
 * @version November 23, 2016, 9:27 pm UTC
 */
class Company extends AccessControlledModel
{

    public $table = 'companies';
    public $timestamps = false;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'name',
        'dba',
        'address_1',
        'address_2',
        'city',
        'state',
        'zip'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'dba' => 'string',
        'address_1' => 'string',
        'address_2' => 'string',
        'city' => 'string',
        'state' => 'string',
        'zip' => 'string'
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
    public function buildings()
    {
        return $this->hasMany(\App\Models\Building::class);
    }
}
