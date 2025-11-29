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
    // 勤務外の場合、勤怠ステータスが「勤務外」と正しく表示される
    public function test_status_is_displayed_as_outside_when_no_attendance_exists()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->get(route('user.attendance.attend'));
        $response->assertSee('勤務外');
    }

    // 出勤中の場合「出勤中」と表示される
    public function test_status_is_displayed_as_working()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'start_time' => '09:00',
            'end_time' => null,
        ]);
        $response = $this->get(route('user.attendance.attend'));
        $response->assertSee('出勤中');
    }
}
