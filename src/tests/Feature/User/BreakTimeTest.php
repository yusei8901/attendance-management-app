<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;

class BreakTimeTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    // 休憩ボタンが正しく機能する
    public function test_user_can_start_break_successfully()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        // 出勤記録作成
        Carbon::setTestNow(Carbon::create(2025, 12, 1, 9, 0, 0));
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'start_time' => now(),
            'end_time' => null,
        ]);
        $response = $this->get(route('user.attendance.attend'));
        $response->assertSee('休憩入');

        $response = $this->post(route('user.attendance.break.start'));
        $response->assertStatus(302);

        $response = $this->get(route('user.attendance.attend'));
        $response->assertSee('休憩戻');
    }

    // 休憩は一日に何回でもできる
    public function test_break_can_be_taken_many_times_per_day()
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
            'end_time' => null,
        ]);
        // 1回目の休憩開始
        $this->post(route('user.attendance.break.start'))->assertStatus(302);
        // 時間を進める
        Carbon::setTestNow(now()->addMinutes(30));
        // 1回目の休憩戻
        $this->post(route('user.attendance.break.end'))->assertStatus(302);
        // 時間を進める
        Carbon::setTestNow(now()->addMinutes(10));
        // 2回目の休憩開始
        $this->post(route('user.attendance.break.start'))->assertStatus(302);
        // DB確認
        $this->assertEquals(2, BreakTime::where('attendance_id', $attendance->id)->count());
        Carbon::setTestNow(); // テスト時刻リセット
    }

    // 休憩戻ボタンが正しく機能する
    public function test_user_can_end_break_successfully()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        // 出勤記録作成
        Carbon::setTestNow($start = Carbon::create(2025, 12, 1, 9, 0, 0));
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'start_time' => now(),
            'end_time' => null,
        ]);
        // 休憩開始時刻を「固定」
        $breakStart = $start->copy()->addHours(3); // 12:00
        BreakTime::create([ 'attendance_id' => $attendance->id, 'break_start' => $breakStart, 'break_end' => null, ]);
        // 現在時刻を「休憩終了時刻」に変更
        Carbon::setTestNow($breakEnd = $start->copy()->addHours(4)); // 13:00
        // 休憩終了
        $this->post(route('user.attendance.break.end'))->assertStatus(302);
        // DB検証
        $this->assertDatabaseHas('breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => $breakStart->format('H:i:s'),
            'break_end' => $breakEnd->format('H:i:s'),
        ]);
    }

    // 休憩戻は一日に何回でもできる
    public function test_break_return_can_be_done_many_times()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        // 出勤記録作成
        $baseTime = Carbon::create(2025, 12, 1, 9, 0, 0);
        Carbon::setTestNow($baseTime);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'start_time' => now(),
            'end_time' => null,
        ]);
        // 1回目の休憩
        $this->post(route('user.attendance.break.start'))->assertStatus(302);
        Carbon::setTestNow($baseTime->copy()->addMinutes(10));
        $this->post(route('user.attendance.break.end'))->assertStatus(302);

        // 時間を進める
        Carbon::setTestNow($baseTime->copy()->addMinutes(20));

        // 2回目の休憩開始
        $this->post(route('user.attendance.break.start'))->assertStatus(302);
        Carbon::setTestNow($baseTime->copy()->addMinutes(30));
        $this->post(route('user.attendance.break.end'))->assertStatus(302);

        // DB確認
        $this->assertEquals(2, BreakTime::where('attendance_id', $attendance->id)->count());
        Carbon::setTestNow(); // テスト時刻リセット
    }

    // 休憩時刻が勤怠一覧画面で確認できる
    public function test_break_time_is_displayed_on_attendance_list()
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
        // 休憩記録作成
        $break = BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => now()->addHours(3)->format('H:i'),
            'break_end' => now()->addHours(4)->format('H:i'),
            'break_time' => 60,
        ]);
        // 休憩時間の表示形式の変換
        $minutes = $break->break_time;
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;
        $expected = sprintf('%d:%02d', $hours, $mins);
        $response = $this->get(route('user.attendance.index'));
        $response->assertStatus(200);
        $response->assertSee($expected);
        Carbon::setTestNow(); // テスト時刻リセット
    }
}
