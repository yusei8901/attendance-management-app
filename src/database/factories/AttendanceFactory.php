<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $start = Carbon::create(2025, 12, 10, 9, 0);
        $end = (clone $start)->addHours(9);
        return [
            'user_id' => User::factory(),
            'work_date' => $start->toDateString(),
            'start_time' => $start,
            'end_time'   => $end,
            'work_time'  => $end->diffInMinutes($start),
        ];
    }
}
