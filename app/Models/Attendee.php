<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendee extends Model
{
    protected $fillable = [
        'attendee_id',
        'email',
        'invite_phone',
        'invitation_token',
        'name',
        'phone',
        'company',
        'company_id',
        'department_id',
        'ticket_type',
        'qr_token',
        'status',
        'checkin_status',
        'checkin_time',
        'checked_by'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
