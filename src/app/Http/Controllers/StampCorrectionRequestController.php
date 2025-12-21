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
        // 管理者：全ユーザーの申請を表示
        $pendingRequests = StampCorrectionRequest::where('status', 'pending')
            ->with(['user', 'attendance'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        $processedRequests = StampCorrectionRequest::whereIn('status', ['approved', 'rejected'])  // ← 修正
            ->with(['user', 'attendance', 'approver'])
            ->orderBy('approved_at', 'desc')
            ->get();
    } else {
        // 一般ユーザー：自分の申請のみ表示
        $pendingRequests = StampCorrectionRequest::where('user_id', auth()->id())
            ->where('status', 'pending')
            ->with('attendance')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $processedRequests = StampCorrectionRequest::where('user_id', auth()->id())
            ->whereIn('status', ['approved', 'rejected'])
            ->with(['attendance', 'approver'])
            ->orderBy('approved_at', 'desc')
            ->get();
    }
    
    return view('stamp_correction_request.index', compact('pendingRequests', 'processedRequests'));
}
}