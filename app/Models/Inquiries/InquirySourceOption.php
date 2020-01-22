<?php

namespace App\Models\Inquiries;

use \App\Models\AccessControlledModel as AccessControlledModel;

class InquirySourceOption extends AccessControlledModel
{
    public $table = 'inquiry_source_options';
    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    public $fillable = [
        'name',
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function inquirySource() {
        return $this->hasMany(InquirySourceOption::class, "_inquiry_sources_inquiry_source_options");
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function inquirySourceSelection() {
        return $this->belongsToMany(InquirySourceSelection::class, "inquiry_source_option_id", "id");
    }
}
