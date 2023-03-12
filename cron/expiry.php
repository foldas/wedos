<?php
if ($_CONFIG && !empty($_CONFIG['email']) && !empty($_CONFIG['from']) && filter_var($_CONFIG['email'],FILTER_VALIDATE_EMAIL) && filter_var($_CONFIG['from'],FILTER_VALIDATE_EMAIL)) {
	$fourteen=date("Y-m-d",strtotime("+14 days"));
	$time=time();
	$auth=sha1($_CONFIG['apiname'].sha1($_CONFIG['apipass']).date('H',$time));
	$input=[
		'request'=>[
			'user'=>$_CONFIG['apiname'],
			'auth'=>$auth,
			'command'=>'domains-list',
			'clTRID'=>'cron - '.$time
		]
	];
	$json=json_encode($input);
	$ch=curl_init('https://api.wedos.com/wapi/json');
	curl_setopt($ch,CURLOPT_TIMEOUT,60);
	curl_setopt($ch,CURLOPT_POST,true);
	curl_setopt($ch,CURLOPT_POSTFIELDS,'request='.$json);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_HTTPHEADER,['Content-Type: application/x-www-form-urlencoded']);
	$res=curl_exec($ch);
	$data=json_decode($res,true);
	$pole_domen=[];
	foreach($data['response'] as $klic => $hodnota) {
		if (is_array($hodnota)) {
			foreach($hodnota as $klic2 => $hodnota2) {
				if (is_array($hodnota2)) {
					foreach($hodnota2 as $klic3 => $hodnota3) {
						if (is_array($hodnota3)) {
							$pole_domen[]=$hodnota3;
						}
					}
				}
			}
		}
	}
	if ($pole_domen) {
		$message="<html><head><meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\"><style>*{background:white;color:black;font-family:verdana;font-size:12px;}table{padding:10px;border:1px solid grey;}th,td{padding:5px;}a{color:navy;}</style></head><body>";
		$message.="<table><thead><tr><th>Doména</th><th>Stav</th><th>Expirace</th></tr><thead><tbody>";
		$expiration=array_column($pole_domen, 'expiration');
		array_multisort($expiration, SORT_ASC, $pole_domen);
		foreach ($pole_domen as $klic => $hodnota) {
			if ($hodnota['status']!="deleted" && $hodnota['expiration']<=$fourteen) {
				$message.="<tr><td><a href=\"https://{$hodnota['name']}\" target=\"_blank\">{$hodnota['name']}</a></td><td>{$hodnota['status']}</td><td class=\"text-center\">".date("d.m.Y",strtotime($hodnota['expiration']))."</td></tr>";
			}
		}
		$message.="</tbody></table>";
		$message.="</body></html>";
		if (!empty($message)) {
			$headers=[
				'MIME-Version: 1.0',
				'Content-type: text/html; charset=utf-8',
				'From: '.$_CONFIG['from']
			];
			$r=mail($_CONFIG['email'],"Wedos domény",$message,implode("\r\n",$headers));
			var_dump($r);
			var_dump($headers);
		}
	}
}
?>