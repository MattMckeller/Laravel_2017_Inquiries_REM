<?php

namespace App\Models\Documents;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class documents
 * @package App\Models
 * @version November 23, 2016, 9:27 pm UTC
 */
class documents extends AccessControlledModel
{

    public $table = 'documents';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'unique_document_id',
        'document_decryption_key_ciphertext',
        'is_active',
        'is_manual'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'unique_document_id' => 'string',
        'document_decryption_key_ciphertext' => 'string'
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
    public function customerEnvelopesDocuments()
    {
        return $this->hasMany(\App\Models\CustomerEnvelopesDocument::class);
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
    public function documentSigningEventsDocuments()
    {
        return $this->hasMany(\App\Models\DocumentSigningEventsDocument::class);
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
    public function documentTypesDocuments()
    {
        return $this->hasMany(\App\Models\DocumentTypesDocument::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function documentVariablesDocuments()
    {
        return $this->hasMany(\App\Models\DocumentVariablesDocument::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function documentVersionsDocuments()
    {
        return $this->hasMany(\App\Models\DocumentVersionsDocument::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function documentsLeases()
    {
        return $this->hasMany(\App\Models\DocumentsLease::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function documentsSigningQueues()
    {
        return $this->hasMany(\App\Models\DocumentsSigningQueue::class);
    }
}
