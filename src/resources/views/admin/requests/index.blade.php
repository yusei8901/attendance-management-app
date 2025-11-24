{{-- 申請一覧画面（管理者） --}}
@extends('layouts.admin')

@section('title')
申請一覧
@endsection

@section('css')
<link href="{{ asset('css/requests.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <main class="background-gray">
        <div class="requests-wrapper">
            <h2 class="page-title">申請一覧</h2>
            <div class="requests-tab">
                <ul class="requests-tab__inner">
                    <li data-id="pending">承認待ち</li>
                    <li data-id="approved">承認済み</li>
                </ul>
            </div>
            {{-- 承認待ち --}}
            <div class="requests-list" id="pending">
                <table>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                    @forelse($pendingAttends as $pendingAttend)
                        <tr>
                            <td>承認待ち</td>
                            <td>{{ $pendingAttend->user->name }}</td>
                            <td>{{ $pendingAttend->attendance->work_date->format('Y/m/d') }}</td>
                            <td>{{ $pendingAttend->remarks }}</td>
                            <td>{{ $pendingAttend->created_at->format('Y/m/d') }}</td>
                            <td><a href="{{ route('admin.attendance.detail', $pendingAttend->attendance_id) }}" class="detail-link">詳細</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><span class="error-message">承認待ちの申請はありません</span></td></tr>
                    @endforelse
                </table>
            </div>
            {{-- 承認済み --}}
            <div class="requests-list" id="approved">
                <table>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                    @forelse($approvedAttends as $approvedAttend)
                        <tr>
                            <td>承認済み</td>
                            <td>{{ $approvedAttend->user->name }}</td>
                            <td>{{ $approvedAttend->attendance->work_date->format('Y/m/d') }}</td>
                            <td>{{ $approvedAttend->remarks }}</td>
                            <td>{{ $approvedAttend->created_at->format('Y/m/d') }}</td>
                            <td><a href="{{ route('admin.attendance.detail', $approvedAttend->attendance_id) }}" class="detail-link">詳細</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><span class="error-message">承認済みの申請はありません</span></td></tr>
                    @endforelse
                </table>
            </div>
        </div>
    </main>
    <script>
        const tabs = document.querySelectorAll('.requests-tab__inner li');
        const lists = document.querySelectorAll('.requests-list');

        // 初期タブ
        const initial = new URLSearchParams(window.location.search).get('tab') || 'pending';
        showTab(initial);

        // タブの切り替え処理
        function showTab(id) {
            if (!document.getElementById(id)) return;
            tabs.forEach(t => t.classList.toggle('selected', t.dataset.id === id));
            lists.forEach(l => l.classList.toggle('selected', l.id === id));
        }
        // クリックイベント設定
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const id = tab.dataset.id;

                // URL更新
                const url = new URL(location);
                id === 'pending' ?
                    url.searchParams.delete('tab') :
                    url.searchParams.set('tab', id);

                history.pushState({}, '', url);
                showTab(id);
            });
        });
        // 戻る/進む
        window.addEventListener('popstate', () => {
            const id = new URLSearchParams(location.search).get('tab') || 'pending';
            showTab(id);
        });
    </script>
@endsection