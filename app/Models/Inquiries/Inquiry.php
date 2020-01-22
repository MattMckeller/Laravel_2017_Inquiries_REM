<?php

namespace App\Models\Inquiries;
use App\Models\Auth\Team\UserAccount;
use App\Models\Customers\Customer;
use App\Models\Appointments\Appointment;
use App\Models\Inventory\Location;
use App\Models\Marketing\BrandExposure;
use App\Models\Marketing\Brand;
use App\Models\Timers\Timer;
use App\Models\Unused\InquirySessionInfo;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class Inquiry
 * @package App\Models
 * @version November 23, 2016, 9:28 pm UTC
 */
class Inquiry extends AccessControlledModel
{
    public $table = 'inquiries';
    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    public $fillable = [
        'inquiry_timestamp',
        'heat_index',
        'is_held',
        'agent_claim_timestamp',
        'agent_claim_expiration'
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
     * Checks whether the current inquiry has an appointment scheduled or not.
     * @return boolean true/false
     */
    function hasAppointment(){
        return rand(0,1)?(true):(false);
    }

    /**
     * Checks whether the current inquiry has an appointment scheduled or not.
     * @return boolean true/false
     */
    function hasLease(){
        return rand(0,1)?(true):(false);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function appointment()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function brands()
    {
        return $this->hasMany(Brand::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function brandExposures()
    {
        return $this->belongsToMany(BrandExposure::class, "_brand_exposures_inquiries");
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function inquiryEvents()
    {
        return $this->hasMany(InquiryEvent::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function inquiryLabel()
    {
        return $this->belongsToMany(InquiryLabel::class, "_inquiry_labels_inquiries");
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function inquiryNote()
    {
        return $this->hasMany(InquiryNote::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function inquiryPreferences()
    {
        return $this->belongsToMany(InquiryPreference::class, "_inquiries_inquiry_preferences");
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     **/
    public function inquirySessionInfo()
    {
        return $this->hasOne(InquirySessionInfo::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function locations()
    {
        return $this->belongsToMany(Location::class, "_inquiries_locations");
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function inquiryOverrides()
    {
        return $this->hasMany(InquiryOverride::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function inquiryClaimingEvents()
    {
        return $this->hasMany(InquiryClaimingEvent::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function timers()
    {
        return $this->hasMany(Timer::class, 'inquiry_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function userAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_account_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function heatRatingEvents()
    {
        return $this->hasMany(HeatRatingEvent::class);
    }

}
