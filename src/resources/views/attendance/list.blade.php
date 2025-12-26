@extends('layouts.app')

@section('title', '勤怠一覧')

@section('content')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">

<div class="request-wrapper">
    <h2 class="page-title">
        <span class="title-bar"></span>勤怠一覧
    </h2>

    {{-- 月選択ナビゲーション --}}
    <div class="month-navigation">
        <a href="{{ route('attendance.list', ['year' => $prevMonth->year, 'month' => $prevMonth->month]) }}" class="nav-button">
            ← 前月
        </a>
        <span class="current-month">{{ $year }}年{{ $month }}月</span>
        <a href="{{ route('attendance.list', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}" class="nav-button">
            翌月 →
        </a>
    </div>

    {{-- 勤怠一覧テーブル --}}
    <div class="attendance-table-container">
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩時間</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->work_date->format('m/d') }}</td>
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
                        <a href="{{ route('attendance.show', $attendance->id) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="empty-message">この月の勤怠データはありません</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection