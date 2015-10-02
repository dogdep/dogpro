FROM datadoglt/php56:latest

RUN yum install -y \
        ansible \
        tar \
    && yum clean all

ADD init.sh /init.sh

# Set ansible dirs
RUN sed -ri "s/#?deprecation_warnings\s*=.*/deprecation_warnings=False/" /etc/ansible/ansible.cfg && \
    sed -ri "s/#?roles_path\s*=.*/roles_path=\/var\/www\/resources\/ansible\/roles/" /etc/ansible/ansible.cfg && \
    sed -ri "s/#?callback_plugins\s*=.*/callback_plugins=\/var\/www\/resources\/ansible\/callback_plugins/" /etc/ansible/ansible.cfg && \
    sed -ri "s/#?lookup_plugins\s*=.*/lookup_plugins=\/var\/www\/resources\/ansible\/lookup_plugins/" /etc/ansible/ansible.cfg && \
    sed -ri "s/#?library\s*=.*/library=\/var\/www\/resources\/ansible\/modules/" /etc/ansible/ansible.cfg && \
    sed -ri "s/#?filter_plugins\s*=.*/filter_plugins=\/var\/www\/resources\/ansible\/filter_plugins/" /etc/ansible/ansible.cfg

