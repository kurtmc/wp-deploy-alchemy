#!/bin/bash

DEPLOY_ENV=staging

/usr/bin/mysqld_safe --basedir=/usr &
# Need to sleep to give mariadb time to start
sleep 5
/usr/sbin/httpd

# Start sshd
/usr/sbin/sshd -D &

# Deploy
source /usr/local/rvm/scripts/rvm
rvm --default use ${RUBY_VERSION}
cd /var/alchemy
./deploy.rb ${DEPLOY_ENV}

# Tail log
tail -f /var/log/httpd/error_log
