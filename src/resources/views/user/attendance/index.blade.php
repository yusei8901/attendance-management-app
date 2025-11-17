{{-- 勤怠一覧画面（一般ユーザー用） --}}
@extends('layouts.user')

@section('title')
    勤怠一覧
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="{{ asset('css/index.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <main class="background-gray">
        <div class="attendance-wrapper">
            <h2 class="page-title">勤怠一覧</h2>
            <div class="attendance-month">
                <a class="month-before"
                    href="{{ route('user.attendance.index', ['year' => $current->copy()->subMonth()->year, 'month' => $current->copy()->subMonth()->month]) }}">
                    前月
                </a>
                <div class="month-selector">
                    <button id="openMonthModal" class="month-display">
                        <i class="fa-solid fa-calendar-days"></i>
                        {{ $current->format('Y/m') }}
                    </button>
                </div>
                {{-- 月選択モーダル --}}
                <div id="monthModal" class="modal-overlay" style="display:none;">
                    <div class="modal-window">
                        <h3>表示する月を選択</h3>
                        <input type="month" id="monthInput" value="{{ $current->format('Y-m') }}">
                        <div class="modal-buttons">
                            <button id="closeMonthModal" class="cancel-btn">キャンセル</button>
                            <button id="submitMonth" class="submit-btn">決定</button>
                        </div>
                    </div>
                </div>
                <a class="month-next"
                    href="{{ route('user.attendance.index', ['year' => $current->copy()->addMonth()->year, 'month' => $current->copy()->addMonth()->month]) }}">
                    翌月
                </a>
            </div>
            <div class="attendance-list">
                <table>
                    <tr>
                        <th>日付</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                    @php
                        $days = [];
                        foreach (range(1, $current->daysInMonth) as $day) {
                            $days[] = $current->copy()->day($day);
                        }
                    @endphp
                    @foreach ($days as $day)
                        @php
                            $attend = $attends->firstWhere('work_date', $day->format('Y-m-d'));
                            $breakMinutes = $attend ? $attend->breaks->sum('break_time') : 0;
                        @endphp
                        <tr>
                            <td>{{ $day->format('m/d') }}({{ ['日', '月', '火', '水', '木', '金', '土'][$day->dayOfWeek] }})</td>
                            <td>
                                @if (!$attend)
                                    -
                                @elseif($attend->stamp_correction_request === 'pending')
                                    <span class="info-text">修正申請中</span>
                                @else
                                    {{ formatTimeString($attend->start_time) }}
                                @endif
                            </td>
                            <td>
                                @if (!$attend)
                                    -
                                @elseif($attend->stamp_correction_request === 'pending')
                                    <span class="info-text">修正申請中</span>
                                @else
                                    {{ formatTimeString($attend->end_time) }}
                                @endif
                            </td>
                            <td>
                                @if (!$attend)
                                    -
                                @elseif($attend->stamp_correction_request === 'pending')
                                    <span class="info-text">修正申請中</span>
                                @else
                                    {{ formatTime($breakMinutes) }}
                                @endif
                            </td>
                            <td>
                                @if (!$attend)
                                    -
                                @elseif($attend->stamp_correction_request === 'pending')
                                    <span class="info-text">修正申請中</span>
                                @else
                                    {{ formatTime($attend->work_time) }}
                                @endif
                            </td>
                            <td>
                                @if ($attend)
                                    <a class="detail-link" href="{{ route('user.attendance.detail', $attend->id) }}">
                                        詳細
                                    </a>
                                @else
                                    ー
                                @endif
                            </td>
                        </tr>
                    @endforeach
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
            const [year, month] = value.split('-');
            window.location.href = `/attendance/list/${year}/${month}`;
        });
    </script>
@endsection
