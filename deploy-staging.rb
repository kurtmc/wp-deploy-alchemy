#!/usr/bin/env ruby
#bundle exec cap staging deploy
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
end

def set(*args)
end

exec_shell("bundle exec cap staging deploy")

require './config/deploy/staging.rb'
