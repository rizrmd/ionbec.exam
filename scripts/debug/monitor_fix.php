<?php

echo "=== Monitoring Question Fix ===\n";
echo "Instructions:\n";
echo "1. User should test the exam now\n";
echo "2. Navigate to questions that were previously blank\n";
echo "3. This script will monitor logs for getQuestions calls\n";
echo "4. Press Ctrl+C to stop monitoring\n\n";

$monitorCommand = 'ssh root@cf.avolut.com "docker exec app-okksscs4w0s8oc0go0k4cg8k tail -f /var/www/storage/logs/laravel.log | grep -E \\"(getQuestions|Item found|Questions query)\\""';

echo "Running: $monitorCommand\n";
echo "========================================\n\n";

passthru($monitorCommand);