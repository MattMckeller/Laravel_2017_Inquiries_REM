<?php

namespace App\Models\Appointments;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class EmailMessage extends Model
{
    public $table = 'email_messages';

    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    public $fillable = [
        'user_type',
        'event_type',
        'from_email_address',
        'view_path',
        'subject'
    ];

    /**
     * @param Builder $query
     * @param $user_type
     * @param $event_type
     * @return mixed
     */
    public function scopeOfUserEventType($query, $user_type, $event_type) {
        return $query->where('user_type', $user_type)->where('event_type', $event_type);
    }
}
