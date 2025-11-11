{{-- 一般ユーザー用会員登録画面 --}}
@extends('layouts.user')

@section('title')
ログイン
@endsection

@section('css')
<link href="{{ asset('css/login.css') }}" rel="stylesheet" />
<link href="{{ asset('css/register.css') }}" rel="stylesheet" />
@endsection

@section('content')
    @php
        $hideSection = true;
    @endphp
    <main>
        <form class="form" action="/register" method="POST">
            @csrf
            <h2 class="title">会員登録</h2>
            <div class="input-box">
                <label for="name">名前</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}">
                <div class="error-message">
                    @error('name')
                        {{ $message }}
                    @enderror
                </div>
            </div>
            <div class="input-box">
                <label for="email">メールアドレス</label>
                <input type="text" name="email" id="email" value="{{ old('email') }}">
                <div class="error-message">
                    @error('email')
                        {{ $message }}
                    @enderror
                </div>
            </div>
            <div class="input-box">
                <label for="password">パスワード</label>
                <input type="password" name="password" id="password">
                <div class="error-message">
                    @error('password')
                        {{ $message }}
                    @enderror
                </div>
            </div>
            <div class="input-box">
                <label for="password_confirmation">パスワード確認</label>
                <input type="password" name="password_confirmation" id="password_confirmation">
                <div class="error-message">
                    @error('password_confirmation')
                        {{ $message }}
                    @enderror
                </div>
            </div>
            <button class="form-button" type="submit">登録する</button>
        </form>
        <div class="link-box">
            <a class="link" href="/login">ログインはこちら</a>
        </div>
    </main>
@endsection