<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    // その日になされた全ユーザーの勤怠情報が正確に確認できる
    public function test_admin_can_view_all_users_attendance_for_today()
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
        Carbon::setTestNow(); //テスト時刻リセット
    }

    // 遷移した際に現在の日付が表示される
    public function test_today_date_is_displayed_on_admin_attendance_page()
    {
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0, 0));
        $today = Carbon::now();
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
        Carbon::setTestNow(); //テスト時刻リセット
    }

    // 「前日」を押下した時に前の日の勤怠情報が表示される
    public function test_clicking_previous_day_button_displays_previous_day_attendance()
    {
        // ユーザーを３人作成
        /** @var \App\Models\User $user */
        $users = User::factory()->count(3)->create();
        // ３人の出勤データを作成
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0, 0));
        $today = Carbon::now();
        $yesterday = Carbon::now()->copy()->subDay();
        // 出勤データを作成
        $firstUserAttendance = Attendance::factory()->for($users[0])->create([
            'work_date' => $yesterday,
            'work_time' => 480,
        ]);
        $secondUserAttendance = Attendance::factory()->for($users[1])->create([
            'work_date' => $yesterday,
            'start_time' => '10:00',
            'end_time' => '19:00',
            'work_time' => 480,
        ]);
        $thirdUserAttendance = Attendance::factory()->for($users[2])->create([
            'work_date' => $yesterday,
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
        // 前日ボタンを押下
        $response = $this->get(route('admin.attendance.list', [
            'year' => $yesterday->year,
            'month' => $yesterday->month,
            'day' => $yesterday->day
        ]));
        $response->assertStatus(200);
        $response->assertSee($yesterday->format('Y年m月d日') . 'の勤怠');
        $response->assertSee($firstUserAttendance->start_time->format('H:i'));
        $response->assertSee($firstUserAttendance->end_time->format('H:i'));
        $response->assertSee($secondUserAttendance->start_time->format('H:i'));
        $response->assertSee($secondUserAttendance->end_time->format('H:i'));
        $response->assertSee($thirdUserAttendance->start_time->format('H:i'));
        $response->assertSee($thirdUserAttendance->end_time->format('H:i'));
        Carbon::setTestNow(); //テスト時刻リセット
    }

    // 「翌日」を押下した時に次の日の勤怠情報が表示される
    public function test_clicking_next_day_button_displays_next_day_attendance()
    {
        // ユーザーを３人作成
        /** @var \App\Models\User $user */
        $users = User::factory()->count(3)->create();
        // ３人の出勤データを作成
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0, 0));
        $today = Carbon::now();
        $tomorrow = Carbon::now()->copy()->addDay();
        // 出勤データを作成
        $firstUserAttendance = Attendance::factory()->for($users[0])->create([
            'work_date' => $tomorrow,
            'work_time' => 480,
        ]);
        $secondUserAttendance = Attendance::factory()->for($users[1])->create([
            'work_date' => $tomorrow,
            'start_time' => '10:00',
            'end_time' => '19:00',
            'work_time' => 480,
        ]);
        $thirdUserAttendance = Attendance::factory()->for($users[2])->create([
            'work_date' => $tomorrow,
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
        // 翌日ボタンを押下
        $response = $this->get(route('admin.attendance.list', [
            'year' => $tomorrow->year,
            'month' => $tomorrow->month,
            'day' => $tomorrow->day
        ]));
        $response->assertStatus(200);
        $response->assertSee($tomorrow->format('Y年m月d日') . 'の勤怠');
        $response->assertSee($firstUserAttendance->start_time->format('H:i'));
        $response->assertSee($firstUserAttendance->end_time->format('H:i'));
        $response->assertSee($secondUserAttendance->start_time->format('H:i'));
        $response->assertSee($secondUserAttendance->end_time->format('H:i'));
        $response->assertSee($thirdUserAttendance->start_time->format('H:i'));
        $response->assertSee($thirdUserAttendance->end_time->format('H:i'));
        Carbon::setTestNow(); //テスト時刻リセット
    }
}
