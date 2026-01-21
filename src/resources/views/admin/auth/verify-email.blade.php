@extends('layouts.app')

@section('title', 'メール認証')

@section('content')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">

<div class="auth-wrapper">
    <h2 class="auth-title">メール認証</h2>

    <div class="auth-box">
        @if (session('status') == 'verification-link-sent')
            <div style="color: green; margin-bottom: 1.5rem; text-align: center; font-weight: bold;">
                登録していただいたメールアドレスに<br>
                認証メールを送付しました。
            </div>
        @endif

        <p style="text-align: center; margin-bottom: 2rem;">
            メール認証を完了してください。<br>
            送付されたメールに記載された<br>
            リンクをクリックしてください。
        </p>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit">認証メールを再送付する</button>
        </form>

    </div>
</div>
@endsection