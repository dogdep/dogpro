FROM datadoglt/php56:latest

RUN yum install -y \
        ansible \
        tar \
    && yum clean all

ADD init.sh /init.sh
