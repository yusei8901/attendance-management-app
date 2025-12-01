<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    // 勤務外の場合、勤怠ステータス「勤務外」が正しく表示される
    public function test_status_is_displayed_as_outside_when_no_attendance_exists()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        $response = $this->get(route('user.attendance.attend'));
        $response->assertSee('勤務外');
    }

    // 出勤中の場合、勤怠ステータス「出勤中」が正しく表示される
    public function test_status_is_displayed_as_working()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0, 0));
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'start_time' => Carbon::now(),
            'end_time' => null,
        ]);
        $response = $this->get(route('user.attendance.attend'));
        $response->assertSee('出勤中');
    }

    // 休憩中の場合、勤怠ステータス「休憩中」が正しく表示される
    public function test_status_is_displayed_as_breaking()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        // 出勤データ作成（退勤はまだ）
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0, 0));
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'start_time' => Carbon::now(),
            'end_time' => null,
        ]);
        // 休憩開始（休憩終了なし）
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()->addHours(3),
            'break_end' => null,
        ]);
        // 勤怠打刻画面へアクセス
        $response = $this->get(route('user.attendance.attend'));
        // 「休憩中」が表示されているか
        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    // 退勤済の場合、勤怠ステータス「退勤済」が正しく表示される
    public function test_status_is_displayed_as_finished()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        // 出勤・退勤まで完了している勤怠データ作成
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0, 0));
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'start_time' => Carbon::now(),
            'end_time' => Carbon::now()->addHours(8),
        ]);
        // 勤怠打刻画面へアクセス
        $response = $this->get(route('user.attendance.attend'));
        // 「退勤済」が表示されているか
        $response->assertStatus(200);
        $response->assertSee('退勤済');
        Carbon::setTestNow(); // テスト時刻リセット
    }
}
