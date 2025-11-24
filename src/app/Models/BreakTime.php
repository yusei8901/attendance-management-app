<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    protected $table = 'breaks';

    protected $fillable = [
        'attendance_id',
        'break_start',
        'break_end',
        'break_time'
    ];
    protected $casts = [
        'break_start' => 'datetime:H:i',
        'break_end' => 'datetime:H:i',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
    public function breakEditRequests()
    {
        return $this->hasMany(BreakEditRequest::class, 'break_id');
    }
}
