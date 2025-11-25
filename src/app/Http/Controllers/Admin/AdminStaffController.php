<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class AdminStaffController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('admin.staff.index', compact('users'));
    }

    public function attend($id, $year = null, $month = null)
    {
        $current = Carbon::createFromDate(
            $year ?? Carbon::now()->year,
            $month ?? Carbon::now()->month,
            1
        );
        $start = $current->copy()->startOfMonth();
        $end = $current->copy()->endOfMonth();
        $user = User::findOrFail($id);
        $attends = Attendance::with('breaks')
            ->where('user_id', $id)
            ->whereBetween('work_date', [$start, $end])
            ->orderBy('work_date')
            ->get();
        return view('admin.staff.attendance', compact('current', 'attends', 'user'));
    }
}
