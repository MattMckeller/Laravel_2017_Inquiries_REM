<?php

namespace App\Models\Documents;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class document_variables
 * @package App\Models
 * @version November 23, 2016, 9:27 pm UTC
 */
class document_variables extends AccessControlledModel
{

    public $table = 'document_variables';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'name',
        'value'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'value' => 'string'
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
    public function documentVariablesDocuments()
    {
        return $this->hasMany(\App\Models\DocumentVariablesDocument::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function documentVariablesLeases()
    {
        return $this->hasMany(\App\Models\DocumentVariablesLease::class);
    }
}
