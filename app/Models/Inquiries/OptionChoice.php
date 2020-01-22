<?php

namespace App\Models\Inquiries;

use \App\Models\AccessControlledModel as AccessControlledModel;

class OptionChoice extends AccessControlledModel
{
    public $table = 'option_choices';
    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public $fillable = [
        'option_choice_name',
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
    public function question()
    {
        return $this->belongsToMany(Question::class, "_questions_option_choices");
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function questionOptionSelection() {
        return $this->belongsToMany(QuestionOptionSelection::class, "option_choice_id", "id");
    }
}
