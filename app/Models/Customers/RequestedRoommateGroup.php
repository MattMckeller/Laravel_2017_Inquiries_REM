<?php

namespace App\Models\Customers;

use App\Models\Application;
use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class requested_roommates
 * @package App\Models
 * @version November 23, 2016, 9:29 pm UTC
 */
class RequestedRoommateGroup extends AccessControlledModel
{
    
    public $table = 'requested_roommate_groups';
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
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
