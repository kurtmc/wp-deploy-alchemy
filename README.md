Development environment
=======================

### Build
```
docker build -t alchemy-external-docker .
```

### Run
```
docker run -p 80:80 -v $(pwd):/var/alchemy -i -t alchemy-external-docker /var/alchemy/start.sh
```
