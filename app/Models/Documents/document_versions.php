<?php

namespace App\Models\Documents;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class document_versions
 * @package App\Models
 * @version November 23, 2016, 9:27 pm UTC
 */
class document_versions extends AccessControlledModel
{

    public $table = 'document_versions';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'version_hash',
        'is_current',
        'dir_path',
        'file_name',
        'is_encrypted',
        'decryption_key_ciphertext'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'version_hash' => 'string',
        'dir_path' => 'string',
        'file_name' => 'string',
        'decryption_key_ciphertext' => 'string'
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
    public function documentSigningEventsDocumentVersions()
    {
        return $this->hasMany(\App\Models\DocumentSigningEventsDocumentVersion::class);
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
    public function documentVersionsLeases()
    {
        return $this->hasMany(\App\Models\DocumentVersionsLease::class);
    }
}
