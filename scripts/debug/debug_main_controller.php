<?php

/**
 * Debug MainController index method - check what data is sent to frontend
 */

echo "=== DEBUG MAIN CONTROLLER INDEX METHOD ===\n\n";

// Connect to production logs to check recent MainController activity
$logCommand = 'ssh root@cf.avolut.com "docker exec app-okksscs4w0s8oc0go0k4cg8k tail -50 /var/www/storage/logs/laravel.log | grep -E \'(MainController: Rendering|examItems|items_count)\'"';

echo "Checking recent MainController activity...\n";
echo "Command: $logCommand\n";
echo "========================================\n";

$output = shell_exec($logCommand);
echo $output;

echo "\n========================================\n\n";

// Also check if there are any recent exam main page loads
$logCommand2 = 'ssh root@cf.avolut.com "docker exec app-okksscs4w0s8oc0go0k4cg8k tail -20 /var/www/storage/logs/laravel.log | grep -E \'(MainController: Session check|ExamMiddleware: Exam session found)\'"';

echo "Checking recent exam session activity...\n";
echo "Command: $logCommand2\n";
echo "========================================\n";

$output2 = shell_exec($logCommand2);
echo $output2;

echo "\n=== DEBUG COMPLETE ===\n";