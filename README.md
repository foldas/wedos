## Webová aplikace pro správu domén z wedos.cz

![dashboard](https://raw.githubusercontent.com/foldas/wedos/main/.github/images/dashboard.jpg)

### Funkce

- seznam domén včetně dat expirací
- prodloužení domény na jeden klik z kreditu
- stav kreditu
- pohyby kreditu za poslední 3 měsíce
- ping

### Použití

- přejmenujte config.sample.php na config.php a vyplňte údaje (přihlašovací údaje k aplikaci + k API)
- proměnná `$_CONFIG['pass']` musí být hashována přes password_hash funkci
- pokud není `$_CONFIG['name']` ani `$_CONFIG['pass']` vyplněno, přihlášení do aplikace se nepoužije a rovnou se spustí

### Cron

- podle potřeby můžete spouštět přes cron úlohu, která pošle na e-mail blížící se expirace domén (14 dnů)
- spouštějte 1x týdně (případně 1x za den) přes /cron/expiry/ nebo /cron.php?go=expiry
- doplňte příjemce do `$_CONFIG['email']` a odesílací e-mailovou adresu do `$_CONFIG['from']`