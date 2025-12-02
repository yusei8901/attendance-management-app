<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'work_date' => Carbon::today(),
            'start_time' => Carbon::parse('09:00'),
            'end_time' => Carbon::parse('18:00'),
        ];
    }
}
