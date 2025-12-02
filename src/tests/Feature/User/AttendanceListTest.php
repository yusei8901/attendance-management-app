<?php

namespace Tests\Feature\User;

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
    // 自分の行った勤怠情報がすべて表示されている
    public function test_user_can_see_all_own_attendance_records()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0, 0));
        $attendances = Attendance::factory()
            ->count(5)
            ->sequence(fn($sequence) => [
                'work_date' => Carbon::today()->addDays($sequence->index),
            ])
            ->for($user)
            ->create();
        $response = $this->get(route('user.attendance.index'))->assertStatus(200);
        foreach($attendances as $attendance) {
            $response->assertSee($attendance->start_time->format('H:i'));
            $response->assertSee($attendance->end_time->format('H:i'));
        }
        Carbon::setTestNow(); //テスト時刻リセット
    }

    // 勤怠一覧画面に遷移した際に現在の月が表示される
    public function test_current_month_is_displayed_on_attendance_list()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0, 0));
        $response = $this->get(route('user.attendance.index'))->assertStatus(200);
        $response->assertSee(Carbon::now()->format('Y/m'));
        Carbon::setTestNow(); //テスト時刻リセット
    }

    // 「前月」を押下した時に表示月の前月の情報が表示される
    public function test_clicking_previous_month_displays_previous_month_data()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0, 0));
        // 前月出勤データを作成
        $beforeMonthAttendance = Attendance::factory()->for($user)->create([
            'work_date' => Carbon::now()->subMonth()->startOfMonth(),
        ]);
        // 今月の出勤データを作成
        $thisMonthAttendance = Attendance::factory()->for($user)->create([
            'work_date' => Carbon::now(),
            'start_time' => Carbon::parse('10:00'),
            'end_time' => Carbon::parse('19:00'),
        ]);
        $response = $this->get(route('user.attendance.index'))->assertStatus(200);
        $response->assertSee(Carbon::now()->format('Y/m'));
        $response->assertSee($thisMonthAttendance->start_time->format('H:i'));
        $response->assertDontSee($beforeMonthAttendance->start_time->format('H:i'));
        // 前月ボタンをクリック
        $response = $this->get(route('user.attendance.index', ['year' => Carbon::now()->copy()->subMonth()->year, 'month' => Carbon::now()->copy()->subMonth()->month]));
        $response->assertStatus(200);
        $response->assertSee(Carbon::now()->subMonth()->format('Y/m'));
        $response->assertDontSee($thisMonthAttendance->start_time->format('H:i'));
        $response->assertSee($beforeMonthAttendance->start_time->format('H:i'));
        Carbon::setTestNow(); //テスト時刻リセット
    }

    // 「翌月」を押下した時に表示月の前月の情報が表示される
    public function test_clicking_next_month_displays_next_month_data()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0, 0));
        // 翌月出勤データを作成
        $nextMonthAttendance = Attendance::factory()->for($user)->create([
            'work_date' => Carbon::now()->addMonth()->startOfMonth(),
        ]);
        // 今月の出勤データを作成
        $thisMonthAttendance = Attendance::factory()->for($user)->create([
            'work_date' => Carbon::now(),
            'start_time' => Carbon::parse('10:00'),
            'end_time' => Carbon::parse('19:00'),
        ]);
        // 勤怠一覧画面を表示
        $response = $this->get(route('user.attendance.index'))->assertStatus(200);
        $response->assertSee(Carbon::now()->format('Y/m'));
        $response->assertSee($thisMonthAttendance->start_time->format('H:i'));
        $response->assertDontSee($nextMonthAttendance->start_time->format('H:i'));
        // 翌月ボタンをクリック
        $response = $this->get(route('user.attendance.index', ['year' => Carbon::now()->copy()->addMonth()->year, 'month' => Carbon::now()->copy()->addMonth()->month]));
        $response->assertStatus(200);
        $response->assertSee(Carbon::now()->addMonth()->format('Y/m'));
        $response->assertDontSee($thisMonthAttendance->start_time->format('H:i'));
        $response->assertSee($nextMonthAttendance->start_time->format('H:i'));
        Carbon::setTestNow(); //テスト時刻リセット
    }

    // 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function test_user_can_navigate_to_attendance_detail()
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
        $response->assertSee(Carbon::now()->format('Y/m'));
        $response->assertSee($attendance->start_time->format('H:i'));
        // 詳細画面へ移動
        $response = $this->get(route('user.attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);
        Carbon::setTestNow(); //テスト時刻リセット
    }
}
