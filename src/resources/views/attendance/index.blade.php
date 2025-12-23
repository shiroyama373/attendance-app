@extends('layouts.app')

@section('title', '出勤登録')


@section('content')
<link rel="stylesheet" href="{{ asset('css/attendance_index.css') }}">

<div class="clock-container">
    {{-- ステータス表示 --}}
    <div class="status-badge">
        @switch($attendance->status)
            @case('not_started')
                勤務外
                @break
            @case('clocked_in')
                出勤中
                @break
            @case('on_break')
                休憩中
                @break
            @case('clocked_out')
                退勤済
                @break
        @endswitch
    </div>

    {{-- 日時表示 --}}
    <div class="datetime-display">
        <div class="date-text">{{ now()->format('Y年m月d日') }}</div>
        <div class="time-text" id="current-time">{{ now()->format('H:i:s') }}</div>
    </div>

    {{-- 打刻ボタン --}}
    <div class="clock-buttons">
        @if($attendance->status === 'not_started')
            {{-- 出勤ボタンのみ --}}
            <form action="{{ route('attendance.store') }}" method="POST" style="grid-column: 1 / -1;">
                @csrf
                <input type="hidden" name="action" value="clock_in">
                <button type="submit" class="btn-clock">出勤</button>
            </form>
        @endif

        @if($attendance->status === 'clocked_in')
            {{-- 退勤・休憩入ボタン --}}
            <form action="{{ route('attendance.store') }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="clock_out">
                <button type="submit" class="btn-clock">退勤</button>
            </form>
            <form action="{{ route('attendance.store') }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="break_start">
                <button type="submit" class="btn-clock btn-white">休憩入</button>
            </form>
        @endif

        @if($attendance->status === 'on_break')
            {{-- 休憩戻ボタンのみ --}}
            <form action="{{ route('attendance.store') }}" method="POST" style="grid-column: 1 / -1;">
                @csrf
                <input type="hidden" name="action" value="break_end">
                <button type="submit" class="btn-clock btn-white">休憩戻</button>
            </form>
        @endif

        @if($attendance->status === 'clocked_out')
            {{-- 退勤済み：メッセージ表示 --}}
            <div style="grid-column: 1 / -1; font-size: 1.5rem; padding: 1.5rem; color: #000;  font-weight: bold;">
                お疲れ様でした
            </div>
@endif
    </div>
</div>

{{-- リアルタイム時計 --}}
<script>
function updateTime() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');

    document.getElementById('current-time').textContent = `${hours}:${minutes}`;
}
setInterval(updateTime, 1000);
updateTime(); // ← ページ読み込み直後にも表示させる
</script>
@endsection