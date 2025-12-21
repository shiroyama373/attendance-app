<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StampCorrectionRequestController as AdminStampCorrectionRequestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 会員登録・ログイン（Fortifyが自動処理）
// GET/POST /register - 会員登録
// GET/POST /login - ログイン
// POST /logout - ログアウト

/*
|--------------------------------------------------------------------------
| 一般ユーザー用ルート（認証必須）
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    
    // 出勤登録画面
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    
    // 勤怠一覧画面
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    
    // 勤怠詳細画面・修正申請
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::post('/attendance/detail/{id}', [AttendanceController::class, 'storeCorrectionRequest'])->name('attendance.storeCorrectionRequest');
    
});

// 申請一覧画面（一般ユーザー・管理者共通）
Route::middleware(['auth'])->group(function () {
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])->name('stamp_correction_request.index');
});

/*
|--------------------------------------------------------------------------
| 管理者用ルート（管理者認証必須）
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    
    // 勤怠一覧画面（全スタッフ）
    Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.index');
    
    // 勤怠詳細画面
    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admin.attendance.show');
    
    // スタッフ一覧画面
    Route::get('/staff/list', [StaffController::class, 'index'])->name('admin.staff.index');
    
    // スタッフ別勤怠一覧画面
    Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'showByStaff'])->name('admin.attendance.showByStaff');
});

// 修正申請承認画面（管理者専用・/adminプレフィックスなし）
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminStampCorrectionRequestController::class, 'show'])->name('admin.stamp_correction_request.show');
    Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminStampCorrectionRequestController::class, 'approve'])->name('admin.stamp_correction_request.approve');
});

// 管理者ログイン（Fortifyが自動処理）
// GET/POST /admin/login - 管理者ログイン