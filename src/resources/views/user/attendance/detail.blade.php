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
                <form class="detail-form" action="{{ route('user.attendance.request', ['id' => $attend->id]) }}"
                    method="POST">
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
                                    @if ($attend->status === 'before_request')
                                        <input type="hidden" name="old_start_time" value="{{ $attend->start_time }}">
                                        <input type="text" name="new_start_time"
                                            value="{{ old('new_start_time', formatTimeNullable($attend->start_time)) }}">
                                    @else
                                        {{ formatTimeNullable($attend->editRequests?->first()?->new_start_time) ?? '申請中' }}
                                    @endif
                                </td>
                                <td>～</td>
                                <td>
                                    @if ($attend->status === 'before_request')
                                        <input type="hidden" name="old_end_time" value="{{ $attend->end_time }}">
                                        <input type="text" name="new_end_time"
                                            value="{{ old('new_end_time', formatTimeNullable($attend->end_time)) }}">
                                    @else
                                        {{ formatTimeNullable($attend->editRequests?->first()?->new_end_time) ?? '申請中' }}
                                    @endif
                                </td>
                                <td>
                                    <div class="error-message">
                                        @if ($errors->has('new_start_time'))
                                            {!! $errors->first('new_start_time') !!}
                                        @elseif ($errors->has('new_end_time'))
                                            {!! $errors->first('new_end_time') !!}
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @if ($attend->breaks->isEmpty())
                                <tr>
                                    <th>休憩</th>
                                    <td>
                                        @if ($attend->status === 'before_request')
                                            <input type="text" name="new_breaks[0][break_start]"
                                                value="{{ old('new_breaks.0.break_start') }}">
                                        @else
                                            --:--
                                        @endif
                                    </td>
                                    <td>〜</td>
                                    <td>
                                        @if ($attend->status === 'before_request')
                                            <input type="text" name="new_breaks[0][break_end]"
                                                value="{{ old('new_breaks.0.break_end') }}">
                                        @else
                                            --:--
                                        @endif
                                    </td>
                                    <td>
                                        <div class="error-message">
                                            @if ($errors->has('new_breaks.0.break_start'))
                                                {!! $errors->first('new_breaks.0.break_start') !!}
                                            @elseif ($errors->has('new_breaks.0.break_end'))
                                                {!! $errors->first('new_breaks.0.break_end') !!}
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
                                        @if ($attend->status === 'before_request')
                                            <input type="hidden" name="new_breaks[{{ $loop->index }}][id]"
                                                value="{{ $break->id }}">
                                            <input type="hidden" name="old_breaks[{{ $loop->index }}][id]"
                                                value="{{ $break->id }}">
                                            <input type="hidden" name="old_breaks[{{ $loop->index }}][break_start]"
                                                value="{{ $break->break_start }}">
                                            <input type="text" name="new_breaks[{{ $loop->index }}][break_start]"
                                                value="{{ old("new_breaks.$loop->index.break_start", formatTimeNullable($break->break_start)) }}">
                                        @else
                                            @php
                                                $request = $break->breakEditRequests?->first();
                                            @endphp
                                            {{ $request ? formatTimeNullable($request->new_break_start) : formatTimeNullable($break->break_start) }}
                                        @endif
                                    </td>
                                    <td>～</td>
                                    <td>
                                        @if ($attend->status === 'before_request')
                                            <input type="hidden" name="old_breaks[{{ $loop->index }}][break_end]"
                                                value="{{ $break->break_end }}">
                                            <input type="text" name="new_breaks[{{ $loop->index }}][break_end]"
                                                value="{{ old("new_breaks.$loop->index.break_end", formatTimeNullable($break->break_end)) }}">
                                        @else
                                            @php
                                                $request = $break->breakEditRequests?->first();
                                            @endphp
                                            {{ $request ? formatTimeNullable($request->new_break_end) : formatTimeNullable($break->break_end) }}
                                        @endif
                                    </td>
                                    <td>
                                        <div class="error-message">
                                            @php $i = $loop->index; @endphp
                                            @if ($errors->has("new_breaks.$i.break_start"))
                                                {!! $errors->first("new_breaks.$i.break_start") !!}
                                            @elseif ($errors->has("new_breaks.$i.break_end"))
                                                {!! $errors->first("new_breaks.$i.break_end") !!}
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            <tr>
                                <th>備考</th>
                                <td colspan="3">
                                    @if ($attend->status === 'pending')
                                        <p class="remarks">{{ $attend->editRequests?->first()?->remarks ?? '申請中' }}</p>
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
                        @if ($attend->status === 'pending')
                            <p class="info-text">*承認待ちのため修正はできません。</p>
                        @elseif($attend->status === 'approved')
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
