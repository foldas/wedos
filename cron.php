<?php
require_once __DIR__.'/config.php';
// CLI: php cron.php expiry | web: /cron.php?go=expiry
$go = PHP_SAPI === 'cli' ? ($argv[1] ?? '') : ($_GET['go'] ?? '');
if (is_string($go) && preg_match('/^[a-zA-Z0-9\-]+$/', $go) && file_exists(__DIR__."/cron/{$go}.php")) {
	include __DIR__.'/cron/'.$go.'.php';
} elseif (PHP_SAPI === 'cli') {
	fwrite(STDERR, "Unknown cron job: {$go}\n");
	exit(1);
}
