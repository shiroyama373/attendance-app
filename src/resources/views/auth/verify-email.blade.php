@extends('layouts.app')

@section('title', 'メール認証')

@section('content')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">

<div style="display: flex; justify-content: center; align-items: center; min-height: 60vh; padding-top: 5rem;">
    <div class="auth-box" style="text-align: center; max-width: 800px;">
        @if (session('status') == 'verification-link-sent')
            <div style="color: green; margin-bottom: 1.5rem; font-weight: bold;">
                認証メールを再送信しました。<br>メールをご確認ください。
            </div>
        @endif

        <p style="margin-bottom: 1rem; font-size: 1.5rem; line-height: 1.5; font-weight: bold;">
            登録していただいたメールアドレスに認証メールを送信しました。
        </p>
        <p style="margin-bottom: 2rem; font-size: 1.5rem; line-height: 1.5; font-weight: bold;">
            メール認証を完了してください。
        </p>

        <div style="margin-top: 5rem; margin-bottom: 2rem;">
            <a href="http://localhost:8025" 
               target="_blank" 
               style="display: inline-block; padding: 0.8rem 2.5rem; font-size: 1.3rem; background: #e0e0e0; color: #000; border: 1.5px solid #000; border-radius: 5px; text-decoration: none; font-weight: bold;">
                認証はこちらから
            </a>
        </div>

        <form method="POST" action="{{ route('verification.send') }}" style="margin-top: 3rem;">
            @csrf
            <button type="submit" 
                    style="background: none; border: none; color: #0066cc; cursor: pointer; text-decoration: underline; font-size: 1.3rem;">
                認証メールを再送する
            </button>
        </form>
    </div>
</div>
@endsection