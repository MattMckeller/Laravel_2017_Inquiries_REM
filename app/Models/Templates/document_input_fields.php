<?php

namespace App\Models;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class document_input_fields
 * @package App\Models
 * @version November 23, 2016, 9:27 pm UTC
 */
class document_input_fields extends AccessControlledModel
{

    public $table = 'document_input_fields';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'name',
        'value',
        'group_name',
        'signing_user_type',
        'signing_user_id',
        'is_signed',
        'is_encrypted',
        'decryption_key_ciphertext',
        'value_ciphertext',
        'is_required',
        'is_secure'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'value' => 'string',
        'group_name' => 'string',
        'signing_user_type' => 'string',
        'decryption_key_ciphertext' => 'string',
        'value_ciphertext' => 'string'
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
    public function documentInputFieldsDocumentSigningEvents()
    {
        return $this->hasMany(\App\Models\DocumentInputFieldsDocumentSigningEvent::class);
    }

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
    public function documentInputFieldsDocuments()
    {
        return $this->hasMany(\App\Models\DocumentInputFieldsDocument::class);
    }

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
    public function documentInputFieldsInputFieldGroups()
    {
        return $this->hasMany(\App\Models\DocumentInputFieldsInputFieldGroup::class);
    }

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
    public function documentInputFieldsLeases()
    {
        return $this->hasMany(\App\Models\DocumentInputFieldsLease::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function documentInputFieldsValidationTypes()
    {
        return $this->hasMany(\App\Models\DocumentInputFieldsValidationType::class);
    }
}
