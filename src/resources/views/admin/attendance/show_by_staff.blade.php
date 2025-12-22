@extends('layouts.admin')

@section('title', 'スタッフ別勤怠一覧 - 管理者')

@section('content')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">

<div style="max-width: 1200px; margin: 0 auto;">
    <h2 class="page-title">{{ $user->name }} さんの勤怠一覧</h2>

    {{-- 月選択ナビゲーション --}}
    <div class="month-navigation">
        <a href="{{ route('admin.attendance.showByStaff', ['id' => $user->id, 'year' => $prevMonth->year, 'month' => $prevMonth->month]) }}" class="nav-button">
            ← {{ $prevMonth->format('Y年m月') }}
        </a>
        <span class="current-month">{{ $year }}年{{ $month }}月</span>
        <a href="{{ route('admin.attendance.showByStaff', ['id' => $user->id, 'year' => $nextMonth->year, 'month' => $nextMonth->month]) }}" class="nav-button">
            {{ $nextMonth->format('Y年m月') }} →
        </a>
    </div>

    {{-- 勤怠一覧テーブル --}}
    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩時間</th>
                    <th>合計勤務時間</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->work_date->format('Y-m-d') }}</td>
                    <td>{{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '-' }}</td>
                    <td>{{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '-' }}</td>
                    <td>
                        @php
                            $totalBreak = 0;
                            foreach($attendance->breaks as $break) {
                                if($break->break_start && $break->break_end) {
                                    $start = \Carbon\Carbon::parse($break->break_start);
                                    $end = \Carbon\Carbon::parse($break->break_end);
                                    $totalBreak += $end->diffInMinutes($start);
                                }
                            }
                            $hours = floor($totalBreak / 60);
                            $minutes = $totalBreak % 60;
                        @endphp
                        {{ sprintf('%02d:%02d', $hours, $minutes) }}
                    </td>
                    <td>
                        @if($attendance->clock_in && $attendance->clock_out)
                            @php
                                $workMinutes = $attendance->clock_out->diffInMinutes($attendance->clock_in) - $totalBreak;
                                $workHours = floor($workMinutes / 60);
                                $workMins = $workMinutes % 60;
                            @endphp
                            {{ sprintf('%02d:%02d', $workHours, $workMins) }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.attendance.show', $attendance->id) }}" class="action-link">詳細・修正</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 2rem;">この月の勤怠データはありません</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- CSVダウンロードボタン --}}
        <div style="text-align: center; margin-top: 2rem;">
            <a href="{{ route('admin.attendance.exportCsv', ['id' => $user->id, 'year' => $year, 'month' => $month]) }}" 
               class="btn-edit" 
               style="display: inline-block; text-decoration: none;">
                CSVダウンロード
            </a>
        </div>
    </div>

    <div style="text-align: center; margin-top: 1.5rem;">
        <a href="{{ route('admin.attendance.index') }}" style="color: #0066cc; text-decoration: none;">全体勤怠一覧に戻る</a>
    </div>
</div>
@endsection