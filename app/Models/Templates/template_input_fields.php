<?php

namespace App\Models;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class template_input_fields
 * @package App\Models
 * @version November 23, 2016, 9:29 pm UTC
 */
class template_input_fields extends AccessControlledModel
{

    public $table = 'template_input_fields';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'name',
        'user_type',
        'is_secure',
        'is_required'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'user_type' => 'string'
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
    public function documentTemplatesTemplateInputFields()
    {
        return $this->hasMany(\App\Models\DocumentTemplatesTemplateInputField::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function inputFieldCoordinatesTemplateInputFields()
    {
        return $this->hasMany(\App\Models\InputFieldCoordinatesTemplateInputField::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function inputFieldGroupsTemplateInputFields()
    {
        return $this->hasMany(\App\Models\InputFieldGroupsTemplateInputField::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function inputFieldTypesTemplateInputFields()
    {
        return $this->hasMany(\App\Models\InputFieldTypesTemplateInputField::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function templateInputFieldsValidationTypes()
    {
        return $this->hasMany(\App\Models\TemplateInputFieldsValidationType::class);
    }
}
