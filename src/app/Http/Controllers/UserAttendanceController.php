<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CorrectionRequest;

class UserAttendanceController extends Controller
{
    // 勤怠一覧画面表示
    public function index($year = null, $month = null)
    {
        $current = Carbon::createFromDate(
            $year ?? Carbon::now()->year,
            $month ?? Carbon::now()->month,
            1
        );
        $start = $current->copy()->startOfMonth();
        $end = $current->copy()->endOfMonth();
        $attends = Attendance::with('breaks')
            ->where('user_id', Auth::id())
            ->whereBetween('work_date', [$start, $end])
            ->orderBy('work_date')
            ->get();
        return view('user.attendance.index', compact('current', 'attends'));
    }
    // 勤怠詳細画面の表示
    public function detail($id)
    {
        $user = auth()->user();
        $attend = Attendance::findOrFail($id);
        return view('user.attendance.detail', compact('attend', 'user'));
    }
    // 勤怠詳細画面の編集処理
    public function edit(CorrectionRequest $request, $id)
    {
        $attend = Attendance::with('breaks')->findOrFail($id);
        $start = Carbon::parse($request->input('start_time'));
        $end = Carbon::parse($request->input('end_time'));
        $workMinutes = $start->diffInMinutes($end);
        $breakTotal = 0;
        $requestBreaks = $request->input('breaks', []);
        foreach ($attend->breaks as $index => $break) {
            $breakStart = $requestBreaks[$index]['break_start'] ?? null;
            $breakEnd   = $requestBreaks[$index]['break_end'] ?? null;
            // break_start または break_end が無ければこの休憩は計算しない
            if (!$breakStart || !$breakEnd) {
                continue;
            }
            $startTime = Carbon::parse($breakStart);
            $endTime   = Carbon::parse($breakEnd);
            $breakMinutes = $startTime->diffInMinutes($endTime);
            $breakTotal += $breakMinutes;
            $break->update([
                'break_start' => $breakStart,
                'break_end'   => $breakEnd,
                'break_time'  => $breakMinutes,
            ]);
        }
        $attend->update([
            'start_time' => $request->input('start_time'),
            'end_time'   => $request->input('end_time'),
            'remarks'    => $request->input('remarks'),
            'work_time'  => max($workMinutes - $breakTotal, 0),
            'stamp_correction_request' => 'pending',
        ]);
        return redirect()->route('user.attendance.detail', ['id' => $attend->id])->with('success_message', '勤怠情報を更新し、申請を送信しました。');
    }

    // 勤怠画面表示
    public function attend()
    {
        $user = auth()->user();
        $today = now()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();
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
        $clockOut = now();
        $workMinutes = Carbon::parse($attendance->start_time)->diffInMinutes($clockOut);
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
