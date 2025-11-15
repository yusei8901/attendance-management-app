<?php

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
    $mins = $minutes;

    return $hours . ':' . sprintf('%02d', $mins);
}