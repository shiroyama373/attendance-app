@extends(auth()->user()->is_admin ? 'layouts.admin' : 'layouts.app')

@section('title', '申請一覧')

@section('content')
<link rel="stylesheet" href="{{ asset('css/request.css') }}">

<div class="request-wrapper">
    <h2 class="page-title">
        <span class="title-bar"></span>申請一覧
    </h2>

    {{-- タブ --}}
    <div class="tabs">
        <button class="tab active" data-tab="pending">承認待ち</button>
        <button class="tab" data-tab="approved">承認済み</button>
    </div>

    {{-- 承認待ち --}}
    <div class="tab-content active" id="pending">
        <div class="table-container">
            <table class="request-table">
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingRequests as $request)
                    <tr>
                        <td>承認待ち</td>
                        <td>{{ $request->user->name }}</td>
                        <td>{{ $request->attendance->work_date->format('Y/m/d') }}</td>
                        <td>{{ Str::limit($request->note, 20) }}</td>
                        <td>{{ $request->created_at->format('Y/m/d') }}</td>
                        <td>
                            @if(auth()->user()->is_admin)
                                <a href="{{ route('admin.stamp_correction_request.show', $request->id) }}" class="detail-link">詳細</a>
                            @else
                                <a href="{{ route('attendance.show', $request->attendance_id) }}" class="detail-link">詳細</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="empty-message">承認待ちの申請はありません</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 承認済み --}}
    <div class="tab-content" id="approved">
        <div class="table-container">
            <table class="request-table">
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($approvedRequests as $request)
                    <tr>
                        <td>{{ $request->status === 'approved' ? '承認済み' : '却下' }}</td>
                        <td>{{ $request->user->name }}</td>
                        <td>{{ $request->attendance->work_date->format('Y/m/d') }}</td>
                        <td>{{ Str::limit($request->note, 20) }}</td>
                        <td>{{ $request->created_at->format('Y/m/d') }}</td>
                        <td>
                            @if(auth()->user()->is_admin)
                                <a href="{{ route('admin.attendance.show', $request->attendance_id) }}" class="detail-link">詳細</a>
                            @else
                                <a href="{{ route('attendance.show', $request->attendance_id) }}" class="detail-link">詳細</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="empty-message">承認済みの申請はありません</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// タブ切り替え
document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', function() {
        const targetTab = this.dataset.tab;
        
        // タブのアクティブ状態を切り替え
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        // コンテンツの表示を切り替え
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        document.getElementById(targetTab).classList.add('active');
    });
});
</script>
@endsection