@extends('layouts.app')

@section('title', 'ログイン')

@section('content')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">

<div class="auth-container">
    <h2 class="auth-title">ログイン</h2>

    <form action="{{ route('login') }}" method="POST" novalidate>
        @csrf

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

        <button type="submit" class="btn-primary">ログインする</button>
    </form>

    <p class="auth-link">
        <a href="{{ route('register') }}">会員登録はこちら</a>
    </p>
</div>
@endsection