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
    $user = \App\Models\User::findOrFail($id);
    
    // 年月をリクエストから取得（デフォルトは今月）
    $year = $request->input('year', Carbon::now()->year);
    $month = $request->input('month', Carbon::now()->month);
    
    // 月初と月末を計算
    $startOfMonth = Carbon::create($year, $month, 1)->toDateString();
    $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
    
    // そのスタッフのその月の勤怠記録を取得
    $attendances = Attendance::where('user_id', $user->id)
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
/**
 * スタッフ別勤怠データをCSVでエクスポート
 */
public function exportCsv(Request $request, $id)
{
    $user = \App\Models\User::findOrFail($id);
    
    $year = $request->input('year', Carbon::now()->year);
    $month = $request->input('month', Carbon::now()->month);
    
    $startOfMonth = Carbon::create($year, $month, 1)->toDateString();
    $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
    
    $attendances = Attendance::with('breaks')
        ->where('user_id', $user->id)
        ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
        ->orderBy('work_date', 'asc')
        ->get();
    
    // CSVヘッダー
    $csvData = "日付,出勤時刻,退勤時刻,休憩時間,勤務時間\n";
    
    foreach ($attendances as $attendance) {
        $date = $attendance->work_date->format('Y/m/d');
        $clockIn = $attendance->clock_in ? $attendance->clock_in->format('H:i') : '';
        $clockOut = $attendance->clock_out ? $attendance->clock_out->format('H:i') : '';
        
        // 休憩時間の計算
        $totalBreak = 0;
        foreach ($attendance->breaks as $break) {
            if ($break->break_start && $break->break_end) {
                $start = Carbon::parse($break->break_start);
                $end = Carbon::parse($break->break_end);
                $totalBreak += $end->diffInMinutes($start);
            }
        }
        $breakHours = floor($totalBreak / 60);
        $breakMins = $totalBreak % 60;
        $breakTime = sprintf('%02d:%02d', $breakHours, $breakMins);
        
        // 勤務時間の計算
        $workTime = '';
        if ($attendance->clock_in && $attendance->clock_out) {
            $workMinutes = $attendance->clock_out->diffInMinutes($attendance->clock_in) - $totalBreak;
            $workHours = floor($workMinutes / 60);
            $workMins = $workMinutes % 60;
            $workTime = sprintf('%02d:%02d', $workHours, $workMins);
        }
        
        $csvData .= "{$date},{$clockIn},{$clockOut},{$breakTime},{$workTime}\n";
    }
    
    // CSVファイルとしてダウンロード
    $filename = "{$user->name}_{$year}年{$month}月_勤怠.csv";
    
    return response($csvData)
        ->header('Content-Type', 'text/csv; charset=UTF-8')
        ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
}
}