FROM datadoglt/php70:latest

RUN yum install -y tar python-pip gcc python-devel

RUN pip install ansible

RUN yum remove -y gcc && yum clean all

ADD init.sh /init.sh
