{{-- 勤怠詳細画面（一般ユーザー用） --}}
@extends('layouts.user')

@section('title')
勤怠詳細
@endsection

@section('css')
<link href="{{ asset('css/detail.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <main class="background-gray">
        <div class="detail-wrapper">
            <h2 class="page-title">勤怠詳細</h2>
            <div class="detail-content">
                <div class="detail-list">
                    <table>
                        <tr>
                            <th>名前</th>
                            <td>一般ユーザー１</td>
                            <td colspan="3"></td>
                        </tr>
                        <tr>
                            <th>日付</th>
                            <td>2023年</td>
                            <td></td>
                            <td>6月1日</td>
                            <td></td>
                        </tr>
                        <tr>
                            <th>出勤・退勤</th>
                            <td><input type="text" name="start_time" value="09:00"></td>
                            <td>～</td>
                            <td><input type="text" name="end_time" value="18:00"></td>
                            <td></td>
                        </tr>
                        <tr>
                            <th>休憩</th>
                            <td><input type="text" name="break_start" value="12:00"></td>
                            <td>～</td>
                            <td><input type="text" name="break_end" value="13:00"></td>
                            <td></td>
                        </tr>
                        <tr>
                            <th>休憩２</th>
                            <td><input type="text" name="break_start" value="15:00"></td>
                            <td>～</td>
                            <td><input type="text" name="break_end" value="16:00"></td>
                            <td></td>
                        </tr>
                        <tr>
                            <th>備考</th>
                            <td colspan="3"><textarea name="" id="" cols="30" rows="10"></textarea></td>
                            <td></td>
                        </tr>
                    </table>
                </div>
                <form class="detail-form" action="" method="POST">
                    @csrf
                    <button class="form-button">修正</button>
                </form>
            </div>
        </div>
    </main>
@endsection