<?php

/**
 * Debug script to check what's happening with item count discrepancy
 */

echo "=== DEBUG ITEM COUNT DISCREPANCY (78 vs 52) ===\n\n";

// Check recent logs to understand data flow
echo "1. CHECKING BACKEND RUST API CALLS:\n";
echo "===================================\n";
$backendCommand = 'ssh root@cf.avolut.com "docker exec app-okksscs4w0s8oc0go0k4cg8k tail -100 /var/www/storage/logs/laravel.log | grep -E \'(RUST API|items_count|UNIFIED DATA SOURCE)\' | tail -10"';
echo "Command: $backendCommand\n";
$output = shell_exec($backendCommand);
echo $output;

echo "\n2. CHECKING EXAM RENDERING:\n";
echo "==========================\n";
$examCommand = 'ssh root@cf.avolut.com "docker exec app-okksscs4w0s8oc0go0k4cg8k tail -50 /var/www/storage/logs/laravel.log | grep -E \'(MainController: Rendering)\' | tail -5"';
echo "Command: $examCommand\n";
$output2 = shell_exec($examCommand);
echo $output2;

echo "\n3. CHECKING FRONTEND JAVASCRIPT ISSUES:\n";
echo "======================================\n";
echo "Since backend is correctly sending 52 items but user sees 78, possible causes:\n";
echo "- Browser caching old JavaScript files\n";
echo "- Frontend using stale localStorage data\n";
echo "- Client-side processing adding items incorrectly\n";
echo "- JavaScript error preventing proper data handling\n";

echo "\n4. VERIFICATION STEPS FOR USER:\n";
echo "===============================\n";
echo "1. Clear browser cache and localStorage\n";
echo "2. Open developer console and check for JavaScript errors\n";
echo "3. Check Network tab for /exam request response\n";
echo "4. Verify examItems count in browser console\n";
echo "5. Check if localStorage has old exam-state data\n";

echo "\n5. LOCALSTORAGE DEBUG:\n";
echo "====================\n";
echo "User should run in browser console:\n";
echo "- localStorage.clear() // Clear all local storage\n";
echo "- localStorage.getItem('exam-state') // Check exam state\n";
echo "- console.log('examItems count:', examItems.length) // In Vue dev tools\n";

echo "\n=== DEBUG COMPLETE ===\n";