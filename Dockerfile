FROM centos:centos7

ADD ./ /var/alchemy

RUN cd /var/alchemy; ./init-dev.sh
