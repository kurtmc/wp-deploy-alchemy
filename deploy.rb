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
    exec_shell("ssh #{user}@#{address} 'source ~/.bash_profile; cd /var/www/html/current; ./pull-json.rb'")
    exec_shell("scp cron-jobs/* root@#{address}:/etc/cron.hourly/")
end

def set(*args)
end

exec_shell("bundle exec cap #{environment} deploy")

require "./config/deploy/#{environment}.rb"
