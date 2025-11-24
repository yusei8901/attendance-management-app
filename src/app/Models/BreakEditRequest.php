<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakEditRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'attendance_id',
        'break_id',
        'old_break_start',
        'old_break_end',
        'old_break_time',
        'new_break_start',
        'new_break_end',
        'new_break_time',
        'remarks',
        'status',
    ];

    protected $casts = [
        'old_break_start' => 'datetime:H:i',
        'old_break_end' => 'datetime:H:i',
        'new_break_start' => 'datetime:H:i',
        'new_break_end' => 'datetime:H:i',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
    public function breakTime()
    {
        return $this->belongsTo(BreakTime::class, 'break_id');
    }
}
