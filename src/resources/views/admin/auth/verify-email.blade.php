@extends('layouts.app')

@section('title', 'メール認証')

@section('content')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">

<div class="auth-wrapper">
    <h2 class="auth-title">メール認証</h2>

    <div class="auth-box">
        @if (session('status') == 'verification-link-sent')
            <div style="color: green; margin-bottom: 1.5rem; text-align: center; font-weight: bold;">
                認証メールを送信しました。<br>メールをご確認ください。
            </div>
        @endif

        <p style="text-align: center; margin-bottom: 2rem; font-size: 1.1rem;">
            ご登録ありがとうございます！<br><br>
            メールアドレスの認証を完了するため、<br>
            送信されたメールに記載されたリンクをクリックしてください。
        </p>

        <p style="text-align: center; color: #666; margin-bottom: 2rem;">
            メールが届いていない場合は、下記のボタンから再送信できます。
        </p>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <div class="auth-btn-container">
                <button type="submit" class="auth-btn">認証メールを再送信</button>
            </div>
        </form>

        <div style="text-align: center; margin-top: 1.5rem;">
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" style="background: none; border: none; color: #0066cc; cursor: pointer; text-decoration: underline; font-size: 1rem;">
                    ログアウト
                </button>
            </form>
        </div>
    </div>
</div>
@endsection