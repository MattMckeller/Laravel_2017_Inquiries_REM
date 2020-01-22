<?php

namespace App\Models\Inventory;

use App\Models\Auth\Team\WorkingLocation;
use App\Models\Customers\Customer;
use App\Models\Inquiries\Inquiry;
use App\Models\Marketing\Market;
use App\Models\Documents\LeaseType;
use App\Models\Templates\DocumentSet;
use App\Models\Other\Application;
use App\Models\Inventory\Building;
use \App\Models\AccessControlledModel as AccessControlledModel;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class locations
 * @package App\Models
 * @version November 23, 2016, 9:28 pm UTC
 */
class Location extends AccessControlledModel
{
    public $table = 'locations';
    public $timestamps = false;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'name',
        'short_name',
        'public_name',
        'type',
        'office_address',
        'city',
        'state',
        'zip',
        'redecoration_fee',
        'activities_fee',
        'image_name'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'short_name' => 'string',
        'public_name' => 'string',
        'type' => 'string',
        'office_address' => 'string',
        'city' => 'string',
        'state' => 'string',
        'zip' => 'string',
        'image_name' => 'string'
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
    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function buildings()
    {
        return $this->belongsToMany(Building::class, '_buildings_locations');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function documentSets()
    {
        return $this->hasMany(DocumentSet::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function leaseTypes()
    {
        return $this->hasMany(LeaseType::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function markets()
    {
        return $this->hasMany(Market::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function inquiries()
    {
        return $this->hasMany(Inquiry::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function preferredCustomers()
    {
        return $this->belongsToMany(Customer::class, '_location_preferences');
    }

    /**
     * @return HasOne
     */
    public function workingLocation()
    {
        return $this->hasOne(WorkingLocation::class);
    }
}
