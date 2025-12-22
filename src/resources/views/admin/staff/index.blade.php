@extends('layouts.admin')

@section('title', 'スタッフ一覧 - 管理者')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin_staff_index.css') }}">

<div class="staff-container">
    <h2 class="staff-title">スタッフ一覧</h2>

    <div class="staff-table-container">
        <table class="staff-table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>月次勤怠</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <a href="{{ route('admin.attendance.showByStaff', $user->id) }}" class="staff-link">勤怠一覧</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection