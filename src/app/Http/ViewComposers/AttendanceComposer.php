<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Models\Attendance;

class AttendanceComposer
{
    public function compose(View $view)
    {
        if (auth()->check()) {
            $user = auth()->user();
            $today = now()->toDateString();
            $attendance = Attendance::where('user_id', $user->id)
                ->where('work_date', $today)
                ->first();
            $view->with('attendance', $attendance);
        }
    }
}
