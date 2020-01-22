<?php

namespace App\Models\Other;

use \App\Models\AccessControlledModel as AccessControlledModel;
use App\Models\Customers\Customer;
use App\Models\Other\School;

/**
 * Class applications
 * @package App\Models
 * @version November 23, 2016, 9:26 pm UTC
 */
class Application extends AccessControlledModel
{

    public $table = 'applications';

    public $timestamps = false;

    public $fillable = [
        'creation_datetime',
        'creating_user_id',
        'wants_utilities',
        'wants_furniture',
        'employer',
        'employer_supervisor',
        'supervisor_phone',
        'landlord',
        'landlord_phone',
        'wants_pet',
        'year_in_school'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'employer' => 'string',
        'employer_supervisor' => 'string',
        'supervisor_phone' => 'string',
        'landlord' => 'string',
        'landlord_phone' => 'string',
        'year_in_school' => 'string'
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
    public function applicationsBrandExposures()
    {
        return $this->hasMany(\App\Models\ApplicationsBrandExposure::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function applicationsBrands()
    {
        return $this->hasMany(\App\Models\ApplicationsBrand::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function applicationsCustomers()
    {
        return $this->hasMany(\App\Models\ApplicationsCustomer::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function applicationsLocations()
    {
        return $this->hasMany(\App\Models\ApplicationsLocation::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function applicationsRequestedRoommates()
    {
        return $this->hasMany(\App\Models\ApplicationsRequestedRoommate::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function customers()
    {
        return $this->belongsToMany(Customer::class, '_applications_customers', 'application_id', 'customer_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
