<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Http\Requests\AdminAttendanceUpdateRequest;

class AttendanceController extends Controller
{
    /**
     * 全スタッフの勤怠一覧を表示（日別）
     */
    public function index(Request $request)
    {
        // 日付をリクエストから取得（デフォルトは今日）
        $date = $request->input('date', Carbon::today()->toDateString());
        
        // その日の全スタッフの勤怠記録を取得
        $attendances = Attendance::where('work_date', $date)
            ->with('user')
            ->orderBy('user_id')
            ->get();
        
        // 前日・翌日の計算
        $prevDate = Carbon::parse($date)->subDay()->toDateString();
        $nextDate = Carbon::parse($date)->addDay()->toDateString();
        
        return view('admin.attendance.index', compact('attendances', 'date', 'prevDate', 'nextDate'));
    }

    /**
     * 勤怠詳細を表示・修正
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
        ]);
        
        // 既存の休憩データを削除
        $attendance->breaks()->delete();
        
        // 新しい休憩データを作成
        if ($request->breaks_data) {
            foreach ($request->breaks_data as $break) {
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
        // 対象スタッフを取得
        $user = \App\Models\User::findOrFail($id);
        
        // 年月をリクエストから取得（デフォルトは今月）
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        
        // 月初と月末を計算
        $startOfMonth = Carbon::create($year, $month, 1)->toDateString();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
        
        // そのスタッフの月別勤怠記録を取得
        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->orderBy('work_date', 'desc')
            ->get();
        
        // 前月・翌月の計算
        $prevMonth = Carbon::create($year, $month, 1)->subMonth();
        $nextMonth = Carbon::create($year, $month, 1)->addMonth();
        
        return view('admin.attendance.show_by_staff', compact(
            'user',
            'attendances',
            'year',
            'month',
            'prevMonth',
            'nextMonth'
        ));
    }
}