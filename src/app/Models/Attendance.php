<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'start_time',
        'end_time',
        'work_time',
        'remarks',
        'status',
    ];

    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }
    public function editRequests()
    {
        return $this->hasMany(AttendanceEditRequest::class);
    }
    public function breakEditRequests()
    {
        return $this->hasMany(BreakEditRequest::class);
    }
}
