<?php

namespace Plugins\TimeTracker\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Plugins\TimeTracker\Models\TimeTrackerConfig;
use Plugins\TimeTracker\Models\TimeTrackerActivityLog;

class TimeAndAttendanceController extends Controller
{
    public function index()
    {

        $workDayStartTime =  $this->loadConfig()['workDayStartTime'] ?? '09:00:00'; // Default to 9 AM if not set
        $workDayStartTime = Carbon::parse($workDayStartTime)->format('H:i:s');
        return view('timetracker::time_and_attendance.index', compact('workDayStartTime'));
    }

    public function timeAndAttendanceData(Request $request)
    {
        $user = getAuthenticatedUser();
        $user_ids = $request->get('user_id', null);

        $currentDate = now();
        $startDate = $request->get('start_date', $currentDate->copy()->subDays(7)->format('Y-m-d'));
        $endDate = $request->get('end_date', $currentDate->format('Y-m-d'));

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        $query = TimeTrackerActivityLog::between($startDateTime, $endDateTime)
            ->orderBy('user_id')
            ->orderBy('timestamp');

        if ($user_ids) {
            $query->where('user_id', $user_ids);
        }
        if (!isAdminOrHasAllDataAccess()) {
            $query->forUser($user->id);
        }

        $logs = $query->get();
        $grouped = [];

        foreach ($logs as $log) {
            $userId = $log->user_id;
            $date = $log->timestamp->format('Y-m-d');
            $grouped[$userId][$date][] = $log;
        }

        $attendanceData = [];

        foreach ($grouped as $userId => $dates) {
            $userModel = \App\Models\User::find($userId);
            if (!$userModel) continue;

            foreach ($dates as $date => $logs) {
                $attendanceData[] = $this->processDay($userModel, $date, $logs);
            }
        }

        usort(
            $attendanceData,
            fn($a, $b) =>
            $a['employee'] === $b['employee']
                ? strcmp($a['date'], $b['date'])
                : strcmp($a['employee'], $b['employee'])
        );

        return response()->json([
            'data' => $attendanceData,
            'summary' => $this->calculateSummary($attendanceData),
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ]);
    }

    private function processDay($user, $date, $logs)
    {
        // Sort logs by timestamp to ensure proper order
        $logs = collect($logs)->sortBy('timestamp');

        $clockIn = null;
        $clockOut = null;
        $workSessions = [];
        $currentSession = null;

        // Activity tracking
        $activityStarts = [];
        $activities = [
            'manual_time' => 0,
            'pending_manual_time' => 0, // <-- Add this
            'break_time' => 0,
            'idle_time' => 0
        ];

        foreach ($logs as $log) {
            $action = $log->action;
            $timestamp = Carbon::parse($log->timestamp);

            switch ($action) {
                case 'clock-in':
                    $clockIn = $clockIn ?? $timestamp; // Keep first clock-in
                    if ($currentSession && !isset($currentSession['end'])) {
                        $currentSession['end'] = $timestamp;
                        $workSessions[] = $currentSession;
                    }
                    $currentSession = ['start' => $timestamp];
                    break;

                case 'clock-out':
                    $clockOut = $timestamp; // Keep last clock-out
                    if ($currentSession) {
                        $currentSession['end'] = $timestamp;
                        $workSessions[] = $currentSession;
                        $currentSession = null;
                    }
                    break;

                case 'manual-start':
                    $activityStarts['manual'] = $timestamp;
                    break;

                case 'manual-stop':
                    if (isset($activityStarts['manual'])) {
                        $duration = $activityStarts['manual']->diffInSeconds($timestamp);
                        $activities['manual_time'] += $duration;
                        unset($activityStarts['manual']);
                    }
                    break;

                // NEW: Pending manual time
                case 'manual-processing-start':
                    $activityStarts['pending_manual'] = $timestamp;
                    break;

                case 'manual-processing-stop':
                    if (isset($activityStarts['pending_manual'])) {
                        $duration = $activityStarts['pending_manual']->diffInSeconds($timestamp);
                        $activities['pending_manual_time'] += $duration;
                        unset($activityStarts['pending_manual']);
                    }
                    break;

                case 'break-start':
                    $activityStarts['break'] = $timestamp;
                    break;

                case 'break-stop':
                    if (isset($activityStarts['break'])) {
                        $duration = $activityStarts['break']->diffInSeconds($timestamp);
                        $activities['break_time'] += $duration;
                        unset($activityStarts['break']);
                    }
                    break;

                case 'idle-start':
                    $activityStarts['idle'] = $timestamp;
                    break;

                case 'idle-stop':
                    if (isset($activityStarts['idle'])) {
                        $duration = $activityStarts['idle']->diffInSeconds($timestamp);
                        $activities['idle_time'] += $duration;
                        unset($activityStarts['idle']);
                    }
                    break;
            }
        }

        // Handle unclosed session
        if ($currentSession && !isset($currentSession['end'])) {
            $currentSession['end'] = $clockOut ?? now();
            $workSessions[] = $currentSession;
        }

        // Calculate total work time
        $totalWorkTime = 0;
        foreach ($workSessions as $session) {
            if (isset($session['start']) && isset($session['end'])) {
                $totalWorkTime += $session['start']->diffInSeconds($session['end']);
            }
        }

        // Calculate active time (work time minus breaks and idle)
        $activeTime = max(0, $totalWorkTime - $activities['break_time'] - $activities['idle_time'] - $activities['pending_manual_time']);

        // Productive time is manual time if available, otherwise active time
        $productiveTime = $activities['manual_time'] > 0 ? $activities['manual_time'] : $activeTime;

        // Calculate utilization
        $utilization = $totalWorkTime > 0 ? round(($productiveTime / $totalWorkTime) * 100, 1) : 0;

        return [
            'employee' => trim($user->first_name . ' ' . $user->last_name),
            'user_id' => $user->id,
            'date' => format_date($date),
            'clock_in' => $clockIn ? $clockIn->format('h:i A') : '--',
            'clock_out' => $clockOut ? $clockOut->format('h:i A') : '--',
            'work_time' => $this->formatTime($totalWorkTime),
            'active_time' => $this->formatTime($activeTime),
            'manual_time' => $this->formatTime($activities['manual_time']),
            'pending_manual_time' => $this->formatTime($activities['pending_manual_time']), // <-- Add this
            'break_time' => $this->formatTime($activities['break_time']),
            'idle_time' => $this->formatTime($activities['idle_time']),
            'utilization' => $utilization . '%',
            'status' => $clockOut ? 'Completed' : 'Active'
        ];
    }

    private function formatTime($seconds)
    {
        // Ensure we're working with positive seconds
        $seconds = max(0, $seconds);

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    private function calculateSummary($data)
    {
        $totalWork = 0;
        $totalBreak = 0;
        $totalIdle = 0;
        $totalUtil = 0;
        $countUtil = 0;
        $totalPendingManual = 0; // <-- Add this
        $users = [];

        foreach ($data as $row) {
            $users[$row['user_id']] = true;

            $totalWork += $this->timeToSeconds($row['work_time']);
            $totalBreak += $this->timeToSeconds($row['break_time']);
            $totalIdle += $this->timeToSeconds($row['idle_time']);
            $totalPendingManual += $this->timeToSeconds($row['pending_manual_time']); // <-- Add this

            $util = floatval(str_replace('%', '', $row['utilization']));
            if ($util > 0) {
                $totalUtil += $util;
                $countUtil++;
            }
        }

        $avgUtil = $countUtil ? round($totalUtil / $countUtil, 1) . '%' : '0%';

        return [
            'total_employees' => count($users),
            'total_records' => count($data),
            'total_work_hours' => $this->formatTime($totalWork),
            'total_break_time' => $this->formatTime($totalBreak),
            'total_idle_time' => $this->formatTime($totalIdle),
            'total_pending_manual_time' => $this->formatTime($totalPendingManual), // <-- Add this
            'average_utilization' => $avgUtil
        ];
    }

    private function timeToSeconds($time)
    {
        if (strpos($time, ':') === false) {
            return 0;
        }

        $parts = explode(':', $time);
        if (count($parts) !== 2) {
            return 0;
        }

        $hours = (int) $parts[0];
        $minutes = (int) $parts[1];

        return max(0, ($hours * 3600) + ($minutes * 60));
    }

    public function timeline(Request $request)
    {
        $userId = $request->get('user_id');
        $date = $request->get('date');

        $date = Carbon::createFromFormat(app('php_date_format'), $date);
        $date = format_date($date, to_format: 'Y-m-d');

        $logs = TimeTrackerActivityLog::where('user_id', $userId)
            ->whereBetween('timestamp', [
                Carbon::parse($date)->startOfDay(),
                Carbon::parse($date)->endOfDay()
            ])
            ->orderBy('timestamp')
            ->get();

        $intervals = [];
        $currentState = null;
        $currentStart = null;

        $typeMap = [
            'manual-start' => 'manual',
            'manual-stop' => 'manual',
            'manual-processing-start' => 'pending_manual', // <-- Add this
            'manual-processing-stop' => 'pending_manual',  // <-- Add this
            'break-start' => 'break',
            'break-stop' => 'break',
            'idle-start' => 'idle',
            'idle-stop' => 'idle',
            'clock-in' => 'active',
            'clock-out' => 'active',
        ];

        $activeStart = null;

        foreach ($logs as $log) {
            $action = $log->action;
            $timestamp = Carbon::parse($log->timestamp);
            $type = $typeMap[$action] ?? null;

            if (!$type) continue;

            $isStart = Str::endsWith($action, ['start', 'in']);
            $isStop = Str::endsWith($action, ['stop', 'out']);

            if ($action === 'clock-in') {
                $activeStart = $timestamp;
            } elseif ($action === 'clock-out') {
                if ($activeStart) {
                    $intervals[] = [
                        'start' => $activeStart->toDateTimeString(),
                        'end' => $timestamp->toDateTimeString(),
                        'type' => 'active'
                    ];
                    $activeStart = null;
                }
            } elseif ($isStart) {
                // If active was running, close it
                if ($activeStart) {
                    $intervals[] = [
                        'start' => $activeStart->toDateTimeString(),
                        'end' => $timestamp->toDateTimeString(),
                        'type' => 'active'
                    ];
                    $activeStart = null;
                }

                // Start the current state
                $currentState = $type;
                $currentStart = $timestamp;
            } elseif ($isStop && $type === $currentState && $currentStart) {
                $intervals[] = [
                    'start' => $currentStart->toDateTimeString(),
                    'end' => $timestamp->toDateTimeString(),
                    'type' => $currentState
                ];
                $currentState = null;
                $currentStart = null;

                // Resume active after stop if within clock-in/clock-out window
                $activeStart = $timestamp;
            }
        }


        $intervals = collect($intervals)
            ->unique(fn($item) => $item['start'] . '_' . $item['end'] . '_' . $item['type'])
            ->sortBy('start')
            ->values()
            ->toArray();

        return response()->json(['sessions' => $intervals]);
    }

    private function loadConfig()
    {
        // Default values in milliseconds
        $defaultConfig = [
            'screenshotInterval' => 60000,        // 1 minute
            'idleTimeThreshold' => 300000,        // 5 minutes
            'breakTimeThreshold' => 600000,       // 10 minutes
            'maxDailyBreakTime' => 3600000,       // 1 hour
            'manualTimeApprover' => [],
            'workDayStartTime' => '09:00',        // Default work day start
        ];
        // Get saved config from DB
        $config = TimeTrackerConfig::where('name', 'time_tracker_config')->value('value');
        // Decode JSON if available
        $decoded = is_array($config) ? $config : json_decode($config, true);
        // Merge decoded config with defaults
        $time_tracker_config = array_merge($defaultConfig, $decoded ?? []);
        // Ensure workDayStartTime is in correct format
        if (isset($time_tracker_config['workDayStartTime'])) {
            $time_tracker_config['workDayStartTime'] = Carbon::parse($time_tracker_config['workDayStartTime'])->format('H:i:s');
        } else {
            $time_tracker_config['workDayStartTime'] = '09:00:00'; // Default to 9 AM if not set
        }
        return $time_tracker_config;
    }
}
