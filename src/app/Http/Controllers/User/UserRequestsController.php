<?php

namespace App\Http\Controllers\User;

use App\Models\Attendance;
use App\Models\AttendanceEditRequest;
use App\Http\Requests\CorrectionRequest;
use App\Models\BreakEditRequest;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserRequestsController extends Controller
{
    // 勤怠詳細画面の表示
    public function detail($id)
    {
        $user = auth()->user();
        $attend = Attendance::with('editRequests', 'breakEditRequests')->findOrFail($id);
        return view('user.attendance.detail', compact('attend', 'user'));
    }
    // 勤怠詳細画面の申請処理
    public function request(CorrectionRequest $request, $id)
    {
        $attend = Attendance::with('breaks')->findOrFail($id);
        $newStart = Carbon::parse($request->input('new_start_time'));
        $newEnd = Carbon::parse($request->input('new_end_time'));
        $newWorkMinutes = $newStart->diffInMinutes($newEnd);
        $newBreakTotal = 0;
        $requestBreaks = $request->input('new_breaks', []);
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
            $newBreakTotal += $breakMinutes;
        }
        $oldBreaks = $request->input('old_breaks', []);
        $newBreaks = $request->input('new_breaks', []);
        foreach ($attend->breaks as $index => $break) {
            $old = $oldBreaks[$index] ?? null;
            $new = $newBreaks[$index] ?? null;
            // new が空なら申請対象にしない
            if (!$new) {
                continue;
            }
            $oldStart = $old['break_start'] ?? null;
            $oldEnd   = $old['break_end'] ?? null;
            $newStart = $new['break_start'] ?? null;
            $newEnd   = $new['break_end'] ?? null;
            // 時間を計算
            $oldTime = ($oldStart && $oldEnd) ? Carbon::parse($oldStart)->diffInMinutes($oldEnd) : null;
            $newTime = ($newStart && $newEnd) ? Carbon::parse($newStart)->diffInMinutes($newEnd) : null;
            BreakEditRequest::create([
                'user_id' => auth()->id(),
                'attendance_id' => $attend->id,
                'break_id' => $break->id,
                'old_break_start' => $oldStart,
                'old_break_end'   => $oldEnd,
                'old_break_time'  => $oldTime,
                'new_break_start' => $newStart,
                'new_break_end'   => $newEnd,
                'new_break_time'  => $newTime,
                'remarks' => $request->input('remarks'),
                'status' => 'pending',
            ]);
        }
        $newWorkTime = max($newWorkMinutes - $newBreakTotal, 0);
        AttendanceEditRequest::create([
            'user_id' => auth()->id(),
            'attendance_id' => $attend->id,
            'old_start_time' => $request->input('old_start_time'),
            'old_end_time' => $request->input('old_end_time'),
            'old_work_time' => $attend->work_time,
            'new_start_time' => $request->input('new_start_time'),
            'new_end_time' => $request->input('new_end_time'),
            'new_work_time' => $newWorkTime,
            'remarks' => $request->input('remarks'),
            'status' => 'pending',
        ]);
        $attend->update(['status' => 'pending']);
        return redirect()->route('user.attendance.detail', ['id' => $attend->id])->with('success_message', '勤怠修正申請を送信しました。');
    }
    // 申請一覧画面表示
    public function index(Request $request)
    {
        $user = auth()->user();
        $tab = $request->query('tab', 'pending');
        $tab = in_array($tab, ['pending', 'approved'], true) ? $tab : 'pending';
        $attends = AttendanceEditRequest::with(['user', 'attendance'])
        ->where('user_id', $user->id)
        ->where('status', $tab)
        ->latest()
        ->paginate(5)
        ->appends(['tab' => $tab]);
        return view('user.attendance.requests', compact('attends', 'tab'));
    }
}
