# ahojpay-module

### Inštalácia

1. Manuálne:
-Skopírovať zložku app/code/Ahoj/AhojPay do rootovského adresára
-Skopírovať zložku UniModul do rootovského adresára


#### Po inštalácií je potrebné povoliť modul prostredníctvom príkazu:

$ php bin/magento module:enable Ahoj_Ahojpay
$ php bin/magento setup:upgrade
$ php bin/magento setup:db-schema:upgrade
$ php bin/magento cron:run --group default

