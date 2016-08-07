Development environment
=======================

You should only need to build the container once, re-running it should re deploy
your code changes.

You will need to get a database copy and asset copy from someone who has a copy
first.

### Build
```
docker build -t alchemy-external-docker .
```

### Run
```
docker run -p 80:80 -v $(pwd):/var/alchemy -i -t alchemy-external-docker /var/alchemy/start.sh
```
