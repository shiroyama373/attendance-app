<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminAttendanceUpdateRequest;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * 勤怠一覧を表示（日別）
     */
    public function index(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        
        // その日の全スタッフの勤怠記録を取得
        $attendances = Attendance::with(['user', 'breaks'])
            ->whereDate('work_date', $date)
            ->get();
        
        // 前日・翌日
        $prevDate = Carbon::parse($date)->subDay()->toDateString();
        $nextDate = Carbon::parse($date)->addDay()->toDateString();
        
        return view('admin.attendance.index', compact('attendances', 'date', 'prevDate', 'nextDate'));
    }

    /**
     * 勤怠詳細を表示
     */
    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breaks'])->findOrFail($id);
        
        return view('admin.attendance.show', compact('attendance'));
    }

    /**
     * 勤怠データを更新
     */
    public function update(AdminAttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        
        // 勤怠データを更新
        $attendance->update([
            'clock_in' => $request->clock_in ? Carbon::createFromFormat('H:i', $request->clock_in) : null,
            'clock_out' => $request->clock_out ? Carbon::createFromFormat('H:i', $request->clock_out) : null,
            'note' => $request->note,
        ]);
        
        // 既存の休憩データを削除
        $attendance->breaks()->delete();
        
        // 新しい休憩データを作成
        if ($request->breaks_data) {
            foreach ($request->breaks_data as $break) {
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
        
        return redirect()->route('admin.attendance.show', $id)
            ->with('success', '勤怠データを更新しました');
    }

    /**
     * スタッフ別勤怠一覧を表示（月別）
     */
    public function showByStaff(Request $request, $id)
    {
        // TODO: 実装予定
        return view('admin.attendance.show-by-staff');
    }
}