<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminStaffController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('admin.staff.index', compact('users'));
    }
}
