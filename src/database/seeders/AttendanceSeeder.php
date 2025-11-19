<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $userIds = [1, 2, 3, 4]; // 4ユーザー
        $today = Carbon::today();
        // 過去3か月
        $startDate = $today->copy()->subMonths(3)->startOfMonth();
        $endDate = $today;
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            // 平日のみ勤怠作成（月〜金）
            if ($date->isWeekend()) {
                continue;
            }
            foreach ($userIds as $userId) {
                // 出勤・退勤
                $start = Carbon::parse($date->format('Y-m-d') . ' 09:00:00');
                $end   = Carbon::parse($date->format('Y-m-d') . ' 18:00:00');

                // ---- 休憩をより自然に生成する ----

                // (1) 自然な休憩構成
                // 1回目：短い休憩（朝〜昼前）10:00〜12:00
                // 2回目：ランチ休憩（固定）12:00〜13:30 30〜60分
                // 3回目：午後休憩（軽め）15:00〜17:00
                $breakTimes = [];
                $totalBreakMinutes = 0;

                // 1回目：午前の小休憩（確率 80%）
                if (rand(1, 100) <= 80) {
                    $morningStart = Carbon::parse($date->format('Y-m-d') . ' 10:00:00')
                        ->addMinutes(rand(0, 120)); // 10:00〜12:00
                    $morningLength = rand(5, 30); // 5〜30分
                    $morningEnd = $morningStart->copy()->addMinutes($morningLength);
                    $breakTimes[] = [
                        'break_start' => $morningStart->format('H:i:s'),
                        'break_end'   => $morningEnd->format('H:i:s'),
                        'break_time'  => $morningLength,
                    ];
                    $totalBreakMinutes += $morningLength;
                }
                // 2回目：ランチ休憩（必ず発生）
                $lunchStart = Carbon::parse($date->format('Y-m-d') . ' 12:00:00')
                    ->addMinutes(rand(0, 90)); // 12:00〜13:30
                $lunchLength = rand(30, 60); // 30〜60分
                $lunchEnd = $lunchStart->copy()->addMinutes($lunchLength);
                $breakTimes[] = [
                    'break_start' => $lunchStart->format('H:i:s'),
                    'break_end'   => $lunchEnd->format('H:i:s'),
                    'break_time'  => $lunchLength,
                ];
                $totalBreakMinutes += $lunchLength;
                // 3回目：午後の軽い休憩（確率 70%）
                if (rand(1, 100) <= 70) {
                    $afternoonStart = Carbon::parse($date->format('Y-m-d') . ' 15:00:00')
                        ->addMinutes(rand(0, 120)); // 15:00〜17:00
                    $afternoonLength = rand(5, 20); // 5〜20分
                    $afternoonEnd = $afternoonStart->copy()->addMinutes($afternoonLength);
                    $breakTimes[] = [
                        'break_start' => $afternoonStart->format('H:i:s'),
                        'break_end'   => $afternoonEnd->format('H:i:s'),
                        'break_time'  => $afternoonLength,
                    ];
                    $totalBreakMinutes += $afternoonLength;
                }

                // 実働時間 = 9h - total_break
                $workMinutes = 9 * 60 - $totalBreakMinutes;

                // 勤怠作成
                $attendance = Attendance::create([
                    'user_id' => $userId,
                    'work_date' => $date->format('Y-m-d'),
                    'start_time' => $start->format('H:i:s'),
                    'end_time' => $end->format('H:i:s'),
                    'work_time' => $workMinutes,
                    'remarks' => null,
                    'status' => 'before_request'
                ]);

                // 休憩をbreaksテーブルへ登録
                foreach ($breakTimes as $bt) {
                    BreakTime::create([
                        'attendance_id' => $attendance->id,
                        'break_start' => $bt['break_start'],
                        'break_end' => $bt['break_end'],
                        'break_time' => $bt['break_time'],
                    ]);
                }
            }
        }
    }
}
