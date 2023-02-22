<?php
session_start();
require_once __DIR__.'/config.php';
if (empty($_SESSION['login'])) {
	if (empty($_CONFIG['name']) || empty($_CONFIG['pass'])) {
		$_SESSION['login']=1;
	} elseif (!empty($_POST['jmeno']) && !empty($_POST['heslo'])) {
		if ($_POST['jmeno']==$_CONFIG['name'] && password_verify($_POST['heslo'],$_CONFIG['pass'])) {
			$_SESSION['login']=1;
		}
	}
} elseif (!empty($_GET['logout']) && !empty($_CONFIG['name']) && !empty($_CONFIG['pass'])) {
	unset($_SESSION['login']);
}
?>
<!doctype html>
<html lang="cs">
<head>
	<meta charset="utf-8" />
	<meta name="robots" content="noindex, nofollow" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="stylesheet" href="/css/bootstrap.min.css" type="text/css" />
	<link rel="icon" href="/favicon.svg" type="image/svg+xml" />
</head>
<body>
<main class="container">
<?php
if (empty($_SESSION['login'])) {
?>
<form action="/" method="post">
	<div class="position-absolute top-50 start-50 translate-middle">
		<div class="d-flex flex-wrap">
			<div class="my-1 w-100"><input type="text" id="jmeno" name="jmeno" placeholder="Jméno" class="form-control" required /></div>
			<div class="my-1 w-100"><input type="password" id="heslo" name="heslo" placeholder="Heslo" class="form-control" required /></div>
			<div class="my-1 w-100 text-center"><button class="btn btn-primary">Přihlásit se</button></div>
		</div>
	</div>
</form>
<?php
} else {
?>
<div class="row mt-2 mt-md-4">
	<div class="col-md-3 col-lg-2 mb-2 pe-lg-4">
		<div class="d-flex flex-wrap flex-md-column">
			<div class="p-1 px-md-0 flex-fill"><a href="/domains/" class="btn btn-primary w-100 text-md-start">Domény</a></div>
			<div class="p-1 px-md-0 flex-fill"><a href="/credit/" class="btn btn-primary w-100 text-md-start">Kredit</a></div>
			<div class="p-1 px-md-0 flex-fill"><a href="/movement/" class="btn btn-primary w-100 text-md-start">Pohyby</a></div>
			<div class="p-1 px-md-0 flex-fill"><a href="/ping/" class="btn btn-primary w-100 text-md-start">Ping</a></div>
<?php
if (!empty($_CONFIG['name']) && !empty($_CONFIG['pass'])) {
?>
			<div class="p-1 px-md-0 flex-fill"><a href="/?logout=yes" class="btn btn-secondary w-100 text-md-start">Odhlásit</a></div>
<?php
}
?>
		</div>
	</div>
	<div class="col-md-9 col-lg-10">
<?php
$time=time();
$auth=sha1($_CONFIG['apiname'].sha1($_CONFIG['apipass']).date('H',$time));
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
				'clTRID'=>$go.' - '.$time
			]
		];
	break;
	case "credit":
		$input=[
			'request'=>[
				'user'=>$_CONFIG['apiname'],
				'auth'=>$auth,
				'command'=>'credit-info',
				'clTRID'=>$go.' - '.$time
			]
		];
	break;
	case "movement":
		$input=[
			'request'=>[
				'user'=>$_CONFIG['apiname'],
				'auth'=>$auth,
				'command'=>'account-list',
				'clTRID'=>$go.' - '.$time,
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
				'clTRID'=>$go.' - '.$time
			]
		];
	break;
	case "renew":
		$input=[
			'request'=>[
				'user'=>$_CONFIG['apiname'],
				'auth'=>$auth,
				'command'=>'domain-renew',
				'clTRID'=>$go.' - '.$time,
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
	$ch=curl_init('https://api.wedos.com/wapi/json');
	curl_setopt($ch,CURLOPT_TIMEOUT,60);
	curl_setopt($ch,CURLOPT_POST,true);
	curl_setopt($ch,CURLOPT_POSTFIELDS,'request='.$json);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_HTTPHEADER,['Content-Type: application/x-www-form-urlencoded']);
	$res=curl_exec($ch);
	$data=json_decode($res,true);
	switch ($go) {
		case "ping":
			foreach($data['response'] as $klic => $hodnota) {
				echo "{$klic}: {$hodnota}<br/>";
			}
		break;
		case "credit":
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
			echo "<table class=\"table\">";
			echo "<thead><tr><th class=\"text-center\">#<th><a href=\"/domains/?sortName=1\" class=\"link-dark\">Doména</a></th><th>Stav</th><th class=\"text-center\"><a href=\"/domains/\" class=\"link-dark\">Expirace</a></th><th class=\"text-center\">Akce</th></tr></thead>";
			echo "<tbody class=\"table-group-divider\">";
			if (count($pole_domen)>0) {
				$today=date("Y-m-d");
				$fourteen=date("Y-m-d",strtotime("+14 days"));
				$no=1;
				if (!empty($_GET['sortName'])) {
					$name=array_column($pole_domen, 'name');
					array_multisort($name, SORT_ASC, $pole_domen);
				} else {
					$expiration=array_column($pole_domen, 'expiration');
					array_multisort($expiration, SORT_ASC, $pole_domen);
				}
				foreach ($pole_domen as $klic => $hodnota) {
					if ($hodnota['status']!="deleted") {
						echo "<tr><td class=\"text-center\">";
						if ($hodnota['expiration']<=$today) {
							echo "<span class=\"badge text-bg-danger\">{$no}</span>";
						} elseif ($hodnota['expiration']<=$fourteen) {
							echo "<span class=\"badge text-bg-warning\">{$no}</span>";
						} else {
							echo "<span class=\"badge text-bg-success\">{$no}</span>";
						}
						echo "<td><a href=\"https://{$hodnota['name']}\" class=\"link-dark\" target=\"_blank\">{$hodnota['name']}</a></td><td>{$hodnota['status']}</td><td class=\"text-center\">".date("d.m.Y",strtotime($hodnota['expiration']))."</td><td class=\"text-center\"><a href=\"/renew/?name={$hodnota['name']}\" onclick=\"return(confirm('Opravdu prodloužit o 1 rok?'));\">Prodloužit</a></td></tr>";
						$no++;
					}
				}
			}
			echo "</tbody>";
			echo "</table>";
		break;
		case "renew":
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
	echo "<br/>\n";
} else {
	echo "<div class=\"m-2 text-center text-md-start\"><a href=\"https://github.com/foldas/wedos\" target=\"_blank\">https://github.com/foldas/wedos</a></div>";
}
?>
	</div>
</div>
<?php
}
?>
</main>
</body>
</html>