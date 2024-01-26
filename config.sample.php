<?php
$_CONFIG=[
	"name"=>'',			// site login name
	"pass"=>'',			// site password [password hash format]
	"apiname"=>'',		// API name
	"apipass"=>'',		// API password
	"email"=>'',		// e-mail for notifications (to)
	"from"=>'',			// e-mail for notifications (from)
	"blocked"=>[],		// blocked domain for renewal [array]
,	"external"=>[
		[
			'name'=>'',				// domain name
			'expiration'=>'',		// domain expiration YYYY-MM-DD
			'note'=>''				// note
		],[
			'name'=>'',
			'expiration'=>'',
			'note'=>''
		]
	]					// external domains [array of array]
];
?>