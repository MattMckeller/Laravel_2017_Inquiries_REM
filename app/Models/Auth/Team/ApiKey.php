<?php

namespace App\Models\Auth\Team;

use \App\Models\RestrictedModel as RestrictedModel;
use App\Models\AccessControl\AclRole;
/**
 * Class api_keys
 * @package App\Models
 * @version November 23, 2016, 9:26 pm UTC
 */
class ApiKey extends RestrictedModel
{

    public $table = 'api_keys';
    var $timestamps = false;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'webserver_key',
        'key_value',
        'timestamp'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'key_value' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function aclRole()
    {
        return $this->belongsTo(AclRole::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function userAccount()
    {
        return $this->belongsTo(UserAccount::class);
    }

    /*
     * Generates a new api key value for the the current instance and saves it. Fails if key is currently null.
     * @return returns the new value of the key
     */
    public function _generate_new_key(){
        if($this->key_value != null){
            $this->key_value = generate_random_string(32);
            $this->save();
            return $this->key_value;
        }else{
            return false;
        }
    }
}
