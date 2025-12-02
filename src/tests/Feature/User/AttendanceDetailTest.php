<?php

namespace Tests\Feature\User;

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
    // 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
    public function test_user_name_is_displayed_correctly_on_attendance_detail_page()
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
        $response->assertSee($user->name);
        Carbon::setTestNow(); //テスト時刻リセット
    }

    // 勤怠詳細画面の「日付」が選択した日付になっている
    public function test_work_date_is_displayed_correctly_on_attendance_detail_page()
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
        $response->assertSee($attendance->work_date->format('n月j日'));
        Carbon::setTestNow(); //テスト時刻リセット
    }

    // 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
    public function test_clock_in_and_clock_out_time_match_attendance_record()
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
        $response->assertSee($attendance->start_time->format('H:i'));
        $response->assertSee($attendance->end_time->format('H:i'));
        Carbon::setTestNow(); //テスト時刻リセット
    }

    // 「休憩」にて記されている時間がログインユーザーの打刻と一致している
    public function test_all_break_times_are_displayed_correctly_on_attendance_detail_page()
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
        $firstBreak = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()->addHours(3),
            'break_end' => Carbon::now()->addHours(4),
        ]);
        $secondBreak = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()->addHours(5),
            'break_end' => Carbon::now()->addHours(6),
        ]);
        // 勤怠一覧画面表示
        $response = $this->get(route('user.attendance.index'))->assertStatus(200);
        // 詳細画面へ移動
        $response = $this->get(route('user.attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);
        $response->assertSee($firstBreak->break_start->format('H:i'));
        $response->assertSee($firstBreak->break_end->format('H:i'));
        $response->assertSee($secondBreak->break_start->format('H:i'));
        $response->assertSee($secondBreak->break_end->format('H:i'));
        Carbon::setTestNow(); //テスト時刻リセット
    }
}
