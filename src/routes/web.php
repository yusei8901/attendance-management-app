<?php

use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AdminRequestsController;
use App\Http\Controllers\AttendanceCSVController;
use App\Http\Controllers\UserAttendanceController;
use App\Http\Controllers\UserRequestsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\EmailVerificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::post('/register', [RegisteredUserController::class, 'store'])->middleware(['guest'])->name('user.register');
Route::prefix('email')->name('verification.')->middleware('auth:web')->group(function() {
    Route::get('/verify', [EmailVerificationController::class, 'notice'])->name('notice');
    Route::get('/verify/confirm', [EmailVerificationController::class, 'confirm'])->name('confirm');
    Route::get('/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware('signed')->name('verify');
    Route::post('/verification-notification', [EmailVerificationController::class, 'resend'])->middleware('throttle:6,1')->name('resend');
});
Route::prefix('attendance')->name('user.attendance.')->middleware(['auth:web', 'verified'])->group(function(){
    Route::get('/', [UserAttendanceController::class, 'attend'])->name('attend');
    Route::post('/start', [UserAttendanceController::class, 'workStart'])->name('start');
    Route::post('/end', [UserAttendanceController::class, 'workEnd'])->name('end');
    Route::post('/break/start', [UserAttendanceController::class, 'breakStart'])->name('break.start');
    Route::post('/break/end', [UserAttendanceController::class, 'breakEnd'])->name('break.end');
    Route::get('/list/{year?}/{month?}', [UserAttendanceController::class, 'index'])->name('index');
    Route::get('/detail/{id}', [UserAttendanceController::class, 'detail'])->name('detail');
    Route::post('/detail/{id}', [UserAttendanceController::class, 'request'])->name('request');
});
Route::prefix('stamp_correction_request')->name('user.')->middleware(['auth:web', 'verified'])->group(function () {
    Route::get('/list', [UserRequestsController::class, 'index'])->name('requests.list');
});
Route::prefix('admin')->group(function() {
    Route::get('/login', [LoginController::class, 'create'])->name('admin.login');
    Route::post('/login', [LoginController::class, 'store']);
});
Route::prefix('admin')->name('admin.')->middleware('auth:admin')->group(function(){
    Route::get('/attendance/list/{year?}/{month?}/{day?}', [AdminAttendanceController::class, 'index'])->name('attendance.list');
    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'detail'])->name('attendance.detail');
    Route::post('/attendance/{id}', [AdminAttendanceController::class, 'edit'])->name('attendance.edit');
    Route::get('/staff/list', [AdminStaffController::class, 'index'])->name('staff.list');
    Route::get('/attendance/staff/{id}/{year?}/{month?}', [AdminStaffController::class, 'attend'])->name('staff.attendance');
    Route::get('/attendance/staff/{id}/{year}/{month}/csv', [AttendanceCSVController::class, 'export'])->name('attendance.csv');
    Route::get('/stamp_correction_request/list', [AdminRequestsController::class, 'index'])->name('request.list');
    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminRequestsController::class, 'detail'])->name('request.detail');
    Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminRequestsController::class, 'update'])->name('request.approve');
});
Route::post('/logout', function () {
    if (auth('admin')->check()) {
        auth('admin')->logout();
        return redirect('/admin/login');
    }
    auth('web')->logout();
    return redirect('/login');
})->name('logout');