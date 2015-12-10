# rrdtool-system-graphs

Complete php script for system graphing.

Uses simple web interface just to show graphs of system load.

Requires:
PHP
Apache
RRDTOOL

Install:

Debian/Ubuntu:
apt-get install apache2 php5 rrdtool

Configure:

Edit config.php file, it's all explained there

Run:

cron or daemon

cron:

add to crontab or create new file in cron.d and change path

*/5 *    * * *   www-data    php /var/www/html/cron.php

If you change update interval (300 sec) then also change */5 to */min where min is interval/60 (minutes).

daemon:

php daemon.php &
