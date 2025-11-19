<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceEditRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'attendance_id',
        'old_start_time',
        'old_end_time',
        'old_work_time',
        'new_start_time',
        'new_end_time',
        'new_work_time',
        'remarks',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
