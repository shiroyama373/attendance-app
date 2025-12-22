@extends(auth()->user()->is_admin ? 'layouts.admin' : 'layouts.app')

@section('title', '申請一覧')

@section('content')
<link rel="stylesheet" href="{{ asset('css/request.css') }}">

<div style="max-width: 1000px; margin: 0 auto;">
    <h2 class="page-title">申請一覧</h2>

    {{-- 承認待ち --}}
    <div class="request-section">
        <h3 class="request-section-title">承認待ち</h3>
        <div class="request-list">
            @forelse($pendingRequests as $request)
            <div class="request-item">
                <div class="request-header">
                    <div>
                        @if(auth()->user()->is_admin)
                            <strong>{{ $request->user->name }}</strong> - 
                        @endif
                        <span class="request-date">{{ $request->attendance->work_date->format('Y年m月d日') }}</span>
                    </div>
                    <span class="request-status status-pending">承認待ち</span>
                </div>
                <div class="request-body">
                    <div>
                        出勤: {{ $request->clock_in ? $request->clock_in->format('H:i') : '-' }} / 
                        退勤: {{ $request->clock_out ? $request->clock_out->format('H:i') : '-' }}
                    </div>
                    @if($request->note)
                        <div class="request-note">備考: {{ Str::limit($request->note, 50) }}</div>
                    @endif
                </div>
                @if(auth()->user()->is_admin)
                    <div style="margin-top: 1rem;">
                        <a href="{{ route('admin.stamp_correction_request.show', $request->id) }}" 
                           style="color: #0066cc; text-decoration: none;">詳細を見る</a>
                    </div>
                @endif
            </div>
            @empty
            <div class="empty-message">承認待ちの申請はありません</div>
            @endforelse
        </div>
    </div>

    {{-- 処理済み --}}
    <div class="request-section">
        <h3 class="request-section-title">処理済み</h3>
        <div class="request-list">
            @forelse($processedRequests as $request)
            <div class="request-item">
                <div class="request-header">
                    <div>
                        @if(auth()->user()->is_admin)
                            <strong>{{ $request->user->name }}</strong> - 
                        @endif
                        <span class="request-date">{{ $request->attendance->work_date->format('Y年m月d日') }}</span>
                    </div>
                    <span class="request-status {{ $request->status === 'approved' ? 'status-approved' : 'status-rejected' }}">
                        {{ $request->status === 'approved' ? '承認済み' : '却下' }}
                    </span>
                </div>
                <div class="request-body">
                    <div>
                        出勤: {{ $request->clock_in ? $request->clock_in->format('H:i') : '-' }} / 
                        退勤: {{ $request->clock_out ? $request->clock_out->format('H:i') : '-' }}
                    </div>
                    @if($request->note)
                        <div class="request-note">備考: {{ Str::limit($request->note, 50) }}</div>
                    @endif
                    @if($request->approver)
                        <div style="color: #999; font-size: 0.875rem; margin-top: 0.5rem;">
                            承認者: {{ $request->approver->name }} ({{ $request->approved_at->format('Y/m/d H:i') }})
                        </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="empty-message">処理済みの申請はありません</div>
            @endforelse
        </div>
    </div>
</div>
@endsection