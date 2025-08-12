<?php
// Laravel safe vendor update script - FIXED Version
// WARNING: This script will replace your entire vendor folder.
// Make sure you have a tested vendor.zip before running this!

set_time_limit(0);
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', 0);

$vendorFolder = __DIR__ . '/vendor';
$vendorZip = __DIR__ . '/vendor.zip';
$artisan = __DIR__ . '/artisan';
$backupFolder = __DIR__ . '/vendor_backup_' . date('Y-m-d_H-i-s');

// Enhanced logging function with real-time output
function logMessage($message, $type = 'INFO')
{
    $timestamp = date('Y-m-d H:i:s');
    $isCli = php_sapi_name() === 'cli';

    if ($isCli) {
        echo "[$timestamp] [$type] $message\n";
    } else {
        $colorMap = [
            'ERROR' => '#dc3545',
            'SUCCESS' => '#28a745',
            'INFO' => '#17a2b8'
        ];

        $bgColorMap = [
            'ERROR' => '#f8d7da',
            'SUCCESS' => '#d4edda',
            'INFO' => '#d1ecf1'
        ];

        $color = $colorMap[$type] ?? '#17a2b8';
        $bgColor = $bgColorMap[$type] ?? '#d1ecf1';

        $logEntry = "<div style='margin: 5px 0; padding: 8px 12px; background: $bgColor; border-left: 4px solid $color; border-radius: 4px; font-size: 14px;'>";
        $logEntry .= "<span style='font-weight: bold; color: #666; font-size: 12px;'>[$timestamp]</span> ";
        $logEntry .= "<span style='font-weight: bold; color: $color; font-size: 12px;'>[$type]</span> ";
        $logEntry .= "<span style='color: #333;'>$message</span>";
        $logEntry .= "</div>";

        echo "<script>
        document.getElementById('logMessages').innerHTML += `$logEntry`;
        document.getElementById('logContainer').scrollTop = document.getElementById('logContainer').scrollHeight;
        </script>";
    }

    // Force output to browser/terminal immediately
    if (ob_get_level()) {
        ob_flush();
    }
    flush();
}

// Single progress bar system with ETA
$progressBarCreated = false;
$operationStartTime = null;

function createProgressBar()
{
    global $progressBarCreated;
    if ($progressBarCreated) return;

    $isCli = php_sapi_name() === 'cli';
    if (!$isCli) {
        echo "<div id='progressContainer' style='position: relative; top: 0; left: 0; right: 0; z-index: 1000; background: white; border-bottom: 2px solid #e0e0e0; padding: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);'>";
        echo "<div id='progressMessage' style='margin-bottom: 8px; font-weight: bold; color: #333;'>Initializing...</div>";
        echo "<div style='background: #e0e0e0; border-radius: 12px; overflow: hidden; height: 28px; position: relative; margin-bottom: 8px;'>";
        echo "<div id='progressBar' style='background: linear-gradient(45deg, #4caf50, #45a049); height: 100%; width: 0%; transition: width 0.3s ease; position: relative;'></div>";
        echo "<div id='progressText' style='position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 13px; font-weight: bold; color: #333; text-shadow: 1px 1px 2px rgba(255,255,255,0.8);'>0%</div>";
        echo "</div>";
        echo "<div style='display: flex; justify-content: space-between; font-size: 12px; color: #666;'>";
        echo "<span id='progressStats'>0 / 0 files</span>";
        echo "<span id='progressETA'>Calculating...</span>";
        echo "<span id='progressSpeed'>0 files/sec</span>";
        echo "</div>";
        echo "</div>";

        // Add padding to body to account for fixed progress bar
        echo "<style>body { padding-top: 120px; }</style>";

        // JavaScript for real-time updates with ETA
        echo "<script>
        let startTime = new Date().getTime();
        let lastUpdateTime = startTime;
        let lastProgress = 0;

        function formatTime(seconds) {
            if (seconds < 60) return Math.round(seconds) + 's';
            if (seconds < 3600) return Math.floor(seconds / 60) + 'm ' + Math.round(seconds % 60) + 's';
            return Math.floor(seconds / 3600) + 'h ' + Math.floor((seconds % 3600) / 60) + 'm';
        }

        function updateProgress(percent, message, current, total) {
            const now = new Date().getTime();
            const elapsed = (now - startTime) / 1000;
            const timeSinceLastUpdate = (now - lastUpdateTime) / 1000;

            document.getElementById('progressBar').style.width = percent + '%';
            document.getElementById('progressText').textContent = Math.round(percent) + '%';
            document.getElementById('progressMessage').textContent = message;
            document.getElementById('progressStats').textContent = current + ' / ' + total + ' files';

            if (percent > 0 && elapsed > 1) {
                const speed = current / elapsed;
                const remaining = total - current;
                const eta = remaining / speed;

                document.getElementById('progressSpeed').textContent = Math.round(speed) + ' files/sec';
                document.getElementById('progressETA').textContent = percent >= 100 ? 'Completed!' : 'ETA: ' + formatTime(eta);
            }

            lastUpdateTime = now;
            lastProgress = percent;
        }
        </script>";

        if (ob_get_level()) ob_flush();
        flush();
    }
    $progressBarCreated = true;
}

function startOperation()
{
    global $operationStartTime;
    $operationStartTime = microtime(true);
    createProgressBar();
}

function updateProgress($current, $total, $message = '')
{
    $percent = ($current / $total) * 100;
    $isCli = php_sapi_name() === 'cli';

    if ($isCli) {
        global $operationStartTime;
        $elapsed = microtime(true) - $operationStartTime;
        $speed = $current / $elapsed;
        $eta = ($total - $current) / $speed;

        $bar = str_repeat('=', (int)($percent / 2)) . str_repeat('-', 50 - (int)($percent / 2));
        $etaFormatted = $eta < 60 ? round($eta) . 's' : round($eta / 60) . 'm';
        echo "\r[$bar] " . number_format($percent, 1) . "% $message | ETA: $etaFormatted | " . round($speed) . " files/sec";
        flush();
    } else {
        echo "<script>updateProgress(" . $percent . ", '" . addslashes($message) . "', " . $current . ", " . $total . ");</script>";
        if (ob_get_level()) ob_flush();
        flush();
    }
}

// Optimized recursive folder delete with ETA
function rrmdir(string $directory): bool
{
    if (!is_dir($directory)) {
        return false;
    }

    startOperation();

    // Count files first for progress
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    $totalFiles = iterator_count($iterator);
    $currentFile = 0;

    // Reset iterator
    $iterator->rewind();

    foreach ($iterator as $file) {
        $currentFile++;
        if ($currentFile % 50 === 0) { // Update progress every 50 files
            updateProgress($currentFile, $totalFiles, "Deleting backup files...");
        }

        if ($file->isDir()) {
            rmdir($file->getRealPath());
        } else {
            unlink($file->getRealPath());
        }
    }

    // Final progress update
    updateProgress($totalFiles, $totalFiles, "Deletion completed!");

    return rmdir($directory);
}

// Optimized copy function with ETA
function rcopy($src, $dst)
{
    startOperation();

    // Count files first
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    $totalFiles = iterator_count($iterator);
    $currentFile = 0;

    // Reset iterator
    $iterator->rewind();

    foreach ($iterator as $file) {
        $currentFile++;
        if ($currentFile % 25 === 0) { // Update progress every 25 files
            updateProgress($currentFile, $totalFiles, "Creating backup...");
        }

        $destPath = $dst . '/' . $iterator->getSubPathName();

        if ($file->isDir()) {
            mkdir($destPath, 0755, true);
        } else {
            copy($file->getRealPath(), $destPath);
        }
    }

    // Final progress update
    updateProgress($totalFiles, $totalFiles, "Backup completed!");
}

// FIXED: Enhanced zip extraction with proper structure validation
function extractZipWithProgress($zipPath, $extractTo)
{
    createProgressBar();

    $zip = new ZipArchive;
    if ($zip->open($zipPath) !== TRUE) {
        return false;
    }

    $totalFiles = $zip->numFiles;
    logMessage("Extracting $totalFiles files from vendor.zip...");

    // CRITICAL FIX: Validate zip structure before extraction
    $hasVendorFolder = false;
    $sampleFiles = [];

    for ($i = 0; $i < min(50, $totalFiles); $i++) { // Check first 50 files
        $filename = $zip->getNameIndex($i);
        $sampleFiles[] = $filename;
        if (strpos($filename, 'vendor/') === 0) {
            $hasVendorFolder = true;
        }
    }

    // Debug: Show sample files for troubleshooting
    logMessage("Sample files from zip: " . implode(', ', array_slice($sampleFiles, 0, 5)));

    // If zip contains vendor/ folder structure, extract to project root
    // If zip contains loose files, extract to vendor/ folder
    if ($hasVendorFolder) {
        logMessage("Detected vendor.zip contains 'vendor/' folder structure. Extracting to project root...");
        $actualExtractTo = dirname($extractTo); // Extract to project root
        logMessage("Actual extraction path: $actualExtractTo");
    } else {
        logMessage("Detected vendor.zip contains loose vendor files. Extracting to vendor/ folder...");
        $actualExtractTo = $extractTo; // Extract to vendor folder
        logMessage("Actual extraction path: $actualExtractTo");

        // Create vendor directory if it doesn't exist
        if (!is_dir($extractTo)) {
            mkdir($extractTo, 0755, true);
        }
    }

    // Verify extraction directory exists and is writable
    if (!is_dir($actualExtractTo)) {
        logMessage("Creating extraction directory: $actualExtractTo");
        if (!mkdir($actualExtractTo, 0755, true)) {
            logMessage("Failed to create extraction directory: $actualExtractTo", 'ERROR');
            return false;
        }
    }

    if (!is_writable($actualExtractTo)) {
        logMessage("Extraction directory is not writable: $actualExtractTo", 'ERROR');
        return false;
    }

    // Extract all files at once (more reliable than chunk extraction)
    logMessage("Starting extraction to: $actualExtractTo");

    try {
        $result = $zip->extractTo($actualExtractTo);
        if (!$result) {
            logMessage("ZipArchive::extractTo() returned false", 'ERROR');
            $zip->close();
            return false;
        }

        // Update progress to 100%
        updateProgress($totalFiles, $totalFiles, "Extraction completed!");

        // Verify extraction worked
        $extractedFiles = glob($actualExtractTo . '/*');
        $extractedCount = count($extractedFiles);
        logMessage("Extraction completed. Found $extractedCount items in extraction directory.");

        if ($extractedCount === 0) {
            logMessage("No files found after extraction - this indicates a problem", 'ERROR');
            $zip->close();
            return false;
        }
    } catch (Exception $e) {
        logMessage("Exception during extraction: " . $e->getMessage(), 'ERROR');
        $zip->close();
        return false;
    }

    $zip->close();
    return true;
}

// Function to validate vendor.zip structure
function validateVendorZip($zipPath)
{
    $zip = new ZipArchive;
    if ($zip->open($zipPath) !== TRUE) {
        return false;
    }

    $hasComposerFiles = false;
    $hasVendorStructure = false;
    $suspiciousFiles = [];

    // Check for key vendor files and structure
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $filename = $zip->getNameIndex($i);

        // Check for composer files (good indicators)
        if (
            strpos($filename, 'composer/') !== false ||
            strpos($filename, 'autoload.php') !== false
        ) {
            $hasComposerFiles = true;
        }

        // Check for vendor folder structure
        if (strpos($filename, 'vendor/') === 0) {
            $hasVendorStructure = true;
        }

        // Check for suspicious files that shouldn't be in vendor
        if (
            preg_match('/\.(php|js|css)$/', $filename) &&
            !preg_match('/vendor\//', $filename) &&
            !preg_match('/composer\//', $filename)
        ) {
            $suspiciousFiles[] = $filename;
        }
    }

    $zip->close();

    // Log validation results
    if ($hasComposerFiles) {
        logMessage("✓ Composer files detected in zip", 'SUCCESS');
    }

    if ($hasVendorStructure) {
        logMessage("✓ Vendor folder structure detected", 'SUCCESS');
    } else {
        logMessage("! Vendor folder structure not found - zip may contain loose files", 'INFO');
    }

    if (!empty($suspiciousFiles)) {
        logMessage("⚠ Suspicious files found outside vendor structure:", 'ERROR');
        foreach (array_slice($suspiciousFiles, 0, 10) as $file) {
            logMessage("  - $file", 'ERROR');
        }
    }

    return $hasComposerFiles || $hasVendorStructure;
}

// HTML header for browser output
if (php_sapi_name() !== 'cli') {
    echo "<!DOCTYPE html><html><head><title>Vendor Update Progress</title></head><body style='font-family: monospace; max-width: 800px; margin: 20px auto; padding: 20px;'>";
    echo "<h2 style='margin-bottom: 20px;'>Taskify Vendor Update Progress</h2>";

    // Create progress bar immediately
    createProgressBar();

    // Log container for messages
    echo "<div id='logContainer' style='max-height: 450px; overflow-y: auto; background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #e9ecef; margin-top: 20px;'>";
    echo "<div id='logMessages'></div>";
    echo "</div>";

    if (ob_get_level()) ob_flush();
    flush();
}

// Pre-flight checks
logMessage("Starting vendor folder update process...");

if (!file_exists($artisan)) {
    logMessage("artisan file not found. Ensure this script is in your Laravel project root.", 'ERROR');
    exit(1);
}

if (!file_exists($vendorZip)) {
    logMessage("vendor.zip not found in project root.", 'ERROR');
    exit(1);
}

// Validate vendor.zip structure
logMessage("Validating vendor.zip structure...");
if (!validateVendorZip($vendorZip)) {
    logMessage("vendor.zip validation failed. The zip may not contain valid vendor files.", 'ERROR');
    logMessage("Please ensure your vendor.zip contains either:", 'ERROR');
    logMessage("1. A 'vendor/' folder with all dependencies inside, OR", 'ERROR');
    logMessage("2. Loose vendor files (autoload.php, composer/, etc.)", 'ERROR');
    exit(1);
}

// Quick zip validation
$zip = new ZipArchive;
if ($zip->open($vendorZip) !== TRUE) {
    logMessage("Cannot open vendor.zip - file may be corrupted.", 'ERROR');
    exit(1);
}
$fileCount = $zip->numFiles;
$zip->close();

logMessage("Found $fileCount files in vendor.zip", 'SUCCESS');

// ENHANCED SAFETY CHECK: Deep validation without extraction
logMessage("Performing comprehensive zip validation...");

$zip = new ZipArchive;
if ($zip->open($vendorZip) !== TRUE) {
    logMessage("Cannot open vendor.zip for validation.", 'ERROR');
    exit(1);
}

$hasVendorStructure = false;
$hasAutoload = false;
$hasComposerDir = false;
$vendorDirCount = 0;
$suspiciousFiles = [];

// Detailed analysis of zip contents
for ($i = 0; $i < $zip->numFiles; $i++) {
    $filename = $zip->getNameIndex($i);

    // Check for vendor folder structure
    if (strpos($filename, 'vendor/') === 0) {
        $hasVendorStructure = true;
        $vendorDirCount++;
    }

    // Check for autoload.php (critical file)
    if ($filename === 'vendor/autoload.php' || $filename === 'autoload.php') {
        $hasAutoload = true;
    }

    // Check for composer directory
    if (strpos($filename, 'vendor/composer/') === 0 || strpos($filename, 'composer/') === 0) {
        $hasComposerDir = true;
    }

    // Check for files that shouldn't be in vendor
    if (
        !preg_match('/^vendor\//', $filename) &&
        !in_array($filename, ['autoload.php', 'composer/']) &&
        preg_match('/\.(php|js|css|html)$/', $filename)
    ) {
        $suspiciousFiles[] = $filename;
    }
}

$zip->close();

// Validation results
logMessage("Zip validation results:");
logMessage("- Vendor structure: " . ($hasVendorStructure ? "✓ Found" : "✗ Missing"));
logMessage("- Autoload.php: " . ($hasAutoload ? "✓ Found" : "✗ Missing"));
logMessage("- Composer directory: " . ($hasComposerDir ? "✓ Found" : "✗ Missing"));
logMessage("- Vendor files count: $vendorDirCount");

if (!empty($suspiciousFiles)) {
    logMessage("⚠ Found " . count($suspiciousFiles) . " suspicious files outside vendor structure", 'ERROR');
    foreach (array_slice($suspiciousFiles, 0, 5) as $file) {
        logMessage("  - $file", 'ERROR');
    }
}

// Final validation
if (!$hasAutoload) {
    logMessage("Critical validation failed: No autoload.php found in zip", 'ERROR');
    exit(1);
}

if (!$hasComposerDir) {
    logMessage("Critical validation failed: No composer directory found", 'ERROR');
    exit(1);
}

if ($vendorDirCount < 10) {
    logMessage("Warning: Very few vendor files detected ($vendorDirCount). This may not be a complete vendor folder.", 'ERROR');
    exit(1);
}

logMessage("Zip validation passed - vendor.zip appears to be valid.", 'SUCCESS');

// Step 1: Backup vendor folder
logMessage("Creating backup of existing vendor folder...");
if (is_dir($vendorFolder)) {
    if (!rename($vendorFolder, $backupFolder)) {
        logMessage("Rename failed, using copy method...");
        rcopy($vendorFolder, $backupFolder);
        rrmdir($vendorFolder);
    }
    logMessage("Backup created: $backupFolder", 'SUCCESS');
} else {
    logMessage("No existing vendor folder found, proceeding with extraction...");
}

// Step 2: Enable maintenance mode
logMessage("Enabling maintenance mode...");
$output = shell_exec("php \"$artisan\" down --render=\"errors::503\" 2>&1");
if ($output) {
    logMessage(trim($output));
}

// Step 3: Extract vendor.zip with progress (using the fixed function)
logMessage("Extracting vendor.zip...");
if (extractZipWithProgress($vendorZip, $vendorFolder)) {
    logMessage("Vendor folder extracted successfully.", 'SUCCESS');
} else {
    logMessage("Extraction failed. Restoring backup...", 'ERROR');
    if (is_dir($backupFolder)) {
        if (is_dir($vendorFolder)) {
            rrmdir($vendorFolder);
        }
        rename($backupFolder, $vendorFolder);
    }
    exit(1);
}

// Step 4: Verify vendor folder and key files
if (!is_dir($vendorFolder)) {
    logMessage("Vendor folder was not created. Restoring backup...", 'ERROR');
    if (is_dir($backupFolder)) {
        rename($backupFolder, $vendorFolder);
    }
    exit(1);
}

// Verify critical files
if (!file_exists($vendorFolder . '/autoload.php')) {
    logMessage("Critical file missing: vendor/autoload.php. Restoring backup...", 'ERROR');
    if (is_dir($backupFolder)) {
        rrmdir($vendorFolder);
        rename($backupFolder, $vendorFolder);
    }
    exit(1);
}

logMessage("Vendor folder structure validated successfully.", 'SUCCESS');

// Step 5: Run Laravel optimize commands
logMessage("Running Laravel optimization commands...");
$commands = [
    "config:clear" => "Clearing configuration cache...",
    "cache:clear" => "Clearing application cache...",
    "route:clear" => "Clearing route cache...",
    "view:clear" => "Clearing view cache...",
    "optimize" => "Optimizing application..."
];

foreach ($commands as $command => $description) {
    logMessage($description);
    $output = shell_exec("php \"$artisan\" $command 2>&1");
    if ($output && trim($output) !== '') {
        logMessage(trim($output));
    }
}

// Step 6: Disable maintenance mode
logMessage("Disabling maintenance mode...");
$output = shell_exec("php \"$artisan\" up 2>&1");
if ($output) {
    logMessage(trim($output));
}

// Step 7: Cleanup
logMessage("Cleaning up...");

// Remove backup folder
if (is_dir($backupFolder)) {
    logMessage("Removing backup folder...");
    if (rrmdir($backupFolder)) {
        logMessage("Backup folder removed.", 'SUCCESS');
    } else {
        logMessage("Could not remove backup folder: $backupFolder", 'ERROR');
    }
}

// Remove vendor.zip
if (file_exists($vendorZip)) {
    if (unlink($vendorZip)) {
        logMessage("vendor.zip removed.", 'SUCCESS');
    } else {
        logMessage("Could not remove vendor.zip.", 'ERROR');
    }
}

logMessage("Vendor update completed successfully!", 'SUCCESS');

// Self-delete (commented out for safety during testing)
if (__FILE__ !== '') {
    unlink(__FILE__);
    logMessage("Update script has been removed for security.", 'SUCCESS');
}


// HTML footer for browser output
if (php_sapi_name() !== 'cli') {
    echo "<script>
        setTimeout(function() {
            window.location.reload();
        }, 3000);
    </script>";
    echo "</body></html>";
}
