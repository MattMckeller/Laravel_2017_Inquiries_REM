<?php

namespace App\Models\Inquiries;

use \App\Models\AccessControlledModel as AccessControlledModel;


class Question extends AccessControlledModel
{
    public $table = 'questions';
    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    public $fillable = [
        'question_name',
        'question_subtext',
        'question_required',
        'answer_required',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'question_name' => 'string',
        'question_subtext' => 'string',
        'question_required' => 'boolean',
        'answer_required' => 'boolean'
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
    public function inputType()
    {
        return $this->belongsTo(QuestionInputType::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function optionChoice() {
        return $this->belongsToMany(OptionChoice::class, "_questions_option_choices");
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function questionOptionSelection() {
        return $this->hasMany(QuestionOptionSelection::class, "question_id", "id");
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function questionType()
    {
        return $this->belongsTo(QuestionType::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function questionGroup()
    {
        return $this->belongsTo(QuestionGroup::class);
    }
}
