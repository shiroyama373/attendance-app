@extends('layouts.admin')

@section('title', '申請承認 - 管理者')

@section('content')
<link rel="stylesheet" href="{{ asset('css/attendance_show.css') }}">

<div class="attendance-wrapper">
    <h2 class="page-title">
        <span class="title-bar"></span>勤怠詳細
    </h2>

    <div class="detail-box">
        {{-- 名前 --}}
        <div class="detail-row">
            <div class="detail-label">名前</div>
            <div class="detail-value">{{ $request->user->name }}</div>
        </div>

        {{-- 日付 --}}
        <div class="detail-row">
            <div class="detail-label">日付</div>
            <div class="detail-value">
                <span>{{ $request->attendance->work_date->format('Y') }}</span>年
                <span style="margin-left: 10rem;">{{ $request->attendance->work_date->format('m月d日') }}</span>
            </div>
        </div>

        {{-- 出勤・退勤 --}}
        <div class="detail-row">
            <div class="detail-label">出勤・退勤</div>
            <div class="detail-value">
    {{ $request->clock_in ? $request->clock_in->format('H:i') : '-' }}
    <span style="margin: 0 1rem;">〜</span>
    {{ $request->clock_out ? $request->clock_out->format('H:i') : '-' }}
</div>
        </div>

        {{-- 休憩 --}}
        @php
            $breaks = $request->breaks_data ?? [];
            $maxBreaks = max(count($breaks), 2);
        @endphp

        @for($i = 0; $i < $maxBreaks; $i++)
            @php
                $break = $breaks[$i] ?? null;
            @endphp

            <div class="detail-row">
                <div class="detail-label">
                    {{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}
                </div>

                <div class="detail-value">
    {{ $break['break_start'] ?? '' }}
    @if(!empty($break['break_start']) || !empty($break['break_end']))
        <span style="margin: 0 1rem;">〜</span>
    @endif
    {{ $break['break_end'] ?? '' }}
</div>
            </div>
        @endfor

        {{-- 備考 --}}
        <div class="detail-row">
            <div class="detail-label">備考</div>
            <div class="detail-value">
    {{ $request->note }}
</div>
        </div>
    </div>

    {{-- 承認ボタン または 承認済みメッセージ --}}
<div class="btn-container">
    @if($request->status === 'pending')
        <form action="{{ route('admin.stamp_correction_request.approve', $request->id) }}" method="POST" style="display: inline;">
            @csrf
            <input type="hidden" name="action" value="approve">
            <button type="submit" class="btn-edit">承認</button>
        </form>
    @else
        <div class="approved-message">承認済み</div>
    @endif
</div>
</div>
@endsection