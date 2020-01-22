<?php

namespace App\Models\Customers;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class organization_types
 * @package App\Models
 * @version November 23, 2016, 9:29 pm UTC
 */
class OrganizationType extends AccessControlledModel
{

    public $table = 'organization_types';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'name'
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function organizationTypesOrganizations()
    {
        return $this->hasMany(\App\Models\OrganizationTypesOrganization::class);
    }
}
