<?php

namespace App\Models;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class input_field_types
 * @package App\Models
 * @version November 23, 2016, 9:28 pm UTC
 */
class input_field_types extends AccessControlledModel
{

    public $table = 'input_field_types';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'name'
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
    public function documentInputFieldsInputFieldTypes()
    {
        return $this->hasMany(\App\Models\DocumentInputFieldsInputFieldType::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function inputFieldTypesTemplateInputFields()
    {
        return $this->hasMany(\App\Models\InputFieldTypesTemplateInputField::class);
    }
}
