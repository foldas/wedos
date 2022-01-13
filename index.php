<?php
session_start();
require_once __DIR__.'/config.php';
if (empty($_SESSION['login'])) {
	if (empty($_CONFIG['name']) && empty($_CONFIG['pass'])) {
		$_SESSION['login']=1;
	} elseif (!empty($_POST['jmeno']) && !empty($_POST['heslo'])) {
		if ($_POST['jmeno']==$_CONFIG['name'] && password_verify($_POST['heslo'],$_CONFIG['pass'])) {
			$_SESSION['login']=1;
		}
	}
}
?>
<!doctype html>
<html lang="cs">
<head>
<meta charset="utf-8" />
<meta name="robots" content="noindex, nofollow" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="/css/normalize.css" type="text/css" />
<link rel="stylesheet" href="/css/skeleton.css" type="text/css" />
<link rel="stylesheet" href="/css/custom.css" type="text/css" />
<link rel="icon" href="/favicon.svg" type="image/svg+xml" />
</head>
<body>
<?php
if (empty($_SESSION['login'])) {
?>
<div class="section">
	<div class="container">
		<form action="/" method="post">
			<input type="text" name="jmeno" placeholder="jméno" required />
			<br/><input type="password" name="heslo" placeholder="heslo" required />
			<br/><button class="button button-primary">PŘIHLÁSIT SE</button>
		</form>
	</div>
</div>
<?php
} else {
?>
<br/>
<div class="container">
<div class="row">
<div class="one-third column">
<?php
$auth = sha1($_CONFIG['apiname'].sha1($_CONFIG['apipass']).date('H', time()));
$cltrid = time();
$url = 'https://api.wedos.com/wapi/json';
?>
<br/><a href="/domains/" class="button button-primary">domény</a>
<br/><a href="/credit/" class="button button-primary">kredit</a>
<br/><a href="/movement/" class="button button-primary">pohyby</a>
<br/><a href="/ping/" class="button button-primary">ping</a>
</div>
<div class="two-thirds column">
<?php
if (!empty($_GET['go'])) $go=$_GET['go']; else $go="";
switch ($go) {
	default:
		$input=[];
	break;
	case "ping":
		$input=[
			'request'=>[
				'user'=>$_CONFIG['apiname'],
				'auth'=>$auth,
				'command'=>'ping',
				'clTRID'=>$go.' - '.$cltrid
			]
		];
	break;
	case "credit":
		$input=[
			'request'=>[
				'user'=>$_CONFIG['apiname'],
				'auth'=>$auth,
				'command'=>'credit-info',
				'clTRID'=>$go.' - '.$cltrid
			]
		];
	break;
	case "movement":
		$input=[
			'request'=>[
				'user'=>$_CONFIG['apiname'],
				'auth'=>$auth,
				'command'=>'account-list',
				'clTRID'=>$go.' - '.$cltrid,
				'data'=>[
					'date_from'=>date("Y-m-d",strtotime("-3 month")),
					'date_to'=>date("Y-m-d")
				]
			]
		];
	break;
	case "domains":
		$input=[
			'request'=>[
				'user'=>$_CONFIG['apiname'],
				'auth'=>$auth,
				'command'=>'domains-list',
				'clTRID'=>$go.' - '.$cltrid
			]
		];
	break;
	case "renew":
		$input=[
			'request'=>[
				'user'=>$_CONFIG['apiname'],
				'auth'=>$auth,
				'command'=>'domain-renew',
				'clTRID'=>$go.' - '.$cltrid,
				'data'=>[
					'name'=>$_GET['name'],
					'period'=>1
				]
			]
		];
	break;
}
if (!empty($input)) {
	$json=json_encode($input);
	$ch=curl_init($url);
	curl_setopt($ch,CURLOPT_TIMEOUT,60);
	curl_setopt($ch,CURLOPT_POST,true);
	curl_setopt($ch,CURLOPT_POSTFIELDS,'request='.$json);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_HTTPHEADER,['Content-Type: application/x-www-form-urlencoded']);
	$res=curl_exec($ch);
	$data=json_decode($res,true);
	switch ($go) {
		case "ping":
			echo "<br/>";
			foreach($data['response'] as $klic => $hodnota) {
				echo "{$klic}: {$hodnota}<br/>";
			}
		break;
		case "credit":
			echo "<br/>";
			$pole=[];
			foreach($data['response'] as $klic => $hodnota) {
				if (is_array($hodnota)) {
					$pole=$hodnota;
				}
			}
			foreach($pole as $klic => $hodnota) {
				echo "{$klic}: {$hodnota}<br/>";
			}
		break;
		case "movement":
			echo "<br/>";
			foreach($data['response'] as $klic => $hodnota) {
				if (is_array($hodnota)) {
					if ($klic=="data") echo "<br/>";
					echo "<b>{$klic}:</b><br/>";
					$i=0;
					$pole=[];
					foreach($hodnota as $klic2 => $hodnota2) {
						$pole[$i]="";
						if (is_array($hodnota2)) {
							foreach($hodnota2 as $klic3 => $hodnota3) {
								if ($klic3=="ID") $pole[$i].="<br/>";
								$pole[$i].="{$klic3}: {$hodnota3}<br/>";
							}
						} else {
							$pole[$i].="{$klic2}: {$hodnota2}<br/>";
						}
						$i++;
					}
					krsort($pole);
					foreach ($pole as $item) {
						echo $item;
					}
				} else {
					echo "{$klic}: {$hodnota}<br/>";
				}
			}
		break;
		case "domains":
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
			echo "<table class=\"u-full-width\">";
			echo "<thead><tr><th>doména</th><th>stav</th><th class=\"text-center\">expriace</th><th class=\"text-center\">akce</th></tr></thead>";
			echo "<tbody>";
			if (count($pole_domen)>0) {
				$expiration=array_column($pole_domen, 'expiration');
				array_multisort($expiration, SORT_ASC, $pole_domen);
				foreach ($pole_domen as $klic => $hodnota) {
					if ($hodnota['status']!="deleted") {
						echo "<tr><td><a href=\"https://{$hodnota['name']}\" target=\"_blank\">{$hodnota['name']}</a></td><td>{$hodnota['status']}</td><td class=\"text-center\">".date("d.m.Y",strtotime($hodnota['expiration']))."</td><td class=\"text-center\"><a href=\"/renew/?name={$hodnota['name']}\" onclick=\"return(confirm('Opravdu prodloužit o 1 rok?'));\">Prodloužit</a></td></tr>";
					}
				}
			}
			echo "</tbody>";
			echo "</table>";
		break;
		case "renew":
			echo "<br/>";
			foreach($data['response'] as $klic => $hodnota) {
				if (is_array($hodnota)) {
					echo "<b>{$klic}:</b><br/>";
					foreach($hodnota as $klic2 => $hodnota2) {
						if (is_array($hodnota2)) {
							foreach($hodnota2 as $klic3 => $hodnota3) {
								echo "{$klic3}: {$hodnota3}<br/>";
							}
						} else {
							echo "{$klic2}: {$hodnota2}<br/>";
						}
					}
				} else {
					echo "{$klic}: {$hodnota}<br/>";
				}
			}
		break;
	}
	echo "<br/>";
}
?>
</div>
</div>
</div>
<?php
}
?>
</div>
</body>
</html>