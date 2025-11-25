<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attendance;
use App\Models\AttendanceEditRequest;
use App\Models\BreakEditRequest;
use App\Http\Controllers\Controller;

class AdminRequestsController extends Controller
{
    // 修正申請一覧画面表示
    public function index()
    {
        $pendingAttends = AttendanceEditRequest::with('attendance', 'user')->where('status', 'pending')->get();
        $approvedAttends = AttendanceEditRequest::with('attendance', 'user')->where('status', 'approved')->get();
        return view('admin.requests.index', compact('pendingAttends', 'approvedAttends'));
    }
    // 修正申請詳細画面表示
    public function detail($attendance_correct_request_id)
    {
        $id = $attendance_correct_request_id;
        $attend = AttendanceEditRequest::with('user', 'attendance')
            ->where('attendance_id', $id)->first();
        $breaks = BreakEditRequest::with('user')
            ->where('attendance_id', $id)->get();
        return view('admin.requests.detail', compact('attend', 'breaks'));
    }
    // 修正申請承認機能
    public function update($attendance_correct_request_id)
    {
        $id = $attendance_correct_request_id;
        $oldAttend = Attendance::with('breaks')->findOrFail($id);
        $oldBreaks = $oldAttend->breaks;
        $attend = AttendanceEditRequest::with('user', 'attendance')
            ->where('attendance_id', $id)->first();
        $breaks = BreakEditRequest::with('user')
            ->where('attendance_id', $id)->get();
        foreach ($oldBreaks as $index => $break) {
            $old = $oldBreaks[$index] ?? null;
            $new = $breaks[$index] ?? null;
            if (!$new) {
                continue;
            }
            $old->update([
                'break_start' => $new->new_break_start,
                'break_end' => $new->new_break_end,
                'break_time' => $new->new_break_time
            ]);
            $new->update([
                'status' => 'approved'
            ]);
        }
        $oldAttend->update([
            'start_time' => $attend->new_start_time,
            'end_time' => $attend->new_end_time,
            'remarks' => $attend->remarks,
            'work_time' => $attend->new_work_time,
            'status' => 'approved'
        ]);
        $attend->update(['status' => 'approved']);
        return redirect()->route('admin.request.detail', compact('attendance_correct_request_id', 'attend', 'breaks'))->with('success_message', '承認が完了しました');
    }
}
