@extends('layouts.admin')

@section('title', '勤怠一覧 - 管理者')

@section('content')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">

<div class="request-wrapper">
    <h2 class="page-title">
        <span class="title-bar"></span>{{ \Carbon\Carbon::parse($date)->format('Y年m月d日') }}の勤怠
    </h2>

    {{-- 日付選択ナビゲーション --}}
    <div class="month-navigation">
        <a href="{{ route('admin.attendance.index', ['date' => $prevDate]) }}" class="nav-button">
            ← 前日
        </a>
        <span class="current-month">{{ \Carbon\Carbon::parse($date)->format('Y年m月d日') }}</span>
        <a href="{{ route('admin.attendance.index', ['date' => $nextDate]) }}" class="nav-button">
            翌日 →
        </a>
    </div>

    {{-- 勤怠一覧テーブル --}}
    <div class="attendance-table-container">
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>名前</th>
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
                    <td>{{ $attendance->user->name }}</td>
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
                        <a href="{{ route('admin.attendance.show', $attendance->id) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="empty-message">この日の勤怠データはありません</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection