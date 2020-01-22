<?php

namespace App\Models\Other;

use \App\Models\AccessControlledModel as AccessControlledModel;
use App\Models\Customers\Customer;

class School extends AccessControlledModel
{
    public $table = 'schools';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public $fillable = [
        'name',
        'short_name'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function application()
    {
        return $this->hasMany(Application::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function customers()
    {
        return $this->belongsToMany(Customer::class, '_customers_schools', 'school_id', 'customer_id');
    }
}
