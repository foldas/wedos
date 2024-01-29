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
<!DOCTYPE html>
<html lang="cs">
<head>
	<meta charset="utf-8" />
	<meta name="robots" content="noindex, nofollow" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="stylesheet" href="/assets/css/bootstrap.min.css" type="text/css" />
	<link href="/assets/css/custom.min.css" rel="stylesheet">
	<link href="/assets/css/variables.css" rel="stylesheet">
	<link rel="icon" href="/favicon.svg" type="image/svg+xml" />
</head>
<body>
<main class="container">
<div class="d-flex">
	<ul class="navbar-nav ms-auto mt-3 me-1">
		<li class="nav-item dropdown">
			<button class="btn btn-link nav-link py-1 px-2 dropdown-toggle d-flex align-items-center border" id="bd-theme" type="button" aria-expanded="false" data-bs-toggle="dropdown">
				<svg class="bi my-1 theme-icon-active"><use href="/assets/icons/bootstrap-icons.svg#circle-half"></use></svg>
			</button>
			<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="bd-theme" style="--bs-dropdown-min-width: 8rem;">
				<li>
					<button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light">
						<svg class="bi me-2 opacity-50 theme-icon"><use href="/assets/icons/bootstrap-icons.svg#sun-fill"></use></svg>
						Světlý
						<svg class="bi ms-auto d-none"><use href="/assets/icons/bootstrap-icons.svg#check2"></use></svg>
					</button>
				</li>
				<li>
					<button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark">
						<svg class="bi me-2 opacity-50 theme-icon"><use href="/assets/icons/bootstrap-icons.svg#moon-stars-fill"></use></svg>
						Tmavý
						<svg class="bi ms-auto d-none"><use href="/assets/icons/bootstrap-icons.svg#check2"></use></svg>
					</button>
				</li>
				<li>
					<button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="auto">
						<svg class="bi me-2 opacity-50 theme-icon"><use href="/assets/icons/bootstrap-icons.svg#circle-half"></use></svg>
						Auto
						<svg class="bi ms-auto d-none"><use href="/assets/icons/bootstrap-icons.svg#check2"></use></svg>
					</button>
				</li>
			</ul>
		</li>
	</ul>
</div>
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
			// external domains
			$pole_domen=[];
			if ($_CONFIG['external']) {
				foreach($_CONFIG['external'] as $item) {
					$pole_domen[]=[
						'status'=>'local',
						'name'=>$item['name'],
						'expiration'=>$item['expiration'],
						'note'=>$item['note'],
					];
				}
			}
			// fakturoid
			if ($_CONFIG['fakturoid']['invoices']) {
				$file_fakturoid=__DIR__."/files/fakturoid.json";
				if (file_exists($file_fakturoid)) {
					$changed=filemtime($file_fakturoid)+21600;
					if ($changed>time()) {
						$request_fakturoid=0;
					} else {
						$request_fakturoid=1;
					}
				} else {
					$request_fakturoid=1;
				}
				if ($request_fakturoid==1) {
					$invoices=[];
					foreach ($_CONFIG['fakturoid']['invoices'] as $invoice_key => $invoice_value) {
						if (empty($invoices[$invoice_value])) {
							$ch=curl_init();
							curl_setopt($ch,CURLOPT_URL,"https://app.fakturoid.cz/api/v2/accounts/{$_CONFIG['fakturoid']['account']}/invoices/{$invoice_value}.json");
							curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
							curl_setopt($ch,CURLOPT_HEADER,false);
							curl_setopt($ch,CURLOPT_HTTPHEADER,[
								"Content-Type: application/json",
								"Authorization: Basic ".base64_encode($_CONFIG['fakturoid']['email'].":".$_CONFIG['fakturoid']['api']),
								"User-Agent: {$_CONFIG['fakturoid']['app']} ({$_CONFIG['fakturoid']['email']})"
							]);
							$response=curl_exec($ch);
							curl_close($ch);
							$response_arr=json_decode($response,true);
							$invoices[$invoice_value]=$response_arr['status'];
							$fp=fopen($file_fakturoid, "w");
							fwrite($fp,json_encode($invoices));
							fclose($fp);
						}
					}
				} else {
					$handle=fopen($file_fakturoid,"r");
					$invoices=fread($handle,filesize($file_fakturoid));
					fclose($handle);
					$invoices=json_decode($invoices,true);
				}
			} else {
				$invoices=[];
			}
			// wedos response
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
			// list of domains
			echo "<table class=\"table\">";
			echo "<thead><tr><th class=\"text-center\">#</th><th><a href=\"/domains/?sortName=1\" class=\"text-body\">Doména</a></th><th>Stav</th><th class=\"text-center\"><a href=\"/domains/\" class=\"text-body\">Expirace</a></th><th class=\"text-center\">Akce</th></tr></thead>";
			echo "<tbody class=\"table-group-divider\">";
			if ($pole_domen) {
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
						echo "<td><a href=\"https://{$hodnota['name']}\" class=\"text-body\" target=\"_blank\">{$hodnota['name']}</a>";
						if (!empty($_CONFIG['fakturoid']['invoices'][$hodnota['name']]) && !empty($invoices[$_CONFIG['fakturoid']['invoices'][$hodnota['name']]])) {
							if ($invoices[$_CONFIG['fakturoid']['invoices'][$hodnota['name']]]=="paid") {
								echo "<span class=\"badge text-bg-success ms-2\">{$invoices[$_CONFIG['fakturoid']['invoices'][$hodnota['name']]]}</span>";
							} else {
								echo "<span class=\"badge text-bg-danger ms-2\">{$invoices[$_CONFIG['fakturoid']['invoices'][$hodnota['name']]]}</span>";
							}
						}

						echo "</td><td>";
						if ($hodnota['status']=="local") echo "<span class=\"badge text-secondary-emphasis bg-secondary-subtle border border-secondary-subtle\">{$hodnota['status']}</span>"; else echo $hodnota['status'];
						echo "</td><td class=\"text-center\">".date("d.m.Y",strtotime($hodnota['expiration']))."</td><td class=\"text-center\">";
						if ($hodnota['status']=="local") {
							echo "<i>{$hodnota['note']}</i>";
						} else {
							if (!in_array($hodnota['name'],$_CONFIG['blocked'])) {
								echo "<a href=\"/renew/?name={$hodnota['name']}\" onclick=\"return(confirm('Opravdu prodloužit o 1 rok?'));\">Prodloužit</a>";
							} else {
								echo "<span class=\"badge text-bg-primary\">X</span>";
							}
						}
						echo "</td></tr>";
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
<script src="/assets/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/switch.min.js"></script>
</body>
</html>