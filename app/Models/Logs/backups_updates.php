<?php

namespace App\Models;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class backups_updates
 * @package App\Models
 * @version November 28, 2016, 3:09 pm UTC
 */
class backups_updates extends AccessControlledModel
{

    public $table = 'backups_update';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'editing_user',
        'object_name',
        'obj_id',
        'json_field_data',
        'json_relationships',
        'timestamp'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'editing_user' => 'integer',
        'object_name' => 'string',
        'json_field_data' => 'string',
        'json_relationships' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    
}
