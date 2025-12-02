<?php

namespace Tests\Feature\User;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceEndTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    // 退勤ボタンが正しく機能する
    public function test_user_can_clock_out_successfully()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0, 0));
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'start_time' => now(),
            'end_time' => null,
        ]);
        // 退勤ボタンの確認
        $response = $this->get(route('user.attendance.attend'));
        $response->assertSee('退勤');
        // 退勤処理後の退勤済ステータスの確認
        $response = $this->post(route('user.attendance.end'))->assertStatus(302);
        $response = $this->get(route('user.attendance.attend'));
        $response->assertSee('退勤済');
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => Carbon::today()->toDateString(),
            'end_time' => Carbon::now()->toTimeString(),
        ]);
        $this->assertDatabaseMissing('attendances', [
            'user_id' => $user->id,
            'end_time' => null,
        ]);
        Carbon::setTestNow(); // テスト時刻リセット
    }

    // 退勤時刻が勤怠一覧画面で確認できる
    public function test_user_can_see_clock_out_time_on_attendance_list()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        // 出勤記録作成
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0, 0));
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $response = $this->get(route('user.attendance.index'));
        $response->assertStatus(200);
        $response->assertSee($attendance->end_time->format('H:i'));
        Carbon::setTestNow(); // テスト時刻リセット
    }
}
