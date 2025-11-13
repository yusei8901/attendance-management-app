{{-- 一般ユーザー用メール認証画面 --}}
@extends('layouts.user')

@section('title')
メール認証
@endsection

@section('css')
<link href="{{ asset('css/verify.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <main>
        @if(session('resend_message'))
            <div class="alert-success">
                {{ session('resend_message') }}
            </div>
        @endif
        <div class="content-wrapper">
            <p>登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
            </p>
            <a class="verify-button" href="{{ route('verification.confirm') }}">認証はこちらから</a>
            <form action="{{ route('verification.resend') }}" method="POST">
                @csrf
                <button class="verify-email-again" type="submit">認証メールを再送する</button>
            </form>
        </div>
    </main>
@endsection