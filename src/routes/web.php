<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// 一般ユーザー関連
Route::middleware('auth:web')->group(function(){
    // 勤怠登録画面
    Route::get('/attendance', function () {
        return view('user.attendance.attend');
    });
    // 勤怠一覧画面
    Route::get('/attendance/list', function () {
        return view('user.attendance.index');
    });
    // 勤怠詳細画面
    Route::get('/attendance/detail/{id}', function () {
        return view('user.attendance.detail');
    });
    // 申請一覧画面
    Route::get('/stamp_correction_request/list', function () {
        return view('user.attendance.requests');
    });
});

// 管理者関連
Route::prefix('admin')->group(function() {
    Route::get('/login', [LoginController::class, 'create'])->name('admin.login');
    Route::post('login', [LoginController::class, 'store']);
});
Route::middleware('auth:admin')->group(function(){
    // 勤怠一覧画面
    Route::get('/admin/attendance/list', function () {
        return view('admin.attendance.index');
    });
    // 勤怠詳細画面
    Route::get('/admin/attendance/{id}', function () {
        return view('admin.attendance.detail');
    });
    // スタッフ一覧画面
    Route::get('/admin/staff/list', function () {
        return view('admin.staff.index');
    });
    // スタッフ別勤怠一覧画面
    Route::get('/admin/attendance/staff/{id}', function () {
        return view('admin.staff.attendance');
    });
    // 申請一覧画面
    Route::get('/admin/stamp_correction_request/list', function () {
        return view('admin.requests.index');
    });
    // 修正申請承認画面
    Route::get('/admin/stamp_correction_request/approve/{attendance_correct_request_id}', function () {
        return view('admin.requests.approval');
    });
});
Route::post('/logout', function () {
    if (auth('admin')->check()) {
        auth('admin')->logout();
        return redirect('/admin/login');
    }
    auth('web')->logout();
    return redirect('/login');
})->name('logout');