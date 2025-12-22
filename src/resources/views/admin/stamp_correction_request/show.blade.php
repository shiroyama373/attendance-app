@extends('layouts.admin')

@section('title', '申請承認 - 管理者')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin_stamp_request_show.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">

<div class="approval-container">
    <h2 class="page-title">修正申請の承認</h2>

    <div class="detail-box">
        {{-- 申請者情報 --}}
        <div class="detail-row">
            <div class="detail-label">申請者</div>
            <div class="detail-value">{{ $request->user->name }}</div>
        </div>

        {{-- 日付 --}}
        <div class="detail-row">
            <div class="detail-label">対象日</div>
            <div class="detail-value">{{ $request->attendance->work_date->format('Y年m月d日') }}</div>
        </div>

        {{-- 現在の勤怠データ --}}
        <div class="detail-row current-data-row">
            <div class="detail-label">【現在のデータ】</div>
            <div class="detail-value">
                <div>出勤: {{ $request->attendance->clock_in ? $request->attendance->clock_in->format('H:i') : '-' }} / 
                     退勤: {{ $request->attendance->clock_out ? $request->attendance->clock_out->format('H:i') : '-' }}</div>
                <div class="detail-sub-info">
                    休憩: 
                    @forelse($request->attendance->breaks as $break)
                        {{ \Carbon\Carbon::parse($break->break_start)->format('H:i') }} 〜 
                        {{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '-' }}
                        @if(!$loop->last), @endif
                    @empty
                        なし
                    @endforelse
                </div>
            </div>
        </div>

        {{-- 申請内容 --}}
        <div class="detail-row requested-data-row">
            <div class="detail-label">【申請内容】</div>
            <div class="detail-value">
                <div>出勤: {{ $request->clock_in ? $request->clock_in->format('H:i') : '-' }} / 
                     退勤: {{ $request->clock_out ? $request->clock_out->format('H:i') : '-' }}</div>
                <div class="detail-sub-info">
                    休憩: 
                    @if($request->breaks_data)
                        @foreach($request->breaks_data as $break)
                            {{ $break['break_start'] ?? '-' }} 〜 {{ $break['break_end'] ?? '-' }}
                            @if(!$loop->last), @endif
                        @endforeach
                    @else
                        なし
                    @endif
                </div>
            </div>
        </div>

        {{-- 備考 --}}
        <div class="detail-row">
            <div class="detail-label">備考</div>
            <div class="note-box">{{ $request->note }}</div>
        </div>

        {{-- ステータス --}}
        <div class="detail-row">
            <div class="detail-label">ステータス</div>
            <div class="detail-value">
                @if($request->status === 'pending')
                    <span class="status-pending">承認待ち</span>
                @elseif($request->status === 'approved')
                    <span class="status-approved">承認済み</span>
                @else
                    <span class="status-rejected">却下</span>
                @endif
            </div>
        </div>

        {{-- 承認情報（処理済みの場合） --}}
        @if($request->status !== 'pending')
        <div class="detail-row">
            <div class="detail-label">承認者</div>
            <div class="detail-value">{{ $request->approver->name ?? '-' }}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">承認日時</div>
            <div class="detail-value">{{ $request->approved_at ? $request->approved_at->format('Y年m月d日 H:i') : '-' }}</div>
        </div>
        @endif

        {{-- 承認・却下ボタン（承認待ちの場合のみ） --}}
        @if($request->status === 'pending')
        <div class="btn-group approval-buttons">
            <form action="{{ route('admin.stamp_correction_request.approve', $request->id) }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="approve">
                <button type="submit" class="btn-approve">承認</button>
            </form>
            <form action="{{ route('admin.stamp_correction_request.approve', $request->id) }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="reject">
                <button type="submit" class="btn-reject">却下</button>
            </form>
        </div>
        @endif
    </div>

    <div class="back-link-container">
        <a href="{{ route('stamp_correction_request.index') }}" style="color: #0066cc; text-decoration: none;">申請一覧に戻る</a>
    </div>
</div>
@endsection