<?php

namespace App\Models\Marketing;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class BrandExposureType
 * @package App\Models
 * @version November 23, 2016, 9:26 pm UTC
 */
class BrandExposureType extends AccessControlledModel
{

    public $table = 'brand_exposure_types';
    public $timestamps = false;
    
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
    public function brandExposureType()
    {
        return $this->hasOne(BrandExposureType::class);
    }
}
