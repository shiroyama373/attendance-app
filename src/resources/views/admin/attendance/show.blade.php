@extends('layouts.admin')

@section('title', '勤怠詳細・修正 - 管理者')

@section('content')
<link rel="stylesheet" href="{{ asset('css/attendance_show.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">

<div style="max-width: 600px; margin: 0 auto;">
    <h2 class="page-title">勤怠詳細・修正</h2>

    <div class="detail-box">
        <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- スタッフ名 --}}
            <div class="detail-row">
                <div class="detail-label">名前</div>
                <div class="detail-value">{{ $attendance->user->name }}</div>
            </div>

            {{-- 日付 --}}
            <div class="detail-row">
                <div class="detail-label">日付</div>
                <div class="detail-value">{{ $attendance->work_date->format('Y年m月d日') }}</div>
            </div>

            {{-- 出勤・退勤 --}}
            <div class="detail-row">
                <div class="detail-label">出勤・退勤</div>
                <div class="detail-value">
                    <div class="time-range">
                        <input type="time" name="clock_in" 
                               value="{{ old('clock_in', $attendance->clock_in ? $attendance->clock_in->format('H:i') : '') }}" 
                               class="time-box" style="border: 1px solid #ddd;">
                        <span>〜</span>
                        <input type="time" name="clock_out" 
                               value="{{ old('clock_out', $attendance->clock_out ? $attendance->clock_out->format('H:i') : '') }}" 
                               class="time-box" style="border: 1px solid #ddd;">
                    </div>
                    @error('clock_out')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- 休憩 --}}
            <div class="detail-row">
                <div class="detail-label">休憩</div>
                <div class="detail-value">
                    @forelse($attendance->breaks as $index => $break)
                    <div class="break-item">
                        @if($index > 0)
                            <span style="color: #999; font-size: 0.875rem;">休憩{{ $index + 1 }}</span>
                        @endif
                        <div class="time-range">
                            <input type="time" name="breaks_data[{{ $index }}][break_start]" 
                                   value="{{ old('breaks_data.'.$index.'.break_start', $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '') }}" 
                                   class="time-box" style="border: 1px solid #ddd;">
                            <span>〜</span>
                            <input type="time" name="breaks_data[{{ $index }}][break_end]" 
                                   value="{{ old('breaks_data.'.$index.'.break_end', $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '') }}" 
                                   class="time-box" style="border: 1px solid #ddd;">
                        </div>
                    </div>
                    @empty
                    <div class="time-range">
                        <input type="time" name="breaks_data[0][break_start]" class="time-box" style="border: 1px solid #ddd;">
                        <span>〜</span>
                        <input type="time" name="breaks_data[0][break_end]" class="time-box" style="border: 1px solid #ddd;">
                    </div>
                    @endforelse
                    @error('breaks_data')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- 備考 --}}
            <div class="detail-row">
                <div class="detail-label">備考 <span style="color: red;">*必須</span></div>
                <div class="detail-value">
                    <textarea name="note" rows="4" class="note-box" style="width: 100%; border: 1px solid #ddd;">{{ old('note', $attendance->note) }}</textarea>
                    @error('note')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- 更新ボタン --}}
            <button type="submit" class="btn-edit" style="margin-top: 1.5rem;">更新する</button>
        </form>
    </div>

    <div style="text-align: center; margin-top: 1.5rem;">
        <a href="{{ route('admin.attendance.index') }}" style="color: #0066cc; text-decoration: none;">勤怠一覧に戻る</a>
    </div>
</div>
@endsection