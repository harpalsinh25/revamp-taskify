<?php

namespace Plugins\TimeTracker\Controllers;
use Exception;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Plugins\TimeTracker\Models\TimeTrack;
use Plugins\TimeTracker\Models\Screenshot;
use Plugins\TimeTracker\Models\TimeTrackerConfig;
use Plugins\TimeTracker\Models\TimeTrackerActivityLog;
class TimeTrackerController extends Controller
{
    /**
     * Log Update - Records employee activity logs.
     *
     * Records a start or end work activity for a specific employee. This can be used to start or stop a work session.
     *
     * @authenticated
     *
     * @group Time Tracking
     *
     * @bodyParam user_id integer required The ID of the employee. Example: 1
     * @bodyParam action string required The action performed. Must be "start_work" or "end_work". Example: start_work
     * @bodyParam timestamp string required The timestamp of the action. Must be a valid datetime. Example: 2025-06-17 10:00:00
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Log updated successfully.",
     *   "data": {
     *     "employeeId": 1,
     *     "action": "start_work",
     *     "timestamp": "2025-06-17 10:00:00"
     *   }
     * }
     *
     * @response 422 {
     *   "error": true,
     *   "message": "The given data was invalid.",
     *   "data": {
     *     "user_id": ["The user_id field is required."]
     *   }
     * }
     */
    public function logUpdate(Request $request)
    {
        $isApi = $request->get('isApi', false);
        if (!($request->has('user_id'))) {
            $request->merge(['user_id' => getAuthenticatedUser()->id]);
        }
        // dd($request, getAuthenticatedUser());
        try {
            $data = $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'action' => 'required|string|in:clock-in,clock-out,idle-start,idle-stop,break-start,break-stop,manual-start,manual-stop',
                'timestamp' => 'required|date',
            ]);
            // Store the activity log entry
            $activityLog = TimeTrackerActivityLog::create([
                'user_id' => $data['user_id'],
                'action' => $data['action'],
                'timestamp' => $data['timestamp'],
            ]);
            return formatApiResponse(
                false,
                'Log updated successfully.',
                [
                    'data' => [
                        'employeeId' => $data['user_id'],
                        'user_id' => $data['user_id'],
                        'action' => $data['action'],
                        'timestamp' => $data['timestamp']
                    ]
                ]
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (Exception $e) {
            Log::error('Log update failed: ' . $e->getMessage());
            return formatApiResponse(true, 'Failed to update log' .
                ' - ' . $e->getMessage(), [
                'data' => []
            ]);
        }
    }
    /**
     * Upload Screenshot - Saves employee screenshot.
     *
     * Uploads a screenshot file taken during tracking and stores it for the authenticated user.
     *
     * @authenticated
     *
     * @group Time Tracking
     *
     * @bodyParam screenshot file required The screenshot image file. Must be jpg, jpeg, or png.
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Screenshot uploaded successfully.",
     *   "data": {
     *     "filename": "1687001200_desktop.png",
     *     "path": "/storage/screenshots/1687001200_desktop.png"
     *   }
     * }
     *
     * @response 400 {
     *   "error": true,
     *   "message": "No file uploaded.",
     *   "data": {}
     * }
     *
     * @response 422 {
     *   "error": true,
     *   "message": "The given data was invalid.",
     *   "data": {
     *     "screenshot": ["The screenshot must be an image."]
     *   }
     * }
     *
     */
    public function uploadScreenshot(Request $request)
    {
        $isApi = $request->get('isApi', false);

        try {
            if (!$request->hasFile('screenshot')) {
                return response()->json([
                    'error' => true,
                    'message' => 'No file uploaded.',
                    'data' => []
                ], 400);
            }

            $data = $request->validate([
                'screenshot' => 'required|image|mimes:png,jpg,jpeg|max:5120', // limit to 5MB
                'metadata' => 'nullable|array',
                'captured_at' => 'nullable|date', // allow custom captured_at
            ]);

            $file = $request->file('screenshot');

            // Use a structured filename for easy management
            $filename = now()->format('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            $path = $file->storeAs('screenshots', $filename, 'public');

            if (!$path) {
                return response()->json([
                    'error' => true,
                    'message' => 'Failed to store the screenshot.',
                    'data' => []
                ], 500);
            }

            $screenshot = Screenshot::create([
                'user_id' => Auth::id() ?? 1,
                'screenshot_path' => $path,
                'filename' => $filename,
                'file_size' => $file->getSize(),
                'captured_at' => $data['captured_at'] ?? now(),
                'metadata' => !empty($data['metadata']) ? json_encode($data['metadata']) : null,
            ]);

            return response()->json([
                'error' => false,
                'message' => 'Screenshot uploaded successfully.',
                'data' => [
                    'id' => $screenshot->id,
                    'filename' => $filename,
                    'url' => Storage::url($path),
                    'captured_at' => $screenshot->captured_at->toDateTimeString(),
                    'file_size_kb' => round($screenshot->file_size / 1024, 2) . ' KB',
                    'metadata' => $screenshot->metadata ? json_decode($screenshot->metadata, true) : null,

                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => true,
                'message' => 'Validation error.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Screenshot upload failed: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Failed to upload screenshot.',
                'data' => []
            ], 500);
        }
    }
    /**
     * Load Config - Returns time tracking configuration.
     *
     * Loads current configuration for time tracking such as screenshot interval, idle threshold, and break detection timing.
     *
     * @authenticated
     *
     * @group Time Tracking
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Config loaded successfully.",
     *   "data": {
     *     "screenshotInterval": 60000,
     *     "idleTimeThreshold": 300000,
     *     "breakTimeThreshold": 600000
     *   }
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "Failed to load configuration"
     * }
     *
     */
    public function loadConfig(Request $request)
    {
        try {
            // Try to load from database first
            $configData = TimeTrackerConfig::where('name', 'time_tracker_config')->value('value');
            $config = [
                'screenshotInterval' => (int) ($configData['screenshotInterval'] ??  '60000'), // Default to 60 seconds
                'idleTimeThreshold' => (int) ($configData['idleTimeThreshold'] ??  '300000'), // Default to 5 minutes
                'breakTimeThreshold' => (int) ($configData['breakTimeThreshold'] ??  '600000'), // Default to 10 minutes
                'maxDailyBreakTime' => (int) ($configData['maxDailyBreakTime'] ??  '3600000'), // Default to 1 hour
                'manualTimeApprover' => $configData['manualTimeApprover'] ?? []
            ];
            return formatApiResponse(
                false,
                'Config loaded successfully.',
                [
                    'data' => $config
                ]
            );
        } catch (Exception $e) {
            Log::error('Failed to load config: ' . $e->getMessage());
            return formatApiResponse(true, 'Failed to load configuration');
        }
    }
    /**
     * Display the time tracker index page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('timetracker::timetracker.index');
    }
    // Configuration Page
    public function configuration()
    {
        // Default values in milliseconds
        $defaultConfig = [
            'screenshotInterval' => 60000,        // 1 minute
            'idleTimeThreshold' => 300000,        // 5 minutes
            'breakTimeThreshold' => 600000,       // 10 minutes
            'maxDailyBreakTime' => 3600000,       // 1 hour
            'manualTimeApprover' => [],
            'workDayStartTime' => '09:00',        // Default work day start
            'auto_delete_screenshots_after_days' => 30, // Default to 30 days
        ];
        // Get saved config from DB
        $config = TimeTrackerConfig::where('name', 'time_tracker_config')->value('value');
        // Decode JSON if available
        $decoded = is_array($config) ? $config : json_decode($config, true);
        // Merge decoded config with defaults
        $time_tracker_config = array_merge($defaultConfig, $decoded ?? []);
        $users = User::select('id', 'first_name', 'last_name')->get()->mapWithKeys(function ($user) {
            return [$user->id => $user->first_name . ' ' . $user->last_name];
        });
        return view('timetracker::timetracker.configuration', compact('time_tracker_config', 'users'));
    }
    // Store Configuration
    public function storeConfig(Request $request)
    {
        $formFields = $request->validate([
            'screenshotInterval' => 'required|integer|min:1',
            'idleTimeThreshold' => 'required|integer|min:1',
            'breakTimeThreshold' => 'required|integer|min:1',
            'maxDailyBreakTime' => 'required|integer|min:1',
            'manualTimeApprover' => 'nullable|array',
            'manualTimeApprover.*' => 'exists:users,id',
            'workDayStartTime' => 'required|date_format:H:i', // Validate as time format
            'auto_delete_screenshots_after_days' => 'nullable|integer|min:1',
        ]);
        $config = [
            'screenshotInterval' => $formFields['screenshotInterval'] * 1000,
            'idleTimeThreshold' => $formFields['idleTimeThreshold'] * 1000,
            'breakTimeThreshold' => $formFields['breakTimeThreshold'] * 1000,
            'maxDailyBreakTime' => $formFields['maxDailyBreakTime'] * 1000,
            'manualTimeApprover' => $formFields['manualTimeApprover'],
            'workDayStartTime' => $formFields['workDayStartTime'],
            'auto_delete_screenshots_after_days' => $formFields['auto_delete_screenshots_after_days'] ?? 30, // Default to 30 days if not set
        ];
        try {
            DB::beginTransaction();
            TimeTrackerConfig::updateOrInsert(
                ['name' => 'time_tracker_config'],
                [
                    'value' => json_encode($config),
                    'updated_at' => now()
                ]
            );
            DB::commit();
            return formatApiResponse(
                false,
                'Config stored successfully.',
                ['data' => $config]
            );
        } catch (Exception $e) {
            DB::rollBack();
            return formatApiResponse(
                true,
                'Config could not be stored. Please try again later.',
                [
                    'data' => [
                        'error' => $e->getMessage(),
                        'line' => $e->getLine(),
                    ]
                ]
            );
        }
    }
}
