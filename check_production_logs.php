<?php

/**
 * Script untuk mengecek production logs tanpa continuous monitoring
 */

echo "=== Checking Production Logs ===\n\n";

// Check last 50 lines from Laravel log
$command = 'ssh root@cf.avolut.com "docker exec app-okksscs4w0s8oc0go0k4cg8k tail -50 /var/www/storage/logs/laravel.log"';

echo "Executing: $command\n";
echo "========================================\n\n";

$output = shell_exec($command);
echo $output;

echo "\n========================================\n";
echo "Log check completed.\n";
echo "\nTo monitor logs in real-time, run:\n";
echo "ssh root@cf.avolut.com \"docker exec app-okksscs4w0s8oc0go0k4cg8k tail -f /var/www/storage/logs/laravel.log | grep -E '(getQuestions|Questions query)\"\"\n";