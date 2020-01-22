<?php

namespace App\Models\Customers;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class Customer Story
 * @package App\Models
 * @version November 23, 2016, 9:00 pm UTC
 */
class LeasingReason extends AccessControlledModel
{

    public $table = 'leasing_reasons';
    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    public $fillable = [
        'description'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     **/
    public function customer()
    {
        return $this->hasOne(Customer::class);
    }
}
