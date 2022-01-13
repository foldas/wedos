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