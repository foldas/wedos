<?php
$_CONFIG = [
	'name' => '',			// site login name
	'pass' => '',			// site password [password hash format]
	'apiname' => '',		// API name
	'apipass' => '',		// API password
	'email' => '',			// e-mail for notifications (to)
	'from' => '',			// e-mail for notifications (from)
	'blocked' => [],		// blocked domain for renewal [array]
	'external' => [],		// external domains [array of array]
	'fakturoid' => []		// fakturoid api
];

/*
External domains example:
	'external' => [
		[
			'name' => '',				// domain name
			'expiration' => '',			// domain expiration (format YYYY-MM-DD)
			'note' => ''				// note
		],[
			'name' => '',
			'expiration' => '',
			'note' => ''
		]
	]
*/

/*
Fakturoid example:
	'fakturoid' => [
		'id' => '',									// fakturoid auth id
		'secret' => '',								// fakturoid auth secret
		'slug' => '',								// name of fakturoid account (slug)
		'app' => 'Domainator <email@domain.cz>',	// app name (can be anything) + e-mail
		'invoices' => [
			'domena.cz' => '123456',				// domain name, invoice id (not number)
			'domena.eu' => '123456'
		]
	]
*/
