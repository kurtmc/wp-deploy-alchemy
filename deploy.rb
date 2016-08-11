#!/usr/bin/env ruby

environment = ARGV[0]

unless ['staging', 'production'].include? environment
    raise "Must specify if staging or production, e.g.\n./deploy.rb staging"
end


def exec_shell(cmd)
    unless system(cmd)
        raise "Command unsuccessful"
    end
end
def server(address, *args)
    user = *args[0][:user]
    user = user[0]
    exec_shell("scp api-credentials.json #{user}@#{address}:/var/www/html/current/")
    exec_shell("scp site-configuration.json #{user}@#{address}:/var/www/html/current/")
    exec_shell("ssh #{user}@#{address} 'source ~/.bash_profile; cd /var/www/html/current; ./pull-json.rb'")
    exec_shell("ssh #{user}@#{address} 'source ~/.bash_profile; cd /var/www/html/current; php api.php'")
    exec_shell("scp cron-jobs/* root@#{address}:/etc/cron.hourly/")
    exec_shell("ssh root@#{address} 'chmod +x /etc/cron.hourly/*'")
    exec_shell("ssh #{user}@#{address} 'source ~/.bash_profile; cd /var/www/html/current; mkdir -p shared/db_backups; chmod 777 shared/db_backups'")
    exec_shell("ssh #{user}@#{address} 'source ~/.bash_profile; cp /var/www/html/current/content/plugins/wp-dbmanager/htaccess.txt /var/www/html/current/shared/db_backups/.htaccess'")
    exec_shell("ssh #{user}@#{address} 'source ~/.bash_profile; cp /var/www/html/current/content/plugins/wp-dbmanager/index.php /var/www/html/current/shared/db_backups/index.php'")
end

def set(*args)
end

exec_shell("bundle exec cap #{environment} deploy")

require "./config/deploy/#{environment}.rb"
