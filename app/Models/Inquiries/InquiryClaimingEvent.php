<?php

namespace App\Models\Inquiries;

use App\Models\Auth\Team\UserAccount;
use \App\Models\AccessControlledModel as AccessControlledModel;

class InquiryClaimingEvent extends AccessControlledModel
{
    public $table = 'inquiry_claiming_events';
    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    public $fillable = [
        'id',
        'timestamp',
        'expiration_timestamp',
        'is_claim'
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function inquiry()
    {
        return $this->belongsTo(Inquiry::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function userAccount() {
        return $this->belongsTo(UserAccount::class, 'user_account_id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function actingUser() {
        return $this->belongsTo(UserAccount::class, 'acting_user_id');
    }

    /**
     * Adds a where filter to the current model
     * @param bool $true is claim or is not claim
     * @return mixed
     */
    public function whereIsClaim($true=true){
        return $this->where('is_claim', ($true == true)?(1):(0));
    }

    /**
     * Adds a where filter to the current model
     * @param bool $true is release, or is not release
     * @return mixed
     */
    public function whereIsRelease($true=true){
        return $this->where('is_claim', ($true == true)?(0):(1));
    }

    /**
     * Adds a where filter to the current model
     * @param bool $true is assignment or not
     * @return mixed
     */
    public function whereIsAssignment($true=true){
        return $this->where('is_claim', 999);
    }
}
