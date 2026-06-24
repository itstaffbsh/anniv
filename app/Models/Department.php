<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name'];

    public function attendees()
    {
        return $this->hasMany(Attendee::class);
    }
}
