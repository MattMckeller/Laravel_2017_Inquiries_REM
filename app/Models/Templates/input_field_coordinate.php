<?php

namespace App\Models;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class input_field_coordinate
 * @package App\Models
 * @version November 28, 2016, 3:07 pm UTC
 */
class input_field_coordinate extends AccessControlledModel
{

    public $table = 'input_field_coordinates';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'pdf_x',
        'pdf_y',
        'pdf_length',
        'pdf_height',
        'css_left',
        'css_top',
        'css_width',
        'css_height',
        'page'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'pdf_x' => 'integer',
        'pdf_y' => 'integer',
        'pdf_length' => 'integer',
        'pdf_height' => 'integer',
        'css_left' => 'integer',
        'css_top' => 'integer',
        'css_width' => 'integer',
        'css_height' => 'integer',
        'page' => 'integer'
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
    public function documentInputFieldsInputFieldCoordinates()
    {
        return $this->hasMany(\App\Models\DocumentInputFieldsInputFieldCoordinate::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function inputFieldCoordinatesTemplateInputFields()
    {
        return $this->hasMany(\App\Models\InputFieldCoordinatesTemplateInputField::class);
    }
}
