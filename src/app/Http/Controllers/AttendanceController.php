<?php

namespace App\Http\Controllers;

use App\Http\Requests\StampCorrectionStoreRequest;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * 出勤登録画面を表示
     */
    public function index()
{
    $attendance = Attendance::firstOrCreate(
        [
            'user_id' => auth()->id(),
            'work_date' => now()->toDateString(),
        ],
        ['status' => 'not_started']
    );

    // 退勤済みの場合、セッションにフラグを設定
    if ($attendance->status === 'clocked_out') {
        session(['is_clocked_out' => true]);
    } else {
        session()->forget('is_clocked_out');
    }

    return view('attendance.index', compact('attendance'));
}

    /**
     * 打刻処理（出勤・休憩入・休憩戻・退勤）
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today()->toDateString();
        $action = $request->input('action'); // 'clock_in', 'break_start', 'break_end', 'clock_out'
        
        // 今日の勤怠記録を取得または新規作成
        $attendance = Attendance::firstOrCreate(
            [
                'user_id' => $user->id,
                'work_date' => $today,
            ],
            [
                'status' => 'not_started',
            ]
        );
        
        switch ($action) {
            case 'clock_in': // 出勤
                if ($attendance->clock_in) {
                    return redirect()->back()->with('error', 'すでに出勤済みです');
                }
                $attendance->clock_in = Carbon::now();
                $attendance->status = 'clocked_in';
                break;
                
            case 'break_start': // 休憩入
                if ($attendance->status !== 'clocked_in') {
                    return redirect()->back()->with('error', '出勤中ではありません');
                }
                $attendance->breaks()->create([
                    'break_start' => Carbon::now()->format('H:i:s'),
                ]);
                $attendance->status = 'on_break';
                break;
                
            case 'break_end': // 休憩戻
                if ($attendance->status !== 'on_break') {
                    return redirect()->back()->with('error', '休憩中ではありません');
                }
                $latestBreak = $attendance->breaks()->whereNull('break_end')->latest()->first();
                if ($latestBreak) {
                    $latestBreak->break_end = Carbon::now()->format('H:i:s');
                    $latestBreak->save();
                }
                $attendance->status = 'clocked_in';
                break;
                
            case 'clock_out': // 退勤
                if ($attendance->status !== 'clocked_in') {
                    return redirect()->back()->with('error', '出勤中ではありません');
                }
                if ($attendance->clock_out) {
                    return redirect()->back()->with('error', 'すでに退勤済みです');
                }
                $attendance->clock_out = Carbon::now();
                $attendance->status = 'clocked_out';
                break;

            default:
                return redirect()->back()->with('error', '不正な操作です');
        }
        
        $attendance->save();
        
        return redirect()->route('attendance.index')->with('success', '打刻が完了しました');
    }

    /**
     * 勤怠一覧を表示（月別）
     */
    public function list(Request $request)
    {
        $user = auth()->user();
        
        // 年月をリクエストから取得（デフォルトは今月）
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        
        // 月初と月末を計算
        $startOfMonth = Carbon::create($year, $month, 1)->toDateString();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
        
        // その月の勤怠記録を取得
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->orderBy('work_date', 'desc')
            ->get();
        
        // 前月・翌月の計算
        $prevMonth = Carbon::create($year, $month, 1)->subMonth();
        $nextMonth = Carbon::create($year, $month, 1)->addMonth();
        
        return view('attendance.list', compact(
            'attendances',
            'year',
            'month',
            'prevMonth',
            'nextMonth'
        ));
    }

    /**
     * 勤怠詳細を表示
     */
    public function show($id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);
        
        // 自分の勤怠記録かチェック
        if ($attendance->user_id !== auth()->id()) {
            abort(403, '権限がありません');
        }
        
        return view('attendance.show', compact('attendance'));
    }

    /**
     * 修正申請を作成
     */
    public function storeCorrectionRequest(StampCorrectionStoreRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        
        // 自分の勤怠記録かチェック
        if ($attendance->user_id !== auth()->id()) {
            abort(403, '権限がありません');
        }
        
        // 修正申請を作成
        $attendance->stampCorrectionRequests()->create([
            'user_id' => auth()->id(),
            'clock_in' => $request->clock_in ? Carbon::createFromFormat('H:i', $request->clock_in) : null,
            'clock_out' => $request->clock_out ? Carbon::createFromFormat('H:i', $request->clock_out) : null,
            'breaks_data' => $request->breaks_data,
            'note' => $request->note,
            'status' => 'pending',
        ]);
        
        return redirect()->route('stamp_correction_request.index')
            ->with('success', '修正申請を送信しました');
    }
}