{{-- 一般ユーザー用メール認証画面 --}}
@extends('layouts.user')

@section('title')
メール認証確認
@endsection

@section('css')
<link href="{{ asset('css/verify.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <main>
        <div class="content-wrapper">
            <p>「認証を完了する」ボタンを押して<br>
            メール認証を完了してください。
            </p>
            <a class="verify-button" href="{{ $verificationUrl }}">認証を完了する</a>
        </div>
    </main>
@endsection