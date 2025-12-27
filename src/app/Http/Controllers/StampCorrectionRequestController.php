<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;

class StampCorrectionRequestController extends Controller
{
    /**
     * 申請一覧を表示（一般ユーザー・管理者共通）
     */

    public function index()
{
    if (auth()->user()->is_admin) {
    // 管理者：全スタッフの申請を表示
    $pendingRequests = \App\Models\StampCorrectionRequest::with(['user', 'attendance'])
        ->where('status', 'pending')
        ->orderBy('created_at', 'desc')
        ->get();
    
    $approvedRequests = \App\Models\StampCorrectionRequest::with(['user', 'attendance'])
        ->where('status', 'approved')
        ->orderBy('created_at', 'desc')
        ->get();
} else {
    // 一般ユーザー：自分の申請のみ表示
$pendingRequests = \App\Models\StampCorrectionRequest::with(['user', 'attendance'])  // ← user を追加
    ->where('user_id', auth()->id())
    ->where('status', 'pending')
    ->orderBy('created_at', 'desc')
    ->get();
    
    $approvedRequests = \App\Models\StampCorrectionRequest::with(['user', 'attendance'])  // ← user を追加
        ->where('user_id', auth()->id())
        ->where('status', 'approved')
        ->orderBy('created_at', 'desc')
        ->get();
}

// 管理者も一般ユーザーも同じビューを使用
return view('stamp_correction_request.index', compact('pendingRequests', 'approvedRequests'));
}
}