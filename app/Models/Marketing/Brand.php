<?php

namespace App\Models\Marketing;

use \App\Models\Inquiries\Inquiry;
use \App\Models\Application;
use \App\Models\Inventory\Building;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class brand
 * @package App\Models
 * @version November 23, 2016, 9:27 pm UTC
 */
class Brand extends AccessControlledModel
{

    public $table = 'brands';
    public $timestamps = false;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'brand_name',
        'brand_code',
        'description'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'brand_name' => 'string',
        'brand_code' => 'string',
        'description' => 'string'
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
    public function buildings()
    {
        return $this->hasMany(Building::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function inquiries()
    {
        return $this->hasMany(Inquiry::class);
    }
}
