{{-- 勤怠一覧画面（管理者用） --}}
@extends('layouts.admin')

@section('title')
    勤怠一覧
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="{{ asset('css/index.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <main class="background-gray">
        <div class="content-wrapper">
            <h2 class="page-title">{{ $current->format('Y年m月d日') }}の勤怠</h2>
            <div class="attendance-month">
                <a class="month-before"
                    href="{{ route('admin.attendance.list', ['year' => $current->copy()->subDay()->year, 'month' => $current->copy()->subDay()->month, 'day' => $current->copy()->subDay()->day]) }}">
                    前日
                </a>
                <div class="month-selector">
                    <button id="openMonthModal" class="month-display">
                        <i class="fa-solid fa-calendar-days"></i>
                        {{ $current->format('Y/m/d') }}
                    </button>
                </div>
                {{-- 月選択モーダル --}}
                <div id="monthModal" class="modal-overlay" style="display:none;">
                    <div class="modal-window">
                        <h3>表示する日付を選択</h3>
                        <input type="date" id="monthInput" value="{{ $current->format('Y-m-d') }}">
                        <div class="modal-buttons">
                            <button id="closeMonthModal" class="cancel-btn">キャンセル</button>
                            <button id="submitMonth" class="submit-btn">決定</button>
                        </div>
                    </div>
                </div>
                <a class="month-next"
                    href="{{ route('admin.attendance.list', ['year' => $current->copy()->addDay()->year, 'month' => $current->copy()->addDay()->month, 'day' => $current->copy()->addDay()->day]) }}">
                    翌日
                </a>
            </div>
            <div class="attendance-list">
                <table>
                    <tr>
                        <th>名前</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                    @foreach ($users as $user)
                        @php
                            $attend = $user->attendanceOfDate;
                            $breakMinutes = $attend->breaks->sum('break_time');
                        @endphp
                        <tr>
                            <td>{{ $user->name }}</td>
                            @if ($attend->status === 'pending')
                                <td colspan="5">
                                    <span class="info-text">修正申請中</span>
                                </td>
                            @else
                                <td>{{ formatTimeNullable($attend->start_time) }}</td>
                                <td>{{ formatTimeNullable($attend->end_time) }}</td>
                                <td>{{ formatTime($breakMinutes) }}</td>
                                <td>{{ formatTime($attend->work_time) }}</td>
                                <td>
                                    <a class="detail-link"
                                        href="{{ route('admin.attendance.detail', $attend->id) }}">
                                        詳細
                                    </a>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                    @if($users->isEmpty())
                        <tr>
                            <td colspan="6">
                                <span class="info-text">出勤記録がありません</span>
                            </td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    </main>
    <script>
        document.getElementById('openMonthModal').addEventListener('click', function() {
            document.getElementById('monthModal').style.display = 'flex';
        });
        document.getElementById('closeMonthModal').addEventListener('click', function() {
            document.getElementById('monthModal').style.display = 'none';
        });
        document.getElementById('submitMonth').addEventListener('click', function() {
            const value = document.getElementById('monthInput').value;
            if (!value) return;
            const [year, month, day] = value.split('-');
            window.location.href = `/admin/attendance/list/${year}/${month}/${day}`;
        });
    </script>
@endsection
