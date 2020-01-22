<?php

namespace App\Models\Customers;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class organizations
 * @package App\Models
 * @version November 23, 2016, 9:29 pm UTC
 */
class Organization extends AccessControlledModel
{

    public $table = 'organizations';
    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'name',
        'organization_dm_first',
        'organization_dm_second'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function customer()
    {
        return $this->belongsToMany(Customer::class, "_customers_organizations");
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function organizationTypesOrganizations()
    {
        return $this->hasMany(\App\Models\OrganizationTypesOrganization::class);
    }
}
