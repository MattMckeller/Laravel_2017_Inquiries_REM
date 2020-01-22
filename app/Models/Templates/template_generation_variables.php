<?php

namespace App\Models;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class template_generation_variables
 * @package App\Models
 * @version November 23, 2016, 9:29 pm UTC
 */
class template_generation_variables extends AccessControlledModel
{

    public $table = 'template_generation_variables';
    
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
    public function documentTemplatesTemplateGenerationVariables()
    {
        return $this->hasMany(\App\Models\DocumentTemplatesTemplateGenerationVariable::class);
    }
}
