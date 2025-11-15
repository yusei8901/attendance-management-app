<?php

use App\Http\Controllers\UserAttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\EmailVerificationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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
// 会員登録
Route::post('/register', [RegisteredUserController::class, 'store'])->middleware(['guest'])->name('user.register');
// メール認証関連
Route::middleware('auth:web')->group(function() {
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');
    Route::get('/email/verify/confirm', [EmailVerificationController::class, 'confirm'])
        ->name('verification.confirm');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')->name('verification.resend');
});
Route::middleware(['auth:web', 'verified'])->group(function(){
    // 勤怠登録画面
    Route::get('/attendance', [UserAttendanceController::class, 'attend'])
        ->name('user.attendance.attend');
    Route::post('/attendance/start', [UserAttendanceController::class, 'workStart'])
        ->name('user.attendance.start');
    Route::post('/attendance/end', [UserAttendanceController::class, 'workEnd'])
        ->name('user.attendance.end');
    Route::post('/attendance/break/start', [UserAttendanceController::class, 'breakStart'])
        ->name('user.break.start');
    Route::post('/attendance/break/end', [UserAttendanceController::class, 'breakEnd'])
        ->name('user.break.end');
    // 勤怠一覧画面
    Route::get('/attendance/list/{year?}/{month?}', [UserAttendanceController::class, 'index'])
        ->name('user.attendance.index');
    // 勤怠詳細画面
    Route::get('/attendance/detail/{id}', [UserAttendanceController::class, 'detail'])
        ->name('user.attendance.detail');
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
        $admin = Auth::user();
        return view('admin.attendance.index', ['admin' => $admin]);
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