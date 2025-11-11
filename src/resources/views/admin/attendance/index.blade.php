{{-- 勤怠一覧画面（管理者用） --}}
@extends('layouts.admin')

@section('title')
勤怠一覧
@endsection

@section('css')
<link href="{{ asset('') }}" rel="stylesheet" />
@endsection

@section('content')
    <main class="background-gray">
        <h2>{{ $admin->name }}がログイン中</h2>
    </main>
@endsection