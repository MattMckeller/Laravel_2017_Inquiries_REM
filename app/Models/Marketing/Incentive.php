<?php

namespace App\Models\Marketing;

use App\Models\Customers\Customer;
use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class requested_roommates
 * @package App\Models
 * @version November 23, 2016, 9:29 pm UTC
 */
class Incentive extends AccessControlledModel
{
    
    public $table = 'incentives';
    public $timestamps = false;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    public $fillable = [
        'name',
        'display_name',
        'standard_duration'
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
        return $this->belongsToMany(Customer::class, "_customers_incentives");
    }
}
