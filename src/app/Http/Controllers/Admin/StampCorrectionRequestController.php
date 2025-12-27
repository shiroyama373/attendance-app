<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use Carbon\Carbon;

class StampCorrectionRequestController extends Controller
{
    /**
     * 申請詳細を表示
     */
    public function show($id)
    {
        $request = StampCorrectionRequest::with(['user', 'attendance.breaks'])
            ->findOrFail($id);
        
        return view('admin.stamp_correction_request.show', compact('request'));
    }
    /**
     * 申請を承認・却下
     */
    public function approve(Request $request, $id)
    {
        $correctionRequest = StampCorrectionRequest::findOrFail($id);
        
        // すでに処理済みの場合
        if ($correctionRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'この申請はすでに処理されています');
        }
        
        $action = $request->input('action'); // 'approve' or 'reject'
        
        if ($action === 'approve') {
            // 承認：勤怠データを更新
            $attendance = $correctionRequest->attendance;
            
            $attendance->update([
                'clock_in' => $correctionRequest->clock_in,
                'clock_out' => $correctionRequest->clock_out,
            ]);
            
            // 既存の休憩データを削除
            $attendance->breaks()->delete();
            
            // 新しい休憩データを作成
if ($correctionRequest->breaks_data) {
    foreach ($correctionRequest->breaks_data as $break) {
        // 空の休憩データはスキップ
        if (empty($break['break_start']) && empty($break['break_end'])) {
            continue;
        }
        
        $attendance->breaks()->create([
            'break_start' => $break['break_start'] ?? null,
            'break_end' => $break['break_end'] ?? null,
        ]);
    }
}
            
            // 申請のステータスを更新
            $correctionRequest->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => Carbon::now(),
            ]);
            
            return redirect()->route('admin.stamp_correction_request.show', $correctionRequest->id)
    ->with('success', '申請を承認しました');
                
        } elseif ($action === 'reject') {
            // 却下
            $correctionRequest->update([
                'status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => Carbon::now(),
            ]);
            
            return redirect()->route('stamp_correction_request.index')
                ->with('success', '申請を却下しました');
                
        } else {
            return redirect()->back()->with('error', '不正な操作です');
        }
    }
}