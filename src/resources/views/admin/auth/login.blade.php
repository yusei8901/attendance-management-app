{{-- ログイン画面（管理者用） --}}
@extends('layouts.admin')

@section('title')
管理者用ログイン
@endsection

@section('css')
<link href="{{ asset('css/login.css') }}" rel="stylesheet" />
@endsection

@section('content')
    @php
        $hideSection = true;
    @endphp
    <main>
        <form class="form" action="/admin/login" method="POST">
            @csrf
            <h2 class="title">管理者ログイン</h2>
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
            <button class="form-button" type="submit">管理者ログインする</button>
        </form>
    </main>
@endsection