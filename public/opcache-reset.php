<?php
// TEMPORARY — flush PHP OPcache in the web-server process, then DELETE this file.
if (function_exists('opcache_reset')) {
    $ok = opcache_reset();
    echo $ok ? 'OPcache reset OK' : 'OPcache reset returned false (maybe already empty / disabled)';
} else {
    echo 'OPcache is not enabled on this server.';
}
echo "\n";
echo 'clearstatcache: '; clearstatcache(true);
echo "done\n";
