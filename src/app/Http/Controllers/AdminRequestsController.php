<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceEditRequest;


class AdminRequestsController extends Controller
{
    public function index()
    {
        $pendingAttends = AttendanceEditRequest::with('attendance', 'user')->where('status', 'pending')->get();
        $approvedAttends = AttendanceEditRequest::with('attendance', 'user')->where('status', 'approved')->get();
        return view('admin.requests.index', compact('pendingAttends', 'approvedAttends'));
    }
}
