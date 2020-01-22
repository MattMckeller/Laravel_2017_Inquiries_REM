<?php

namespace App\Models\Inquiries;

use App\Models\Auth\Team\UserAccount;
use \App\Models\AccessControlledModel as AccessControlledModel;
use App\Models\Timers\Timer;

class InquiryEvent extends AccessControlledModel
{
    public $table = 'inquiry_events';
    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    public $fillable = [
        'id',
        'provided_timestamp'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'provided_timestamp' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];


    public function isVoicemail(){
        //If the contact was successful this event cannot be a voicemail
        if($this->successful_contact == true){return false;}
        //Check contact type
        $sourceOption = $this->inquirySource()->first();
        if($sourceOption->type == 'phone'){
            return true;
        }else{
            return false;
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function answer()
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function inquiry()
    {
        return $this->belongsTo(Inquiry::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     **/
    public function inquiryNote()
    {
        return $this->hasOne(InquiryNote::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function inquirySource() {
        return $this->belongsTo(InquirySource::class, "inquiry_source_selection_id");
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     **/
    public function inquirySourceOption() {
        return $this->hasManyThrough(InquirySourceOption::class, InquirySourceSelection::class, "id", "inquiry_source_selection_id");
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function inquirySourceSelection() {
        return $this->belongsTo(InquirySourceSelection::class, "inquiry_source_selection_id", "id");
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     **/
    public function timer()
    {
        return $this->hasOne(Timer::class, 'inquiry_event_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
     public function userAccount() {
        return $this->belongsTo(UserAccount::class);
    }
}
