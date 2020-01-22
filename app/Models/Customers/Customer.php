<?php

namespace App\Models\Customers;

use App\Models\Other\Application;
use App\Models\Appointments\Appointment;
use App\Models\Customers\CustomerStory;
use App\Models\Inquiries\Inquiry;
use App\Models\Appointments\Group;
use App\Models\Appointments\AppointmentEvent;
use App\Models\Auth\Customer\CustomerAccount;
use App\Models\Inventory\UnitStyle;
use App\Models\Marketing\Incentive;
use \App\Models\AccessControlledModel as AccessControlledModel;
use App\Models\Inventory\Location;
use App\Models\Inquiries\Answer;
use Illuminate\Contracts\Validation\Validator;
use App\Models\Documents\leases;
use App\Models\Other\School;

/**
 * Class Customer
 * @package App\Models
 * @version November 23, 2016, 9:00 pm UTC
 */
class Customer extends AccessControlledModel
{

    public $table = 'customers';
    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    public $fillable = [
        'first_name',
        'middle_initial',
        'last_name',
        'gender',
        'birthday',
        'incentive_offer_date',
        'first_name_dm_first',
        'first_name_dm_second',
        'last_name_dm_first',
        'last_name_dm_second',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'first_name' => 'string',
        'middle_initial' => 'string',
        'last_name' => 'string',
        'gender' => 'string',
        'birthday' => 'date',
        'incentive_offer_date' => 'date'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public $rules = [
        "first_name" => 'required | nameRegex',
        "last_name" => 'required | nameRegex'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function applications()
    {
        return $this->belongsToMany(Application::class, '_applications_customers', 'customer_id', 'application_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function appointmentEvents()
    {
        return $this->hasMany(AppointmentEvent::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function appointments() {
        return $this->belongsToMany(Appointment::class, 'groups');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function customerAccount()
    {
        return $this->hasMany(CustomerAccount::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function customerStory()
    {
        return $this->belongsTo(CustomerStory::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function customerViewingEvents()
    {
        return $this->hasMany(CustomerViewingEvent::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function emailAddresses()
    {
        return $this->hasMany(EmailAddress::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function emergencyContacts()
    {
        return $this->belongsToMany(Customer::class, '_customers_emergency_contacts', 'customer_id', 'emergency_contact_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function group() {
        return $this->hasMany(group::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function guarantors()
    {
        return $this->belongsToMany(Guarantor::class, "_customers_guarantors");
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function inquiries()
    {
        return $this->hasMany(Inquiry::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function leases()
    {
        return $this->belongsToMany(leases::class, '_customers_leases', 'customer_id', 'lease_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function leasingReason()
    {
        return $this->belongsTo(LeasingReason::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function organizations()
    {
        return $this->belongsToMany(Organization::class, "_customers_organizations");
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function phoneNumbers()
    {
        return $this->hasMany(PhoneNumber::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function schools()
    {
        return $this->belongsToMany(School::class, "_customers_schools", 'customer_id', 'school_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function unitStyleLikes()
    {
        return $this->belongsToMany(UnitStyle::class, "customer_likes");
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTO
     **/
    public function requestedRoommatesGroup()
    {
        return $this->belongsTo(RequestedRoommateGroup::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     **/
    public function locationPreferences()
    {
        return $this->belongsToMany(Location::class, "_location_preferences");
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     **/
    public function incentives(){
        return $this->belongsToMany(Incentive::class, '_customers_incentives');
    }
}
