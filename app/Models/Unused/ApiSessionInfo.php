<?php 

namespace App\Models\Unused;

use App\Models\RestrictedModel;

class ApiSessionInfo extends RestrictedModel {
    public $table = 'ci_sessions_api';
    public $timestamps = false;


    public $fillable = [
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];
}
