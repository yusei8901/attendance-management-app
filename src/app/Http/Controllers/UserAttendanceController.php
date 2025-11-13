<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class UserAttendanceController extends Controller
{



    // 勤怠画面表示
    public function attend() {
        $user = auth()->user();
        $today = now()->toDateString();
        // 今日の勤怠を取得
        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();
        // 今日の最後の休憩
        $latestBreak = null;
        if ($attendance) {
            $latestBreak = $attendance->breaks()->latest()->first();
        }
        return view('user.attendance.attend', compact('attendance', 'latestBreak'));
    }
    // 出勤処理
    public function workStart()
    {
        $user = auth()->user();
        $today = now()->toDateString();
        // 既に出勤済みなら何もしない
        $exists = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->exists();
        if (!$exists) {
            Attendance::create([
                'user_id' => $user->id,
                'work_date' => $today,
                'start_time' => now()->format('H:i')
            ]);
        }
        return redirect()->route('user.attendance.attend');
    }
    // 退勤処理
    public function workEnd()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('work_date', now()->toDateString())
            ->first();
        // 勤務時間 = 出勤→退勤 − 総休憩時間
        $clockOut = now();
        $workMinutes = Carbon::parse($attendance->start_time)->diffInMinutes($clockOut);
        // 休憩時間合計
        $breakMinutes = $attendance->breaks->sum('break_time');
        $attendance->update([
            'end_time' => $clockOut->format('H:i'),
            'work_time' => $workMinutes - $breakMinutes,
        ]);
        return redirect()->route('user.attendance.attend');
    }
    // 休憩開始
    public function breakStart()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('work_date', now()->toDateString())
            ->first();

        $attendance->breaks()->create([
            'break_start' => now()->format('H:i'),
        ]);

        return redirect()->route('user.attendance.attend');
    }
    // 休憩終了
    public function breakEnd()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('work_date', now()->toDateString())
            ->first();
        $break = $attendance->breaks()->whereNull('break_end')->first();
        $breakEnd = now();
        $breakTime = $break->break_start
            ? $breakEnd->diffInMinutes(Carbon::parse($break->break_start))
            : 0;
        $break->update([
            'break_end' => $breakEnd->format('H:i'),
            'break_time' => $breakTime,
        ]);
        return redirect()->route('user.attendance.attend');
    }
}
