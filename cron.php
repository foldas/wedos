<?php
require_once __DIR__.'/config.php';
$go = $_GET['go'] ?? '';
if (is_string($go) && preg_match('/^[a-zA-Z0-9\-]+$/', $go) && file_exists(__DIR__."/cron/{$go}.php")) {
	include __DIR__.'/cron/'.$go.'.php';
}
