<?php

namespace App\Models\Documents;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class document_types
 * @package App\Models
 * @version November 23, 2016, 9:27 pm UTC
 */
class document_types extends AccessControlledModel
{

    public $table = 'document_types';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'name',
        'short_name',
        'category'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'short_name' => 'string',
        'category' => 'string'
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
    public function documentTemplatesDocumentTypes()
    {
        return $this->hasMany(\App\Models\DocumentTemplatesDocumentType::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function documentTypesDocuments()
    {
        return $this->hasMany(\App\Models\DocumentTypesDocument::class);
    }
}
