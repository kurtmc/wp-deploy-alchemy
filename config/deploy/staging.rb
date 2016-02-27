############################################
# Setup Server
############################################

set :stage, :staging
set :stage_url, "http://localhost:8080"
server "172.17.0.2", user: "alchemy", roles: %w{web app db}
set :deploy_to, "/var/www/html"

############################################
# Setup Git
############################################

set :branch, "master"

############################################
# Extra Settings
############################################

#specify extra ssh options:

#set :ssh_options, {
#    auth_methods: %w(password),
#    password: 'password',
#    user: 'username',
#}

#specify a specific temp dir if user is jailed to home
#set :tmp_dir, "/path/to/custom/tmp"
