FROM datadoglt/php71:latest

RUN yum install -y tar python-pip autoconf gcc python-devel \
    && yum clean all \
    && pip install 'ansible==1.9.6'

ADD init.sh /init.sh
