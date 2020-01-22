<?php

namespace App\Models;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class validation_types
 * @package App\Models
 * @version November 23, 2016, 9:35 pm UTC
 */
class validation_types extends AccessControlledModel
{

    public $table = 'validation_types';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'name',
        'is_regex'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string'
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
    public function documentInputFieldsValidationTypes()
    {
        return $this->hasMany(\App\Models\DocumentInputFieldsValidationType::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function templateInputFieldsValidationTypes()
    {
        return $this->hasMany(\App\Models\TemplateInputFieldsValidationType::class);
    }
}
