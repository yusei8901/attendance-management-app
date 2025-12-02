<?php

namespace Tests\Feature\User;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
        $response->assertSessionHasErrors([
            'new_end_time' => '<div class="up-position">出勤時間もしくは退勤時間が<br>不適切な値です</div>'
        ]);
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

    // // 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    // public function test_error_is_displayed_when_break_end_is_after_end_time()
    // {

    // }

    // // 備考欄が未入力の場合のエラーメッセージが表示される
    // public function test_error_is_displayed_when_remarks_are_empty()
    // {

    // }

    // // 修正申請処理が実行される
    // public function test_attendance_correction_request_is_created_successfully()
    // {

    // }

    // // 「承認待ち」にログインユーザーが行った申請が全て表示されていること
    // public function test_pending_requests_list_displays_only_logged_in_users_requests()
    // {

    // }

    // // 「承認済み」に管理者が承認した修正申請が全て表示されている
    // public function test_approved_requests_are_displayed_correctly()
    // {

    // }

    // // 各申請の「詳細」を押下すると勤怠詳細画面に遷移する
    // public function test_clicking_detail_button_redirects_to_attendance_detail_page()
    // {

    // }
}
