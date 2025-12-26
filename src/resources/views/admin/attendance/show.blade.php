@extends('layouts.admin')

@section('title', '勤怠詳細')

@section('content')
<link rel="stylesheet" href="{{ asset('css/attendance_show.css') }}">

<div class="attendance-wrapper">
    <h2 class="page-title">
        <span class="title-bar"></span>勤怠詳細
    </h2>

    <div class="detail-box">
       <form id="admin-update-form" action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- 名前 --}}
            <div class="detail-row">
                <div class="detail-label">名前</div>
                <div class="detail-value">{{ $attendance->user->name }}</div>
            </div>

            {{-- 日付 --}}
            <div class="detail-row">
                <div class="detail-label">日付</div>
                <div class="detail-value">
                    <span>{{ $attendance->work_date->format('Y') }}</span>年
                    <span style="margin-left: 10rem;">{{ $attendance->work_date->format('m月d日') }}</span>
                </div>
            </div>

            {{-- 出勤・退勤 --}}
            <div class="detail-row">
                <div class="detail-label">出勤・退勤</div>
                <div class="detail-value">
                    <div class="time-range">
                        <input type="text"
       name="clock_in"
       value="{{ old('clock_in', $attendance->clock_in ? $attendance->clock_in->format('H:i') : '') }}"
       class="time-box"
       placeholder="">

<span>〜</span>

<input type="text"
       name="clock_out"
       value="{{ old('clock_out', $attendance->clock_out ? $attendance->clock_out->format('H:i') : '') }}"
       class="time-box"
       placeholder="">
                    </div>
                    @error('clock_in')
    <p class="error-message">{{ $message }}</p>
@enderror
@error('clock_out')
    <p class="error-message">{{ $message }}</p>
@enderror
                </div>
            </div>

            {{-- 休憩 --}}
            @php
                $breaks = $attendance->breaks ?? collect();
                $maxBreaks = max($breaks->count(), 2);
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
                        <div class="time-range">
                            <input type="text"
       name="breaks_data[{{ $i }}][break_start]"
       value="{{ old(
           'breaks_data.'.$i.'.break_start',
           $break && $break->break_start
               ? \Carbon\Carbon::parse($break->break_start)->format('H:i')
               : ''
       ) }}"
       class="time-box">

<span>〜</span>

<input type="text"
       name="breaks_data[{{ $i }}][break_end]"
       value="{{ old(
           'breaks_data.'.$i.'.break_end',
           $break && $break->break_end
               ? \Carbon\Carbon::parse($break->break_end)->format('H:i')
               : ''
       ) }}"
       class="time-box">
                        </div>
@error('breaks_data.'.$i.'.break_start')
    <p class="error-message">{{ $message }}</p>
@enderror
@error('breaks_data.'.$i.'.break_end')
    <p class="error-message">{{ $message }}</p>
@enderror
                    </div>
                </div>
            @endfor

            {{-- 備考 --}}
            <div class="detail-row">
                <div class="detail-label">備考</div>
                <div class="detail-value">
                    <textarea name="note" class="note-box">{{ old('note', $attendance->note) }}</textarea>
                    @error('note')
            <p class="error-message">{{ $message }}</p>
        @enderror

                </div>
            </div>
        </form>
    </div>

    {{-- 修正ボタン --}}
    <div class="btn-container">
        <button type="submit" form="admin-update-form" class="btn-edit">修正</button>
    </div>
</div>
@endsection