<?php

namespace App\Models\Inquiries;

use \App\Models\AccessControlledModel as AccessControlledModel;

class InquirySource extends AccessControlledModel
{
    public $table = 'inquiry_sources';
    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    public $fillable = [
        'name',
        'type',
        'display_name'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'display_name' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function inquirySourceOption() {
        return $this->belongsToMany(InquirySourceOption::class, "_inquiry_sources_inquiry_source_options");
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function inquirySourceSelection() {
        return $this->hasMany(InquirySourceSelection::class, "inquiry_source_id", "id");
    }
}
