<?php
require_once __DIR__.'/config.php';
if (file_exists("cron/{$_GET['go']}.php")) {
	include 'cron/'.$_GET['go'].'.php';
}
