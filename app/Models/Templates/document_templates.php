<?php

namespace App\Models;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class document_templates
 * @package App\Models
 * @version November 23, 2016, 9:27 pm UTC
 */
class document_templates extends AccessControlledModel
{

    public $table = 'document_templates';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'name',
        'total_pages',
        'is_current',
        'creation_timestamp',
        'immediate_creation',
        'optional_document',
        'config_name'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'total_pages' => 'integer',
        'config_name' => 'string'
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
    public function documentInputFieldsDocumentTemplates()
    {
        return $this->hasMany(\App\Models\DocumentInputFieldsDocumentTemplate::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function documentSetsDocumentTemplates()
    {
        return $this->hasMany(\App\Models\DocumentSetsDocumentTemplate::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function documentTemplatesDocumentTypes()
    {
        return $this->hasMany(\App\Models\DocumentTemplatesDocumentType::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function documentTemplatesDocuments()
    {
        return $this->hasMany(\App\Models\DocumentTemplatesDocument::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function documentTemplatesLeases()
    {
        return $this->hasMany(\App\Models\DocumentTemplatesLease::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function documentTemplatesTemplateGenerationVariables()
    {
        return $this->hasMany(\App\Models\DocumentTemplatesTemplateGenerationVariable::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function documentTemplatesTemplateInputFields()
    {
        return $this->hasMany(\App\Models\DocumentTemplatesTemplateInputField::class);
    }
}
