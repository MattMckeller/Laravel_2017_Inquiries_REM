<?php

namespace App\Models\AccessControl;

use \App\Models\RestrictedModel as RestrictedModel;

/**
 * Class acl_controllers
 * @package App\Models
 * @version November 23, 2016, 9:26 pm UTC
 */
class AclController extends RestrictedModel
{

    public $table = 'acl_controllers';
    var $timestamps = false;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'id', //Allowed for migrations
        'controller'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'controller' => 'string'
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
        return $this->belongsToMany(AclRole::class, '_acl_controllers_acl_roles');
    }
}
