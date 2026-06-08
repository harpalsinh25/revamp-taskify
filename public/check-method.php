<?php
// TEMPORARY diagnostic — DELETE after use.
// Reports what the RUNNING php/web process actually sees for the bulk-upload
// controllers (file path, file mtime, whether the method exists at runtime).
require __DIR__ . '/../vendor/autoload.php';
header('Content-Type: text/plain');

$targets = [
    'App\\Http\\Controllers\\ProjectsController' => 'showBulkUploadForm',
    'App\\Http\\Controllers\\TasksController'    => 'showBulkUploadForm',
    'App\\Http\\Controllers\\UserController'     => 'showBulkUploadForm',
    'App\\Http\\Controllers\\ClientController'   => 'showBulkUploadForm',
];

foreach ($targets as $class => $method) {
    echo $class . "\n";
    if (!class_exists($class)) {
        echo "  -> class NOT loadable\n\n";
        continue;
    }
    $rc = new ReflectionClass($class);
    $file = $rc->getFileName();
    echo "  loaded from : " . $file . "\n";
    echo "  file mtime  : " . date('Y-m-d H:i:s', @filemtime($file)) . "\n";
    echo "  has method  : " . (method_exists($class, $method) ? 'YES' : 'NO  <-- problem') . "\n";
    echo "  method count: " . count($rc->getMethods()) . "\n\n";
}

if (function_exists('opcache_get_status')) {
    $s = @opcache_get_status(false);
    echo "OPcache enabled: " . ($s ? 'YES' : 'NO') . "\n";
    if ($s && isset($s['opcache_statistics'])) {
        echo "OPcache cached scripts: " . $s['opcache_statistics']['num_cached_scripts'] . "\n";
    }
} else {
    echo "OPcache: not available\n";
}
echo "PHP: " . PHP_VERSION . "  | SAPI: " . PHP_SAPI . "\n";
