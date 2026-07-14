<?php
if (($_GET['run'] ?? '') !== 'FxV20_2026') { http_response_code(403); exit('Forbidden'); }

$root  = dirname(__DIR__);
$files = [
    'app/Http/Controllers/Web/FedExBatchController.php',
];

$ok = []; $fail = [];
foreach ($files as $rel) {
    $src  = __DIR__ . '/' . basename($rel);
    $dest = $root . '/' . $rel;
    if (!file_exists($src))          { $fail[] = "$rel (source missing)"; continue; }
    if (!is_dir(dirname($dest)))     { $fail[] = "$rel (dest dir missing)"; continue; }
    if (!copy($src, $dest))          { $fail[] = "$rel (copy failed)"; continue; }
    $ok[] = basename($rel);
}

// Clear caches
@shell_exec('php ' . $root . '/artisan cache:clear 2>&1');
@shell_exec('php ' . $root . '/artisan config:clear 2>&1');
@shell_exec('php ' . $root . '/artisan view:clear 2>&1');

echo 'v20 ' . (empty($fail) ? 'OK' : 'PARTIAL') . ' (' . count($ok) . '): ';
echo implode(', ', $ok);
if ($fail) echo ' FAIL: ' . implode(', ', $fail);
