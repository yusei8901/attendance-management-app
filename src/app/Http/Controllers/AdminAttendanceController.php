<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
    // 勤怠詳細画面
    public function detail($id)
    {
        return view('admin.attendance.detail');
    }
}
