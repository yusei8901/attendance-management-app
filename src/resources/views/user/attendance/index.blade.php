{{-- 勤怠一覧画面（一般ユーザー用） --}}
@extends('layouts.user')

@section('title')
勤怠一覧
@endsection

@section('css')
<link href="{{ asset('') }}" rel="stylesheet" />
@endsection

@section('content')
    <main class="background-gray">
        <h2>{{ $user->name }}がログイン中</h2>
    </main>
@endsection