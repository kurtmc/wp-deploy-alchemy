Development environment
=======================

You should only need to build the container once, re-running it should re deploy
your code changes.

You will need to get a database copy and asset copy from someone who has a copy
first.

### Intial setup
copy config/database.example.yml to config/database.yml

Clone wordpress submodule
```
git submodule update --init --recursive
```

Get data:
```
wget http://...
mkdir db_backups
mv 2016*.sql db_backups/

tar xf uploads.tar.gz
mv uploads content/
chmod -R 777 content/uploads
```

Start the alchemy-internal-website docker container.


### Build
Before you build make sure you are running the alchemy-internal-website, and
it's ip address is the one in site-configuration.json.example. It will probably
be 172.17.0.2 if it's the first container that you are running.
```
docker build -t alchemy-external-docker .
```

### Run
```
docker run --name=alchemy-external --rm -p 80:80 --expose=3000 -v $(pwd):/var/alchemy -i -t alchemy-external-docker /var/alchemy/start.sh
```
