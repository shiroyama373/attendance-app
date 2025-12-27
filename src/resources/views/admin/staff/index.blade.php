@extends('layouts.admin')

@section('title', 'スタッフ一覧 - 管理者')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin_staff_index.css') }}">

<div class="request-wrapper">
    <h2 class="page-title">
        <span class="title-bar"></span>スタッフ一覧
    </h2>

    <div class="table-container">
        <table class="staff-table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>月次勤怠</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <a href="{{ route('admin.attendance.showByStaff', $user->id) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="empty-message">スタッフが登録されていません</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection