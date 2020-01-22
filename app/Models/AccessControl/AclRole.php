<?php

namespace App\Models\AccessControl;

use App\Models\Auth\Team\ApiKey;
use \App\Models\RestrictedModel as RestrictedModel;

/**
 * Class acl_roles
 * @package App\Models
 * @version November 23, 2016, 9:26 pm UTC
 */
class AclRole extends RestrictedModel
{

    public $table = 'acl_roles';
    var $timestamps = false;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'id', //Allowed for migrations
        'role'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'role' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     **/
    public function aclControllers()
    {
        return $this->belongsToMany(AclController::class, '_acl_controllers_acl_roles');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     **/
    public function aclObjects()
    {
        return $this->belongsToMany(AclObject::class, '_acl_objects_acl_roles')->withPivot('field_permission_list');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     **/
    public function aclResponsibilities()
    {
        return $this->belongsToMany(AclResponsibility::class, '_acl_responsibilities_acl_roles', 'acl_role_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     **/
    public function apiKeys()
    {
        return $this->hasMany(ApiKey::class);
    }
}
