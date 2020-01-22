<?php

namespace App\Models;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class price_tags
 * @package App\Models
 * @version November 23, 2016, 9:29 pm UTC
 */
class price_tags extends AccessControlledModel
{

    public $table = 'price_tags';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'term',
        'installments',
        'payment'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'term' => 'integer',
        'installments' => 'integer',
        'payment' => 'string'
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
    public function bedroomsPriceTags()
    {
        return $this->hasMany(\App\Models\BedroomsPriceTag::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function priceTagsUnits()
    {
        return $this->hasMany(\App\Models\PriceTagsUnit::class);
    }
}
