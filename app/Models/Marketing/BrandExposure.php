<?php

namespace App\Models\Marketing;

use \App\Models\Application;
use \App\Models\Inquiries\Inquiry;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class BrandExposure
 * @package App\Models
 * @version November 23, 2016, 9:26 pm UTC
 */
class BrandExposure extends AccessControlledModel
{

    public $table = 'brand_exposures';
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
    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function brandExposureTypes()
    {
        return $this->hasMany(BrandExposureType::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function inquiries()
    {
        return $this->belongsToMany(Inquiry::class, "_brand_exposures_inquiries");
    }
}
