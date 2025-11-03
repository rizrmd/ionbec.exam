<?php

/**
 * Script untuk monitoring Laravel logs secara real-time
 * Gunakan untuk memantau debug output dari getQuestions endpoint
 */

echo "=== Monitoring Laravel Logs for Question Loading Issue ===\n";
echo "Tekan Ctrl+C untuk berhenti\n\n";

// Tunggu user mengakses exam lalu cek logs
echo "1. Buka exam di browser\n";
echo "2. Navigasi ke question yang blank\n";
echo "3. Script ini akan menampilkan debug logs secara real-time\n\n";

// Monitor logs untuk getQuestions
$command = 'ssh root@cf.avolut.com "docker exec app-okksscs4w0s8oc0go0k4cg8k tail -f /var/www/storage/logs/laravel.log | grep -E \'(getQuestions|Questions query)'"';

echo "Menjalankan command: $command\n";
echo "========================================\n\n";

passthru($command);