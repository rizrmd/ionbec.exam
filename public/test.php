<?php
echo "Direct PHP file access works - " . date('Y-m-d H:i:s');
echo "\nRequest URI: " . ($_SERVER['REQUEST_URI'] ?? 'unknown');
echo "\nQuery String: " . ($_SERVER['QUERY_STRING'] ?? 'none');
?>