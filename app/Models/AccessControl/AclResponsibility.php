<?php

namespace App\Models\AccessControl;

use \App\Models\RestrictedModel as RestrictedModel;

/**
 * Class acl_responsibilities
 * @package App\Models
 * @version November 23, 2016, 9:26 pm UTC
 */
class AclResponsibility extends RestrictedModel
{

    public $table = 'acl_responsibilities';
    var $timestamps = false;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'id', //Allowed for migrations
        'responsibility'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'responsibility' => 'string'
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
    public function aclRoles()
    {
        return $this->belongsToMany(AclRole::class, '_acl_responsibilities_acl_roles', 'acl_responsibility_id');
    }
}
