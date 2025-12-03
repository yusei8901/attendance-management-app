<?php

namespace Tests\Feature\User;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\AttendanceEditRequest;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    // 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_error_is_displayed_when_start_time_is_after_end_time()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0, 0));
        // 出勤データを作成
        $attendance = Attendance::factory()->for($user)->create([
            'work_date' => Carbon::now(),
        ]);
        // 勤怠一覧画面表示
        $response = $this->get(route('user.attendance.index'))->assertStatus(200);
        // 詳細画面へ移動
        $response = $this->get(route('user.attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);
        // データの編集を行う
        $response = $this->post(route('user.attendance.request', $attendance->id), [
            'new_start_time' => '20:00',
            'new_end_time' => '09:00',
            'remarks' => 'テスト',
        ]);
        // バリデーションエラー
        $response->assertStatus(302);
        // バリデーションメッセージが表示される
        $response->assertSessionHasErrors('new_end_time');
        $this->assertEquals(
            '出勤時間もしくは退勤時間が不適切な値です',
            strip_tags(session('errors')->first('new_end_time'))
        );
        Carbon::setTestNow(); //テスト時刻リセット
    }

    // 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_error_is_displayed_when_break_start_is_after_end_time()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0, 0));
        // 出勤データを作成
        $attendance = Attendance::factory()->for($user)->create([
            'work_date' => Carbon::now(),
        ]);
        // 休憩データを作成
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00',
            'break_end' => '13:00'
        ]);
        // 勤怠一覧画面表示
        $response = $this->get(route('user.attendance.index'))->assertStatus(200);
        // 詳細画面へ移動
        $response = $this->get(route('user.attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);
        // データの編集を行う
        $response = $this->post(route('user.attendance.request', $attendance->id), [
            'new_start_time' => '09:00',
            'new_end_time' => '18:00',
            'new_breaks' => [
                [
                    'break_start' => '19:00',
                    'break_end' => '17:30'
                ]
            ],
            'remarks' => 'テスト',
        ]);
        // バリデーションエラー
        $response->assertStatus(302);
        // バリデーションメッセージが表示される
        $response->assertSessionHasErrors('new_breaks.0.break_start');
        $this->assertEquals(
            '休憩時間が不適切な値です',
            strip_tags(session('errors')->first('new_breaks.0.break_start'))
        );
        Carbon::setTestNow(); //テスト時刻リセット
    }

    // 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_error_is_displayed_when_break_end_is_after_end_time()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0, 0));
        // 出勤データを作成
        $attendance = Attendance::factory()->for($user)->create([
            'work_date' => Carbon::now(),
        ]);
        // 休憩データを作成
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00',
            'break_end' => '13:00'
        ]);
        // 勤怠一覧画面表示
        $response = $this->get(route('user.attendance.index'))->assertStatus(200);
        // 詳細画面へ移動
        $response = $this->get(route('user.attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);
        // データの編集を行う
        $response = $this->post(route('user.attendance.request', $attendance->id), [
            'new_start_time' => '09:00',
            'new_end_time' => '18:00',
            'new_breaks' => [
                [
                    'break_start' => '17:00',
                    'break_end' => '19:00'
                ]
            ],
            'remarks' => 'テスト',
        ]);
        // バリデーションエラー
        $response->assertStatus(302);
        // バリデーションメッセージが表示される
        $response->assertSessionHasErrors('new_breaks.0.break_end');
        $this->assertEquals(
            '休憩時間もしくは退勤時間が不適切な値です',
            strip_tags(session('errors')->first('new_breaks.0.break_end'))
        );
        Carbon::setTestNow(); //テスト時刻リセット
    }

    // 備考欄が未入力の場合のエラーメッセージが表示される
    public function test_error_is_displayed_when_remarks_are_empty()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0, 0));
        // 出勤データを作成
        $attendance = Attendance::factory()->for($user)->create([
            'work_date' => Carbon::now(),
        ]);
        // 休憩データを作成
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00',
            'break_end' => '13:00'
        ]);
        // 勤怠一覧画面表示
        $response = $this->get(route('user.attendance.index'))->assertStatus(200);
        // 詳細画面へ移動
        $response = $this->get(route('user.attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);
        // データの編集を行う
        $response = $this->post(route('user.attendance.request', $attendance->id), [
            'new_start_time' => '09:00',
            'new_end_time' => '18:00',
            'new_breaks' => [
                [
                    'break_start' => '12:00',
                    'break_end' => '13:00'
                ]
            ],
            'remarks' => '',
        ]);
        // バリデーションエラー
        $response->assertStatus(302);
        // バリデーションメッセージが表示される
        $response->assertSessionHasErrors('remarks');
        $this->assertEquals(
            '備考を記入してください',
            strip_tags(session('errors')->first('remarks'))
        );
        Carbon::setTestNow(); //テスト時刻リセット
    }

    // 修正申請処理が実行される
    public function test_attendance_correction_request_is_created_successfully()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0, 0));
        // 出勤データを作成
        $attendance = Attendance::factory()->for($user)->create([
            'work_date' => Carbon::now(),
            'work_time' => 480,
        ]);
        // 休憩データを作成
        $break = BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00',
            'break_end' => '13:00',
            'break_time' => 60,
        ]);
        // 勤怠一覧画面表示
        $response = $this->get(route('user.attendance.index'))->assertStatus(200);
        // 詳細画面へ移動
        $response = $this->get(route('user.attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);
        // データの編集を行う
        $response = $this->post(route('user.attendance.request', $attendance->id), [
            'old_start_time' => '09:00',
            'old_end_time' => '18:00',
            'old_breaks' => [
                [
                    'break_start' => '12:00',
                    'break_end' => '13:00'
                ]
            ],
            'new_start_time' => '10:00',
            'new_end_time' => '19:00',
            'new_breaks' => [
                [
                    'break_start' => '13:00',
                    'break_end' => '14:00'
                ]
            ],
            'remarks' => 'テスト',
        ])->assertStatus(302);
        // データベースの確認
        $this->assertDatabaseHas('attendance_edit_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'old_start_time' => '09:00:00',
            'old_end_time' => '18:00:00',
            'old_work_time' => 480,
            'new_start_time' => '10:00:00',
            'new_end_time' => '19:00:00',
            'remarks' => 'テスト',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('break_edit_requests', [
            'break_id' => $break->id,
            'attendance_id' => $attendance->id,
            'old_break_start' => '12:00:00',
            'old_break_end' => '13:00:00',
            'old_break_time' => 60,
            'new_break_start' => '13:00:00',
            'new_break_end' => '14:00:00',
            'new_break_time' => 60,
            'remarks' => 'テスト',
            'status' => 'pending',
        ]);
        $response = $this->get(route('user.attendance.detail', ['id' => $attendance->id]));
        $response->assertSee('承認待ちのため修正はできません。');
        Carbon::setTestNow(); //テスト時刻リセット
    }

    // 「承認待ち」にログインユーザーが行った申請が全て表示されていること
    public function test_only_logged_in_users_pending_requests_are_displayed()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0, 0));
        // 出勤データを５つ作成
        $attendances = Attendance::factory()
            ->count(5)
            ->sequence(fn($sequence) => [
                'work_date' => Carbon::today()->addDays($sequence->index),
                'work_time' => 480,
            ])
            ->for($user)
            ->create();
        // 12/1のデータの編集を行う
        $this->post(route('user.attendance.request', $attendances[0]->id), [
            'old_start_time' => '09:00',
            'old_end_time' => '18:00',
            'new_start_time' => '10:00',
            'new_end_time' => '19:00',
            'remarks' => 'テスト',
        ]);
        // 12/3のデータの編集を行う
        $this->post(route('user.attendance.request', $attendances[2]->id), [
            'old_start_time' => '09:00',
            'old_end_time' => '18:00',
            'new_start_time' => '10:00',
            'new_end_time' => '19:00',
            'remarks' => 'テスト',
        ]);
        $response = $this->get(route('user.requests.list'))->assertStatus(200);
        $response->assertSee($attendances[0]->work_date->format('Y/m/d'));
        $response->assertSee($attendances[2]->work_date->format('Y/m/d'));
        Carbon::setTestNow(); //テスト時刻リセット
    }

    // 「承認済み」に管理者が承認した修正申請が全て表示されている
    public function test_only_approved_requests_are_displayed_correctly()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0, 0));
        // 出勤データを作成
        $attendance = Attendance::factory()->for($user)->create([
            'work_date' => Carbon::now(),
            'work_time' => 480,
        ]);
        // データの編集を行う
        $this->post(route('user.attendance.request', $attendance->id), [
            'old_start_time' => '09:00',
            'old_end_time' => '18:00',
            'new_start_time' => '10:00',
            'new_end_time' => '19:00',
            'remarks' => 'テスト',
        ]);
        // 管理者ログイン
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');
        // 申請の承認
        $this->post(route('admin.request.approve', [$attendance->id]))->assertStatus(302);
        $approvedAttendance = $attendance->fresh();
        // ユーザーログイン
        $this->actingAs($user, 'web');
        $response = $this->get(route('user.requests.list', ['tab' => 'approved']))->assertStatus(200);
        // DBで確認
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => 'approved',
        ]);
        // UIで確認
        $response->assertSee($approvedAttendance->work_date->format('Y/m/d'));
        Carbon::setTestNow(); //テスト時刻リセット
    }

    // 各申請の「詳細」を押下すると勤怠詳細画面に遷移する
    public function test_requests_detail_link_navigates_to_attendance_detail_page()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0, 0));
        // 出勤データを作成
        $attendance = Attendance::factory()->for($user)->create([
            'work_date' => Carbon::now(),
            'work_time' => 480,
        ]);
        // データの編集を行う
        $this->post(route('user.attendance.request', $attendance->id), [
            'old_start_time' => '09:00',
            'old_end_time' => '18:00',
            'new_start_time' => '10:00',
            'new_end_time' => '19:00',
            'remarks' => 'テスト',
        ]);
        // 詳細画面へ移動
        $requestAttendance = AttendanceEditRequest::where('attendance_id', $attendance->id)->first();
        $response = $this->get(route('user.attendance.detail', $attendance->id));
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee($requestAttendance->new_start_time->format('H:i'));
        $response->assertSee($requestAttendance->new_end_time->format('H:i'));
        $response->assertSee($requestAttendance->remarks);
        Carbon::setTestNow(); //テスト時刻リセット
    }
}
