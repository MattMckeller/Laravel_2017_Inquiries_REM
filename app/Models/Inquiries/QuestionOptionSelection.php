<?php

namespace App\Models\Inquiries;


use \App\Models\AccessControlledModel as AccessControlledModel;

class QuestionOptionSelection extends AccessControlledModel
{
    public $table = '_questions_option_choices';
    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function optionChoice() {
        return $this->belongsTo(OptionChoice::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function question() {
        return $this->belongsTo(Question::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function questionOptionSelection() {
        return $this->belongsTo(InquirySourceSelection::class, "inquiry_source_selection_id", "id");
    }
}
