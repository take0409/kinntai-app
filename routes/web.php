<?php

use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\AdminAuthenticatedSessionController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\UserAttendanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    if (! $request->user()) {
        return redirect('/login');
    }

    return $request->user()->is_admin
        ? redirect('/admin/attendance/list')
        : redirect('/attendance');
});

Route::get('/admin/login', [AdminAuthenticatedSessionController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AdminAuthenticatedSessionController::class, 'store']);

Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
})->middleware('auth')->name('logout');

Route::middleware(['auth', 'staff', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index']);
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
    Route::post('/attendance/break-start', [AttendanceController::class, 'startBreak'])->name('attendance.break-start');
    Route::post('/attendance/break-end', [AttendanceController::class, 'endBreak'])->name('attendance.break-end');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock-out');

    Route::get('/attendance/list', [UserAttendanceController::class, 'index']);
    Route::get('/attendance/detail/{attendance}', [UserAttendanceController::class, 'show']);
    Route::post('/attendance/detail/{attendance}', [UserAttendanceController::class, 'update']);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index']);
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index']);
    Route::get('/admin/attendance/{attendance}', [AdminAttendanceController::class, 'show']);
    Route::post('/admin/attendance/{attendance}', [AdminAttendanceController::class, 'update']);

    Route::get('/admin/staff/list', [AdminStaffController::class, 'index']);
    Route::get('/admin/attendance/staff/{user}', [AdminStaffController::class, 'show']);
    Route::get('/admin/attendance/staff/{user}/csv', [AdminStaffController::class, 'exportCsv'])->name('admin.staff.csv');

    Route::get('/stamp_correction_request/approve/{stampCorrectionRequest}', [StampCorrectionRequestController::class, 'show']);
    Route::post('/stamp_correction_request/approve/{stampCorrectionRequest}', [StampCorrectionRequestController::class, 'approve']);
});
