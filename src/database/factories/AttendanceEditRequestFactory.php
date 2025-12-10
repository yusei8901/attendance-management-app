<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\AttendanceEditRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceEditRequestFactory extends Factory
{
    protected $model = AttendanceEditRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // ランダムな時間を生成
        $start = Carbon::createFromTime($this->faker->numberBetween(8, 10), 0);
        $end = (clone $start)->addHours($this->faker->numberBetween(7, 9));

        return [
            'user_id'        => User::factory(),
            'attendance_id'  => Attendance::factory(),

            // 旧データ
            'old_start_time' => $start->format('H:i'),
            'old_end_time'   => $end->format('H:i'),
            'old_work_time'  => $end->diffInMinutes($start),

            // 新データ（1時間ずらす例）
            'new_start_time' => $start->copy()->addHour()->format('H:i'),
            'new_end_time'   => $end->copy()->addHour()->format('H:i'),
            'new_work_time'  => $end->copy()->addHour()->diffInMinutes($start->copy()->addHour()),

            'remarks'        => $this->faker->sentence(),
            'status'         => 'pending', // デフォルトは承認待ちにしておく
        ];
    }
    /**
     * 承認待ち
     */
    public function pending()
    {
        return $this->state(fn() => [
            'status' => 'pending',
        ]);
    }

    /**
     * 承認済み
     */
    public function approved()
    {
        return $this->state(fn() => [
            'status' => 'approved',
        ]);
    }
}
