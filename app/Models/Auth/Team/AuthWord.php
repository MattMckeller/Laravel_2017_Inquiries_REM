<?php

namespace App\Models\Auth\Team;

use \App\Models\RestrictedModel as RestrictedModel;

/**
 * Class auth_words
 * @package App\Models
 * @version November 23, 2016, 9:26 pm UTC
 */
class AuthWord extends RestrictedModel
{

    public $table = 'auth_words';
    public $timestamps = false;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'word'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'word' => 'string'
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
    public function userAccount()
    {
        return $this->hasOne(UserAccount::class);
    }

    
}
