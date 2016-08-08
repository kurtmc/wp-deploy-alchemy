#!/bin/bash

unlink /var/www/html/current
ln -s /var/alchemy /var/www/html/current

cd /var/www/html/current
ln -s /var/www/html/shared/.htaccess .htaccess
ln -s /var/www/html/shared/wp-config.php wp-config.php

/usr/bin/mysqld_safe --basedir=/usr &
# Need to sleep to give mariadb time to start
sleep 10
/usr/sbin/httpd
sleep 10

# Tail log
tail -f /var/log/httpd/error_log
