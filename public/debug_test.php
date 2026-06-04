<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$workspaceId = 1; // Or get it from somewhere, but assuming 1
$workspace = \App\Models\Workspace::find($workspaceId);
if (!$workspace) {
    echo "Workspace not found";
    exit;
}

$tasks = $workspace->tasks()->get();
$taskCount = $tasks->count();

$statuses = \App\Models\Status::all();
$sum = 0;
$statusCounts = [];
foreach ($statuses as $status) {
    $count = $workspace->tasks()->where('status_id', $status->id)->count();
    $sum += $count;
    $statusCounts[$status->id] = $count;
}

echo "Total Tasks (workspace->tasks()->count()): " . $workspace->tasks()->count() . "\n";
echo "Total Sum over Statuses: " . $sum . "\n";
echo "Distinct task count: " . $workspace->tasks()->distinct()->count() . "\n";
echo "Distinct task ids: " . $workspace->tasks()->distinct()->count('id') . "\n";

// Check task table for anything unusual
$rawCount = \DB::table('tasks')->where('workspace_id', $workspaceId)->count();
echo "Raw DB count: " . $rawCount . "\n";

file_put_contents('debug_output.txt', json_encode([
    'taskCount' => $taskCount,
    'sum' => $sum,
    'statusCounts' => $statusCounts,
    'rawCount' => $rawCount
]));
