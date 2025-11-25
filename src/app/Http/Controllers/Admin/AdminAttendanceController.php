<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Requests\EditRequest;
use App\Models\Attendance;
use App\Models\User;
use App\Http\Controllers\Controller;

class AdminAttendanceController extends Controller
{
    // 日次勤怠一覧画面表示
    public function index($year = null, $month = null, $day = null)
    {
        $current = Carbon::createFromDate(
            $year ?? Carbon::now()->year,
            $month ?? Carbon::now()->month,
            $day ?? Carbon::now()->day
        );
        $admin = Auth::user();
        $users = User::with(['attendanceOfDate' => function ($q) use ($current) {
            $q->whereDate('work_date', $current)->with('breaks');
        }])->get();
        return view('admin.attendance.index', compact('current', 'users'));
    }
    // スタッフ勤怠詳細画面
    public function detail($id)
    {
        $attend = Attendance::with('user', 'editRequests', 'breakEditRequests')->findOrFail($id);
        return view('admin.attendance.detail', compact('attend'));
    }
    // 勤怠詳細画面の編集処理
    public function edit(EditRequest $request, $id)
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
            'status' => 'before_request',
        ]);
        return redirect()->route('admin.attendance.detail', ['id' => $attend->id])->with('success_message', '勤怠情報を編集しました');
    }
}
