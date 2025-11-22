{{-- スタッフ一覧画面（管理者用） --}}
@extends('layouts.admin')

@section('title')
スタッフ一覧
@endsection

@section('css')
<link href="{{ asset('css/staff-index.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <main class="background-gray">
                <div class="attendance-wrapper">
            <h2 class="page-title">スタッフ一覧</h2>
            <div class="attendance-list">
                <table>
                    <tr>
                        <th></th>
                        <th>名前</th>
                        <th>メールアドレス</th>
                        <th>月次勤怠</th>
                        <th></th>
                    </tr>
                    @foreach($users as $user)
                        <tr>
                            <td></td>
                            <td>
                                {{ $user->name }}
                            </td>
                            <td>
                                {{ $user->email }}
                            </td>
                            <td>
                                <a class="detail-link" href="{{ route('admin.staff.attendance', $user->id) }}">詳細</a>
                            </td>
                            <td></td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </main>
@endsection