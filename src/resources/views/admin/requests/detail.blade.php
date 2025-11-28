{{-- 修正申請承認画面（管理者用） --}}
@extends('layouts.admin')

@section('title')
修正申請承認画面
@endsection

@section('css')
<link href="{{ asset('css/detail.css') }}" rel="stylesheet" />
@endsection

@section('content')
    @if (session('success_message'))
        <div class="alert-success">
            {{ session('success_message') }}
        </div>
    @endif
    <main class="background-gray">
        <div class="content-wrapper">
            <h2 class="page-title">勤怠詳細</h2>
            <div class="detail-content">
                <form class="detail-form" action="{{ route('admin.request.approve', $attend->attendance_id) }}" method="POST">
                    @csrf
                    <div class="detail-list">
                        <table>
                            <tr>
                                <th>名前</th>
                                <td>{{ $attend->user->name }}</td>
                                <td colspan="3"></td>
                            </tr>
                            <tr>
                                <th>日付</th>
                                <td>{{ $attend->attendance->work_date->format('Y年') }}</td>
                                <td></td>
                                <td>{{ $attend->attendance->work_date->format('n月j日') }}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <th>出勤・退勤</th>
                                <td>{{ formatTimeNullable($attend->new_start_time) }}</td>
                                <td>～</td>
                                <td>{{ formatTimeNullable($attend->new_end_time) }}</td>
                                <td></td>
                            </tr>
                            @if ($breaks->isEmpty())
                                <tr>
                                    <th>休憩</th>
                                    <td></td>
                                    <td>なし</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            @endif
                            @foreach ($breaks as $break)
                                <tr>
                                    <th>
                                        休憩
                                        @if ($loop->iteration > 1)
                                            {{ $loop->iteration }}
                                        @endif
                                    </th>
                                    <td>{{ formatTimeNullable($break->new_break_start) }}</td>
                                    <td>～</td>
                                    <td>{{ formatTimeNullable($break->new_break_end) }}</td>
                                    <td></td>
                                </tr>
                            @endforeach
                            <tr>
                                <th>備考</th>
                                <td colspan="3">{{ $attend->remarks }}</td>
                                <td></td>
                            </tr>
                        </table>
                    </div>
                    <div class="button-wrapper">
                        @if ($attend->status === 'pending')
                            <button class="form-button">承認</button>
                        @else
                            <p class="approved">承認済み</p>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </main>

@endsection