<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceUserListTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    // 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
    public function test_admin_can_view_all_users_basic_information()
    {
        // ユーザーを３人作成
        /** @var \App\Models\User $user */
        $users = User::factory()->count(3)->create();
        // 管理者ログイン
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');
        // スタッフ一覧画面を開く
        $response = $this->get(route('admin.staff.list'));
        $response->assertStatus(200);
        $response->assertSee($users[0]->name);
        $response->assertSee($users[0]->email);
        $response->assertSee($users[1]->name);
        $response->assertSee($users[1]->email);
        $response->assertSee($users[2]->name);
        $response->assertSee($users[2]->email);
    }

    // ユーザーの勤怠情報が正しく表示される
    public function test_admin_can_view_selected_users_attendance_list()
    {
        $user = User::factory()->create();
        // --- 勤怠情報を作成 ---
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0));
        $today = Carbon::now();
        $attendance = Attendance::factory()->for($user)->create([
            'work_date'  => $today,
            'start_time' => Carbon::parse('09:00'),
            'end_time'   => Carbon::parse('18:00'),
            'work_time' => 540
        ]);
        // 管理者ログイン
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');
        // 選択したユーザーの勤怠一覧ページにアクセス
        $response = $this->get(
            route('admin.staff.attendance', [
                'id' => $user->id,
                'year' => $today->year,
                'month' => $today->month,
            ])
        );
        $response->assertStatus(200);
        $response->assertSee($user->name.'さんの勤怠');
        // 勤怠データの確認
        $response->assertSee($attendance->work_date->format('m/d'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        // DBに正しく保存されていること
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => Carbon::today()->toDateString(),
        ]);
    }

    // 「前月」を押下した時に表示月の前月の情報が表示される
    public function test_admin_can_navigate_to_previous_month_attendance()
    {
        $user = User::factory()->create();
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0));
        $currentMonth = Carbon::now();
        $previousMonth = $currentMonth->copy()->subMonth();
        // 前月の勤怠データの作成
        $attendancePrevious = Attendance::factory()->for($user)->create([
            'work_date'  => $previousMonth->copy()->startOfMonth(),
            'start_time' => Carbon::parse('09:00'),
            'end_time'   => Carbon::parse('18:00'),
        ]);
        // 今月の勤怠データの作成
        $attendanceCurrent = Attendance::factory()->for($user)->create([
            'work_date'  => $currentMonth->copy()->startOfMonth(),
            'start_time' => Carbon::parse('10:00'),
            'end_time'   => Carbon::parse('19:00'),
        ]);
        // 管理者ログイン
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');
        // 今月の勤怠一覧ページを開く
        $response = $this->get(route('admin.staff.attendance', [
            'id'    => $user->id,
            'year'  => $currentMonth->year,
            'month' => $currentMonth->month,
        ]));
        $response->assertStatus(200);
        // 「前月」ボタン押下
        $response = $this->get(route('admin.staff.attendance', [
            'id'    => $user->id,
            'year'  => $previousMonth->year,
            'month' => $previousMonth->month,
        ]));
        $response->assertStatus(200);
        // 前月のデータが見えることの確認
        $response->assertSee($attendancePrevious->work_date->format('m/d'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        // 今月のデータは表示されていないこと
        $response->assertDontSee($attendanceCurrent->work_date->format('m/d'));
        Carbon::setTestNow(); //テスト時刻リセット
    }

    // 「翌月」を押下した時に表示月の翌月の情報が表示される
    public function test_admin_can_navigate_to_next_month_attendance()
    {
        $user = User::factory()->create();
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0));
        $currentMonth = Carbon::now();
        $nextMonth = $currentMonth->copy()->addMonth();
        // 前月の勤怠データの作成
        $attendanceNext = Attendance::factory()->for($user)->create([
            'work_date'  => $nextMonth->copy()->startOfMonth(),
            'start_time' => Carbon::parse('09:00'),
            'end_time'   => Carbon::parse('18:00'),
        ]);
        // 今月の勤怠データの作成
        $attendanceCurrent = Attendance::factory()->for($user)->create([
            'work_date'  => $currentMonth->copy()->startOfMonth(),
            'start_time' => Carbon::parse('10:00'),
            'end_time'   => Carbon::parse('19:00'),
        ]);
        // 管理者ログイン
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');
        // 今月の勤怠一覧ページを開く
        $response = $this->get(route('admin.staff.attendance', [
            'id'    => $user->id,
            'year'  => $currentMonth->year,
            'month' => $currentMonth->month,
        ]));
        $response->assertStatus(200);
        // 「翌月」ボタン押下
        $response = $this->get(route('admin.staff.attendance', [
            'id'    => $user->id,
            'year'  => $nextMonth->year,
            'month' => $nextMonth->month,
        ]));
        $response->assertStatus(200);
        // 翌月のデータが見えることの確認
        $response->assertSee($attendanceNext->work_date->format('m/d'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        // 今月のデータは表示されていないこと
        $response->assertDontSee($attendanceCurrent->work_date->format('m/d'));
        Carbon::setTestNow(); //テスト時刻リセット
    }

    // 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function test_admin_can_view_attendance_detail_from_list()
    {
        $user = User::factory()->create();
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0));
        $attendance = Attendance::factory()->for($user)->create([
            'work_date'  => Carbon::today(),
            'start_time' => Carbon::parse('09:00'),
            'end_time'   => Carbon::parse('18:00'),
        ]);
        // 管理者ログイン
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');
        $response = $this->get(route('admin.staff.attendance', [
            'id'    => $user->id,
            'year'  => Carbon::now()->year,
            'month' => Carbon::now()->month,
        ]));
        $response->assertStatus(200);
        $response = $this->get(route('admin.attendance.detail', $attendance->id));
        $response->assertStatus(200);
        // 詳細画面に勤怠データが表示されていること
        $response->assertSee($attendance->work_date->format('Y年'));
        $response->assertSee($attendance->work_date->format('n月j日'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        Carbon::setTestNow(); //テスト時刻リセット
    }

}
