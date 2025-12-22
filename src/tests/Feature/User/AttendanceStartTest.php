<?php

namespace Tests\Feature\User;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceStartTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    // 出勤ボタンが正しく機能する
    public function test_user_can_clock_in_successfully()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        $response = $this->get(route('user.attendance.attend'));
        $response->assertSee('data-testid="clock-in-btn"', false); //出勤ボタンの確認

        $response = $this->post(route('user.attendance.start'))->assertStatus(302);
        $response = $this->get(route('user.attendance.attend'));
        $response->assertSee('出勤中');
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => Carbon::today()->toDateString(),
        ]);
    }

    // 出勤は一日一回のみできる
    public function test_user_cannot_clock_in_twice_in_one_day()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        // 出勤記録作成
        $now = Carbon::create(2025, 1, 1, 9, 0, 0);
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $now->toDateString(),
            'start_time' => $now,
            'end_time' => $now->copy()->addHours(8),
        ]);
        $response = $this->get(route('user.attendance.attend'));
        $response->assertStatus(200);
        $response->assertDontSee('data-testid="clock-in-btn"');
    }

    // 出勤時刻が勤怠一覧画面で確認できる
    public function test_user_can_see_clock_in_time_on_attendance_list()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        // 出勤記録作成
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0, 0));
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'start_time' => Carbon::now(),
            'end_time' => Carbon::now()->addHours(8),
        ]);
        $response = $this->get(route('user.attendance.index'));
        $response->assertStatus(200);
        $response->assertSee($attendance->start_time->format('H:i'));
        Carbon::setTestNow(); // テスト時刻リセット
    }

}
