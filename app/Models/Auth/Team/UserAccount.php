<?php

namespace App\Models\Auth\Team;

use App\Models\Customers\Note;
use App\Models\Inquiries\InquiryClaimingEvent;
use App\Models\Inquiries\InquiryEvent;
use App\Models\Inquiries\HeatRatingEvent;
use App\Models\RestrictedModel;
use \App\Models\Auth\Team\ApiKey;
use \App\Models\Auth\Team\UserAccountInformation;
use \App\Models\Auth\Team\AuthWord;
use App\Models\Appointments\Appointment;
use App\Models\Inquiries\Inquiry;
use Illuminate\Support\Facades\App;

/**
 * Class user_accounts
 * @package App\Models
 * @version November 23, 2016, 9:30 pm UTC
 */
class UserAccount extends RestrictedModel
{

    public $table = 'user_accounts';
    public $timestamps = false;
    
    public $fillable = [
        'webserver',
        'username',
        'password',
        'salt',
        'work_email_address',
        'ip_address',
        'activated',
        'forgot_password_expiriation',
        'last_login_date',
        'failed_logins',
        'failed_login_ip',
        'failed_logins_second_factor',
        'locked',
        'banned',
        'ban_expiration',
        'account_creation_date',
        'update_email_address',
        'activation_token',
        'forgot_password_token'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'username' => 'string',
        'password' => 'string',
        'salt' => 'string',
        'work_email_address' => 'string',
        'ip_address' => 'string',
        'failed_logins' => 'integer',
        'failed_login_ip' => 'string',
        'update_email_address' => 'string',
        'activation_token' => 'string',
        'forgot_password_token' => 'string'
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
    public function apiKey()
    {
        return $this->hasOne(ApiKey::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function userAccountInformation()
    {
        return $this->belongsTo(UserAccountInformation::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function authWord()
    {
        return $this->belongsTo(AuthWord::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function inquiryEvent()
    {
        return $this->hasMany(InquiryEvent::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function inquiries()
    {
        return $this->hasMany(Inquiry::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function inquiryClaimingEvents()
    {
        return $this->hasMany(InquiryClaimingEvent::class, 'user_account_id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function heatRatingEvents()
    {
        return $this->hasMany(HeatRatingEvent::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function actingInquiryClaimingEvents()
    {
        return $this->hasMany(InquiryClaimingEvent::class, 'active_agent_id');
    }

    /**
     * Description: Attempts to validate the model based on its current username, password, and second factor authentication value.
     * @param bool $second_factor_auth true if it needs to verify the second factor authentication value
     * @param string $second_factor_method the type of second factor authentication being used
     * @return bool true|false depending on whether or not the credentials of this account matched an existing account
     */
    function login($second_factor_auth = false, $second_factor_method = '')
    {
        $teamAuth = App::make('TeamAuth');
        return $teamAuth->login($second_factor_auth, $second_factor_method, $this);
    }
    /**/
    
    function save(array $options = array()){
        $this->_encrypt_pass('password'); //Makes sure the password gets encrypted before being saved.
        return parent::save($options);
    }
    /**
     * Description: Returns a list of authentication words. If this is a valid account
     *      it will include its correct auth word.
     * @param int $amount_of_words the number of words you want returned, A maximum is defined however.
     * @return array array of words
     */
    function auth_word_options($amount_of_words = 5){
        $teamAuth = App::make('TeamAuth');
        return $teamAuth->actual_auth_word_options($amount_of_words, $this);
    }
    /**********************************
     * VALIDATION FUNCTIONS/ ENCRYPTION
    /**********************************/

    /**
     * Encrypt (prep)
     *
     * If not already encrypted->encrypts the field with this objects salt.
     *
     * @access    private
     * @param    string
     */
    function _encrypt_pass($field)
    {
        if (!empty($this->{$field}))
        {
            $already_hashed = password_get_info($this->{$field});
            if($already_hashed['algo'] === 0){
                $this->{$field} = password_hash($this->{$field}, PASSWORD_BCRYPT);
                return true;
            }else{
                return true;
            }
        }
    }

}
