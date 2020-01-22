<?php

namespace App\Models\Inquiries;

use \App\Models\AccessControlledModel as AccessControlledModel;
use App\Models\Inquiries\Inquiry;

class InquiryPreference extends AccessControlledModel {

	public $table = 'inquiry_preferences';
	public $timestamps = false;

	const CREATED_AT = 'created_at';
	const UPDATED_AT = 'updated_at';

	public $fillable = [
		'name',
		'display_question',
		'type',
		'value',
	];

	/**
	 * The attributes that should be casted to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'name'=>'string',
		'display_question'=>'string',
		'type'=>'string',
		'value'=>'string'
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
	public function inquiries()
	{
		return $this->belongsToMany(Inquiry::class, "_inquiries_inquiry_preferences");
	}

}
