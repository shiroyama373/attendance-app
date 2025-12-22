@extends('layouts.app')

@section('title', '会員登録')

@section('content')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">

<div class="auth-container">
    <h2 class="auth-title">会員登録</h2>

    <form action="{{ route('register') }}" method="POST">
        @csrf

        <div class="form-group">
            <label class="form-label">名前</label>
            <input type="text" name="name" value="{{ old('name') }}" class="form-input">
            @error('name')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">メールアドレス</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-input">
            @error('email')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">パスワード</label>
            <input type="password" name="password" class="form-input">
            @error('password')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">パスワード確認</label>
            <input type="password" name="password_confirmation" class="form-input">
        </div>

        <button type="submit" class="btn-primary">登録する</button>
    </form>

    <p class="auth-link">
        ログインは<a href="{{ route('login') }}">こちら</a>
    </p>
</div>
@endsection