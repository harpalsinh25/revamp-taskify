<?php
$output = shell_exec('git diff HEAD~1 -- resources/views/settings/general_settings.blade.php');
file_put_contents('git_diff_output.txt', $output);
echo "Done";
