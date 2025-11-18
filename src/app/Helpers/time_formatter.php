<?php

use Carbon\Carbon;


function formatTimeString($time)
{
    return substr($time, 0, 5);
}

function formatTime($minutes)
{
    // null または空、または数値でなければ '-'
    if ($minutes === null || $minutes === '' || !is_numeric($minutes)) {
        return '-';
    }
    $minutes = intval($minutes); // 数値化
    $hours = floor($minutes / 60);
    $mins = $minutes - $hours * 60;
    return $hours . ':' . sprintf('%02d', $mins);
}

if (! function_exists('formatTimeNullable')) {
    /**
     * 時刻（nullable）を HH:MM 形式にフォーマット
     */
    function formatTimeNullable($value)
    {
        if (empty($value)) {
            return '';
        }
        try {
            return Carbon::parse($value)->format('H:i');
        } catch (\Exception $e) {
            return '';
        }
    }
}