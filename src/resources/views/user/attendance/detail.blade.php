{{-- 勤怠詳細画面（一般ユーザー用） --}}
@extends('layouts.user')

@section('title')
    勤怠詳細
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
        <div class="detail-wrapper">
            <h2 class="page-title">勤怠詳細</h2>
            <div class="detail-content">
                <form class="detail-form" action="{{ route('user.attendance.edit', ['id' => $attend->id]) }}" method="POST">
                    @csrf
                    <div class="detail-list">
                        <table>
                            <tr>
                                <th>名前</th>
                                <td>{{ $user->name }}</td>
                                <td colspan="3"></td>
                            </tr>
                            <tr>
                                <th>日付</th>
                                <td>{{ \Carbon\Carbon::parse($attend->work_date)->format('Y年') }}</td>
                                <td></td>
                                <td>{{ \Carbon\Carbon::parse($attend->work_date)->format('n月j日') }}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <th>出勤・退勤</th>
                                <td>
                                    @if ($attend->stamp_correction_request === 'before_request')
                                        <input type="text" name="start_time"
                                            value="{{ old('start_time', formatTimeNullable($attend->start_time)) }}">
                                    @else
                                        {{ $attend->start_time ? \Carbon\Carbon::parse($attend->start_time)->format('H:i') : '' }}
                                    @endif

                                </td>
                                <td>～</td>
                                <td>
                                    @if ($attend->stamp_correction_request === 'before_request')
                                        <input type="text" name="end_time"
                                            value="{{ old('end_time', formatTimeNullable($attend->end_time)) }}">
                                    @else
                                        {{ $attend->end_time ? \Carbon\Carbon::parse($attend->end_time)->format('H:i') : '' }}
                                    @endif
                                </td>
                                <td>
                                    <div class="error-message">
                                        @if ($errors->has('start_time'))
                                            {!! $errors->first('start_time') !!}
                                        @elseif ($errors->has('end_time'))
                                            {!! $errors->first('end_time') !!}
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @if ($attend->breaks->isEmpty())
                                <tr>
                                    <th>休憩</th>
                                    <td>
                                        @if ($attend->stamp_correction_request === 'before_request')
                                            <input type="text" name="breaks[0][break_start]" value="{{ old('breaks.0.break_start') }}">
                                        @else
                                            --:--
                                        @endif
                                    </td>
                                    <td>～</td>
                                    <td>
                                        @if ($attend->stamp_correction_request === 'before_request')
                                            <input type="text" name="breaks[0][break_end]" value="{{ old('breaks.0.break_end') }}">
                                        @else
                                            --:--
                                        @endif
                                    </td>
                                    <td>
                                        <div class="error-message">
                                            @if ($errors->has('breaks.0.break_start'))
                                                {!! $errors->first('breaks.0.break_start') !!}
                                            @elseif ($errors->has('breaks.0.break_end'))
                                                {!! $errors->first('breaks.0.break_end') !!}
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endif

                            @foreach ($attend->breaks as $break)
                                <tr>
                                    <th>
                                        休憩
                                        @if ($loop->iteration > 1)
                                            {{ $loop->iteration }}
                                        @endif
                                    </th>
                                    <td>
                                        @if ($attend->stamp_correction_request === 'before_request')
                                            <input type="hidden" name="breaks[{{ $loop->index }}][id]"
                                                value="{{ $break->id }}">
                                            <input type="text" name="breaks[{{ $loop->index }}][break_start]"
                                                value="{{ old("breaks.$loop->index.break_start", formatTimeNullable($break->break_start)) }}">
                                        @else
                                            {{ $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '' }}
                                        @endif
                                    </td>
                                    <td>～</td>
                                    <td>
                                        @if ($attend->stamp_correction_request === 'before_request')
                                            <input type="text" name="breaks[{{ $loop->index }}][break_end]"
                                                value="{{ old("breaks.$loop->index.break_end", formatTimeNullable($break->break_end)) }}">
                                        @else
                                            {{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '' }}
                                        @endif
                                    </td>
                                    <td>
                                        <div class="error-message">
                                            @php $i = $loop->index; @endphp
                                            @if ($errors->has("breaks.$i.break_start"))
                                                {!! $errors->first("breaks.$i.break_start") !!}
                                            @elseif ($errors->has("breaks.$i.break_end"))
                                                {!! $errors->first("breaks.$i.break_end") !!}
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            <tr>
                                <th>備考</th>
                                <td colspan="3">
                                    @if ($attend->stamp_correction_request === 'pending')
                                        <p class="remarks">{{ $attend->remarks }}</p>
                                    @else
                                        <textarea name="remarks" cols="30" rows="10">{{ old('remarks', $attend->remarks) }}</textarea>
                                    @endif
                                </td>
                                <td>
                                    <div class="error-message">
                                        @error('remarks')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="button-wrapper">
                        @if ($attend->stamp_correction_request === 'pending')
                            <p class="info-text">*承認待ちのため修正はできません。</p>
                        @elseif($attend->stamp_correction_request === 'approved')
                            <p class="info-text">*打刻修正申請が承認されたため修正できません。</p>
                        @else
                            <button class="form-button">修正</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </main>
@endsection
