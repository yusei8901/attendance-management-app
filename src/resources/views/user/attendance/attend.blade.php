{{-- 勤怠登録画面 --}}
@extends('layouts.user')

@section('title')
    勤怠登録
@endsection

@section('css')
    <link href="{{ asset('css/attend.css') }}" rel="stylesheet" />
@endsection

@section('content')
    @if (session('verify_success'))
        <div class="alert-success">
            {{ session('verify_success') }}
        </div>
    @endif
    <main class="background-gray">
        <div class="attendance-container">
            <div class="status-label">
                @if (!$attendance)
                    勤務外
                @elseif ($attendance->end_time)
                    退勤済
                @elseif ($latestBreak && $latestBreak->break_end === null)
                    休憩中
                @else
                    出勤中
                @endif
            </div>
            <div id="current-date" class="date">読み込み中...</div>
            <div id="time" class="time">
                <span id="hh"></span><span class="colon">:</span><span id="mm"></span>
            </div>
            <div class="attendance-buttons">
                @if (!$attendance)
                    <form method="POST" action="{{ route('user.attendance.start') }}">
                        @csrf
                        <button type="submit" class="btn attend">出勤</button>
                    </form>
                @elseif ($attendance->end_time)
                    <p class="finished-text">お疲れさまでした。</p>
                @elseif ($latestBreak && $latestBreak->break_end === null)
                    <form method="POST" action="{{ route('user.attendance.break.end') }}">
                        @csrf
                        <button type="submit" class="btn break">休憩戻</button>
                    </form>
                @else
                    <div class="two-btn">
                        <form method="POST" action="{{ route('user.attendance.break.start') }}">
                            @csrf
                            <button type="submit" class="btn break">休憩入</button>
                        </form>
                        <form method="POST" action="{{ route('user.attendance.end') }}">
                            @csrf
                            <button type="submit" class="btn attend">退勤</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </main>
    <script>
        function updateClock() {
            const now = new Date();
            const year = now.getFullYear();
            const month = now.getMonth() + 1;
            const date = now.getDate();
            const dayNames = ['日', '月', '火', '水', '木', '金', '土'];
            const day = dayNames[now.getDay()];
            document.getElementById('current-date').textContent =
                `${year}年${month}月${date}日(${day})`;
            const hh = String(now.getHours()).padStart(2, '0');
            const mm = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('hh').textContent = hh;
            document.getElementById('mm').textContent = mm;
        }
        updateClock();
        setInterval(updateClock, 1000);
    </script>
@endsection
