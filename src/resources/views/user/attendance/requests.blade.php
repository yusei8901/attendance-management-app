{{-- 申請一覧画面（一般ユーザー用） --}}
@extends('layouts.user')

@section('title')
    申請一覧
@endsection

@section('css')
    <link href="{{ asset('css/requests.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <main class="background-gray">
        <div class="content-wrapper">
            <h2 class="page-title">申請一覧</h2>
            <div class="requests-tab">
                <a href="?tab=pending" class="tab-link {{ $tab === 'pending' ? 'selected' : '' }}">承認待ち</a>
                <a href="?tab=approved" class="tab-link {{ $tab === 'approved' ? 'selected' : '' }}">承認済み</a>
            </div>
            {{-- 承認待ち --}}
            <div class="requests-list">
                <table>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                    @forelse($attends as $attend)
                        <tr>
                            <td>{{ $tab === 'approved' ? '承認済み' : '承認待ち' }}</td>
                            <td>{{ $attend->user->name }}</td>
                            <td>{{ $attend->attendance->work_date->format('Y/m/d') }}</td>
                            <td>{{ $attend->remarks }}</td>
                            <td>{{ $attend->created_at->format('Y/m/d') }}</td>
                            <td>
                                <a class="detail-link" href="{{ route('user.attendance.detail', $attend->attendance_id) }}">詳細</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <span class="error-message">
                                    {{ $tab === 'approved' ? '承認済みの申請はありません' : '承認待ちの申請はありません' }}
                                </span>
                            </td>
                        </tr>
                    @endforelse
                </table>
            </div>
            <div class="d-flex justify-content-center mt-4">
                {{ $attends->links() }}
            </div>
        </div>
    </main>
@endsection
