<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceEditRequest;
use Illuminate\Http\Request;

class UserRequestsController extends Controller
{
    // 申請一覧画面表示
    public function index()
    {
        $user = auth()->user();
        $pendingAttends = AttendanceEditRequest::with('attendance', 'user')
        ->where('user_id', $user->id)
        ->where('status', 'pending')->get();
        $approvedAttends = AttendanceEditRequest::with('attendance', 'user')
            ->where('user_id', $user->id)
        ->where('status', 'approved')->get();
        return view('user.attendance.requests', compact('user', 'pendingAttends', 'approvedAttends'));
    }
}
