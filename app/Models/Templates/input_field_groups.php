<?php

namespace App\Models;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class input_field_groups
 * @package App\Models
 * @version November 23, 2016, 9:28 pm UTC
 */
class input_field_groups extends AccessControlledModel
{

    public $table = 'input_field_groups';
    
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
    public function documentInputFieldsInputFieldGroups()
    {
        return $this->hasMany(\App\Models\DocumentInputFieldsInputFieldGroup::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function inputFieldGroupsTemplateInputFields()
    {
        return $this->hasMany(\App\Models\InputFieldGroupsTemplateInputField::class);
    }
}
