#!/bin/bash

RUBY_VERSION=2.1
DEPENDS="git gnupg which tar findutils procps php php-mysql php-fpm httpd mariadb-server mariadb openssh-server"
DEPLOY_ENV=staging

yum update -y
yum install -y ${DEPENDS}

# Install RVM, ruby and bundler
gpg --keyserver hkp://keys.gnupg.net --recv-keys 409B6B1796C275462A1703113804BB82D39DC0E3
\curl -sSL https://get.rvm.io | bash
source /usr/local/rvm/scripts/rvm
rvm install ${RUBY_VERSION}
rvm --default use ${RUBY_VERSION}
gem install bundler

# Install wp-cli
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
mv wp-cli.phar /usr/local/bin/wp

# Start sshd
/usr/bin/ssh-keygen -A
mkdir -p ~/.ssh
ssh-keyscan localhost >> ~/.ssh/known_hosts
yes "" | ssh-keygen -t rsa -b 4096 -N ""
cat ~/.ssh/id_rsa.pub >> ~/.ssh/authorized_keys
/usr/sbin/sshd -D &

# Install capistrano
cd /var/alchemy
bundle install

# Start mariadb
/usr/libexec/mariadb-prepare-db-dir
/usr/bin/mysqld_safe --basedir=/usr &

# Need to sleep to give mariadb time to start
sleep 5

ROOT_PASS=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1)

# Setup mysql
# Make sure that NOBODY can access the server without a password
mysql -e "UPDATE mysql.user SET Password = PASSWORD('${ROOT_PASS}') WHERE User = 'root'"
# Kill the anonymous users
mysql -e "DROP USER ''@'localhost'"
# Because our hostname varies we'll use some Bash magic here.
mysql -e "DROP USER ''@'$(hostname)'"
# Kill off the demo database
mysql -e "DROP DATABASE test"
# Make our changes take effect
mysql -e "FLUSH PRIVILEGES"
# Any subsequent tries to run queries this way will get access denied because lack of usr/pwd param

RUBY_SCRIPT="data = YAML::load(STDIN.read); puts"
DB_HOST="`cat config/database.yml | ruby -ryaml -e "$RUBY_SCRIPT data['${DEPLOY_ENV}']['host']"`"
DB_NAME="`cat config/database.yml | ruby -ryaml -e "$RUBY_SCRIPT data['${DEPLOY_ENV}']['database']"`"
DB_USER="`cat config/database.yml | ruby -ryaml -e "$RUBY_SCRIPT data['${DEPLOY_ENV}']['username']"`"
DB_PASS="`cat config/database.yml | ruby -ryaml -e "$RUBY_SCRIPT data['${DEPLOY_ENV}']['password']"`"

# Create database and user
echo "CREATE DATABASE ${DB_NAME};
CREATE USER ${DB_USER}@${DB_HOST} IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO ${DB_USER}@${DB_HOST} IDENTIFIED BY '${DB_PASS}';
FLUSH PRIVILEGES;" | mysql -u root -p${ROOT_PASS}

# Setup Wordpress
yes "" | bundle exec cap ${DEPLOY_ENV} wp:setup:remote

# Get data
# bundle exec cap production db:backup
cd /var/alchemy/db_backups
LATEST=$(ls -Art | tail -n 1)
sed -i 's/www\.alchemyagencies\.com/localhost/g' ${LATEST}
sed -i 's/alchemyagencies\.com/localhost/g' ${LATEST}
mysql -u root -p${ROOT_PASS} ${DB_NAME} < ${LATEST}

# Update httpd.conf
sed -i 's#DocumentRoot "/var/www/html"#DocumentRoot "/var/www/html/current"#g' /etc/httpd/conf/httpd.conf
sed -i 's#Directory "/var/www/html"#Directory "/var/www/html/current"#g' /etc/httpd/conf/httpd.conf
TMP="$(cat /etc/httpd/conf/httpd.conf | awk '/<Directory "\/var\/www\/html\/current">/,/AllowOverride None/{sub("None", "All",$0)}{print}')"
echo "$TMP" > /etc/httpd/conf/httpd.conf

# Get images
cp -r -n /var/alchemy/content/uploads/* /var/www/html/shared/content/uploads/
chmod -R 777 /var/www/html/shared/content/uploads

# Deploy, this needs to happend last otherwise things get broken
cd /var/alchemy
./deploy.rb ${DEPLOY_ENV}
