<?php

namespace App\Http\Controllers\Admin;

use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class AttendanceCSVController extends Controller
{
    // csv出力機能
    public function export($id, $year, $month)
    {
        // 対象スタッフ
        $user = User::findOrFail($id);

        // 対象月の Carbon
        $current = Carbon::create($year, $month, 1);
        $start   = $current->copy()->startOfMonth();
        $end     = $current->copy()->endOfMonth();

        // 対象スタッフの勤怠（月単位）
        $attends = Attendance::with('breaks')
            ->where('user_id', $id)
            ->whereBetween('work_date', [$start, $end])
            ->orderBy('work_date')
            ->get();

        $fileName = "{$user->name}_{$current->format('Y_m')}.csv";

        $response = new StreamedResponse(function () use ($attends) {

            $handle = fopen('php://output', 'w');

            // Excel向け BOM
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // ヘッダー行
            fputcsv($handle, [
                '日付',
                '出勤',
                '退勤',
                '休憩時間(分)',
                '勤務時間(分)'
            ]);

            foreach ($attends as $attend) {

                $breakMinutes = $attend->breaks->sum('break_time');

                fputcsv($handle, [
                    $attend->work_date->format('Y-m-d'),
                    $attend->start_time ? $attend->start_time->format('H:i') : '',
                    $attend->end_time ? $attend->end_time->format('H:i') : '',
                    $breakMinutes,
                    $attend->work_time,
                ]);
            }

            fclose($handle);
        });

        // ダウンロード設定
        $response->headers->set("Content-Type", "text/csv");
        $response->headers->set("Content-Disposition", "attachment; filename=\"{$fileName}\"");

        return $response;
    }
}
