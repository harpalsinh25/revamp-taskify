<?php

/**
 * Verification script for Midnight Crossing
 */

function calculateDayMetricsSimulation($logs, $referenceNowStr) {
    $metrics = [
        'work_time' => 0,
        'active_time' => 0,
        'idle_time' => 0,
    ];

    $activeSessionStart = null;
    $idleStartTime = null;

    $referenceNow = new DateTime($referenceNowStr);

    usort($logs, function($a, $b) {
        return strtotime($a['timestamp']) - strtotime($b['timestamp']);
    });
    
    foreach ($logs as $log) {
        $timestamp = new DateTime($log['timestamp']);
        
        switch ($log['action']) {
            case 'clock-in':
                if (!$activeSessionStart) $activeSessionStart = $timestamp;
                break;
            case 'clock-out':
                if ($activeSessionStart) {
                    $diff = $timestamp->getTimestamp() - $activeSessionStart->getTimestamp();
                    $metrics['work_time'] += floor($diff / 60);
                    $activeSessionStart = null;
                }
                break;
        }
    }

    // PULSE LOGIC (Simplified)
    $lastLog = end($logs);
    $lastActivityTimestamp = $lastLog ? new DateTime($lastLog['timestamp']) : null;
    $sessionEndTime = $lastActivityTimestamp ?? $referenceNow;
    
    if ($lastActivityTimestamp && $referenceNow->format('Y-m-d') === $lastActivityTimestamp->format('Y-m-d')) {
        $diffSec = $referenceNow->getTimestamp() - $lastActivityTimestamp->getTimestamp();
        if ($diffSec < 300) $sessionEndTime = $referenceNow;
    }

    if ($activeSessionStart && $sessionEndTime >= $activeSessionStart) {
        $diff = $sessionEndTime->getTimestamp() - $activeSessionStart->getTimestamp();
        $metrics['work_time'] += floor($diff / 60);
    }

    $metrics['active_time'] = $metrics['work_time'] - $metrics['idle_time'];
    return $metrics;
}

// SIMULATE MIDNIGHT CROSSING
echo "Simulating Midnight Crossing...\n";

// Day 1: 10 PM to 12 AM
$logsDay1 = [
    ['timestamp' => '2026-03-26 22:00:00', 'action' => 'clock-in'],
    ['timestamp' => '2026-03-26 23:59:00', 'action' => 'pulse'],
];
$resDay1 = calculateDayMetricsSimulation($logsDay1, '2026-03-27 12:00:00'); // Viewing from next day
echo "Day 1 (22:00 - 00:00): Expected 120 mins, Got " . $resDay1['work_time'] . " mins\n";

// Day 2: 12 AM to 02 AM
$logsDay2 = [
    ['timestamp' => '2026-03-27 00:01:00', 'action' => 'pulse'],
    ['timestamp' => '2026-03-27 01:59:00', 'action' => 'clock-out'],
];
$resDay2 = calculateDayMetricsSimulation($logsDay2, '2026-03-27 12:00:00');
echo "Day 2 (00:00 - 02:00): Expected 120 mins, Got " . $resDay2['work_time'] . " mins\n";

if ($resDay2['work_time'] == 0) {
    echo "CRITICAL: Midnight crossing bug confirmed. Day 2 work is 0 because first log is not clock-in.\n";
} else {
    echo "Midnight crossing seems handled (unlikely with current code).\n";
}
