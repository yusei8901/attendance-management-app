<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    // 勤怠詳細画面に表示されるデータが選択したものになっている
    public function test_attendance_detail_page_displays_correct_data()
    {
        // ユーザーを３人作成
        /** @var \App\Models\User $user */
        $users = User::factory()->count(3)->create();
        // ３人の出勤データを作成
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0, 0));
        $today = Carbon::now();
        // 出勤データを作成
        $firstUserAttendance = Attendance::factory()->for($users[0])->create([
            'work_date' => $today,
            'work_time' => 480,
        ]);
        $secondUserAttendance = Attendance::factory()->for($users[1])->create([
            'work_date' => $today,
            'start_time' => '10:00',
            'end_time' => '19:00',
            'work_time' => 480,
        ]);
        $thirdUserAttendance = Attendance::factory()->for($users[2])->create([
            'work_date' => $today,
            'start_time' => '11:00',
            'end_time' => '20:00',
            'work_time' => 480,
        ]);
        // 管理者ログイン
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');
        // 勤怠一覧画面の表示
        $response = $this->get(route('admin.attendance.list', [
            'year' => $today->year,
            'month' => $today->month,
            'day' => $today->day
        ]));
        $response->assertStatus(200);
        $response->assertSee($today->format('Y年m月d日') . 'の勤怠');
        $response->assertSee($firstUserAttendance->start_time->format('H:i'));
        $response->assertSee($firstUserAttendance->end_time->format('H:i'));
        $response->assertSee($secondUserAttendance->start_time->format('H:i'));
        $response->assertSee($secondUserAttendance->end_time->format('H:i'));
        $response->assertSee($thirdUserAttendance->start_time->format('H:i'));
        $response->assertSee($thirdUserAttendance->end_time->format('H:i'));
        // 詳細画面へ移動
        $response = $this->get(route('admin.attendance.detail', $firstUserAttendance->id));
        $response->assertStatus(200);
        $response->assertSee($users[0]->name);
        $response->assertSee($firstUserAttendance->work_date->format('Y年'));
        $response->assertSee($firstUserAttendance->work_date->format('n月j日'));
        $response->assertSee($firstUserAttendance->start_time->format('H:i'));
        $response->assertSee($firstUserAttendance->end_time->format('H:i'));
        Carbon::setTestNow(); //テスト時刻リセット
    }

    // 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_validation_fails_when_start_time_is_after_end_time() {
        $user = User::factory()->create();
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0, 0));
        $today = Carbon::now();
        // 出勤データを作成
        $attendance = Attendance::factory()->for($user)->create([
            'work_date' => $today,
        ]);
        // 管理者でログイン
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');
        // 勤怠一覧画面表示
        $response = $this->get(route('admin.attendance.list', [
            'year' => $today->year,
            'month' => $today->month,
            'day' => $today->day
        ]));
        $response->assertStatus(200);
        // 詳細画面へ移動
        $response = $this->get(route('admin.attendance.detail', $attendance->id));
        $response->assertStatus(200);
        // データの編集を行う
        $response = $this->post(route('admin.attendance.edit', $attendance->id), [
            'start_time' => '20:00',
            'end_time' => '09:00',
            'remarks' => 'テスト',
        ]);
        // バリデーションエラー
        $response->assertStatus(302);
        // バリデーションメッセージが表示される
        $response->assertSessionHasErrors('end_time');
        $this->assertEquals(
            '出勤時間もしくは退勤時間が不適切な値です',
            strip_tags(session('errors')->first('end_time'))
        );
        Carbon::setTestNow(); //テスト時刻リセット
    }

    // 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_validation_fails_when_break_start_time_is_after_end_time() {
        $user = User::factory()->create();
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0, 0));
        $today = Carbon::now();
        // 出勤データを作成
        $attendance = Attendance::factory()->for($user)->create([
            'work_date' => $today,
        ]);
        // 休憩データを作成
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00',
            'break_end' => '13:00'
        ]);
        // 管理者でログイン
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');
        // 勤怠一覧画面表示
        $response = $this->get(route('admin.attendance.list', [
            'year' => $today->year,
            'month' => $today->month,
            'day' => $today->day
        ]));
        $response->assertStatus(200);
        // 詳細画面へ移動
        $response = $this->get(route('admin.attendance.detail', $attendance->id));
        $response->assertStatus(200);
        // データの編集を行う
        $response = $this->post(route('admin.attendance.edit', $attendance->id), [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'breaks' => [
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
        $response->assertSessionHasErrors('breaks.0.break_start');
        $this->assertEquals(
            '休憩時間が不適切な値です',
            strip_tags(session('errors')->first('breaks.0.break_start'))
        );
        Carbon::setTestNow(); //テスト時刻リセット
    }

    // 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_validation_fails_when_break_end_time_is_after_end_time() {
        $user = User::factory()->create();
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0, 0));
        $today = Carbon::now();
        // 出勤データを作成
        $attendance = Attendance::factory()->for($user)->create([
            'work_date' => $today,
        ]);
        // 休憩データを作成
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00',
            'break_end' => '13:00'
        ]);
        // 管理者でログイン
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');
        // 勤怠一覧画面表示
        $response = $this->get(route('admin.attendance.list', [
            'year' => $today->year,
            'month' => $today->month,
            'day' => $today->day
        ]));
        $response->assertStatus(200);
        // 詳細画面へ移動
        $response = $this->get(route('admin.attendance.detail', $attendance->id));
        $response->assertStatus(200);
        // データの編集を行う
        $response = $this->post(route('admin.attendance.edit', $attendance->id), [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'breaks' => [
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
        $response->assertSessionHasErrors('breaks.0.break_end');
        $this->assertEquals(
            '休憩時間もしくは退勤時間が不適切な値です',
            strip_tags(session('errors')->first('breaks.0.break_end'))
        );
        Carbon::setTestNow(); //テスト時刻リセット
    }

    // 備考欄が未入力の場合のエラーメッセージが表示される
    public function test_remarks_is_required() {
        $user = User::factory()->create();
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0, 0));
        $today = Carbon::now();
        // 出勤データを作成
        $attendance = Attendance::factory()->for($user)->create([
            'work_date' => $today,
        ]);
        // 休憩データを作成
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00',
            'break_end' => '13:00'
        ]);
        // 管理者でログイン
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');
        // 勤怠一覧画面表示
        $response = $this->get(route('admin.attendance.list', [
            'year' => $today->year,
            'month' => $today->month,
            'day' => $today->day
        ]));
        $response->assertStatus(200);
        // 詳細画面へ移動
        $response = $this->get(route('admin.attendance.detail', $attendance->id));
        $response->assertStatus(200);
        // データの編集を行う
        $response = $this->post(route('admin.attendance.edit', $attendance->id), [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'breaks' => [
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

}
