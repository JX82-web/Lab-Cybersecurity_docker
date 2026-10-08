FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

# Installa Apache, PHP, estensioni DB, SSH, GCC, Netcat e utility d'esplorazione avanzate
RUN apt-get update && apt-get install -y \
    apache2 \
    php \
    nano \
    vim \
    python3 \
    python3-pip \
    libc6-dev \
    iproute2 \
    procps \
    net-tools \
    php-mysql \
    mysql-client \
    openssh-server \
    gcc \
    make \
    netcat-openbsd \
    curl \
    wget \
    sudo \
    cron \
    jq \
    && rm -rf /var/lib/apt/lists/*

# Configurazione utente di sistema bert per l'accesso SSH successivo
RUN useradd -m -s /bin/bash bert && \
    echo "bert:cybersecurity" | chpasswd
RUN echo 'e606960d01f5d833bedfecfb20bb59a8' > /home/bert/user.txt && \
    chmod 600 /home/bert/user.txt && \
    chown -R bert:bert /home/bert/user.txt && \
    echo 'b0e3b69e33046c8308bf9d6bb85302e8' > /root/root.txt && \
    chown -R root:root /root/root.txt && \
    chmod 600 /root/root.txt

# Abilita l'esca per il PrivEsc tramite LD_PRELOAD in C
RUN echo "bert ALL=(root) SETENV: /usr/bin/uptime" >> /etc/sudoers

# [CORREZIONE 1] Setup directory condivisa per il Symlink Attack con i permessi corretti per Bert
RUN mkdir -p /home/bert/sync && \
    chown -R bert:bert /home/bert/sync && \
    chmod 755 /home/bert/sync

# Setup directory per il servizio SSH
RUN mkdir -p /var/run/sshd

# Copia del codice sorgente dell'applicazione web
COPY src/ /var/www/html/
RUN rm /var/www/html/index.html || true
RUN chown -R www-data:www-data /var/www/html

# [CORREZIONE 2] Configurazione entrypoint: registra il cronjob di root ed avvia i servizi
RUN echo '#!/bin/bash\n\
# Registra lo script di root nel crontab per eseguirlo ogni minuto\n\
echo "* * * * * /opt/cron_root.sh >> /var/log/cron.log 2>&1" | crontab -\n\
touch /var/log/cron.log\n\
\n\
# Avvia i demoni di background\n\
service cron start\n\
service ssh start\n\
\n\
# Esegue Apache in primo piano\n\
source /etc/apache2/envvars && exec apache2 -DFOREGROUND' > /usr/local/bin/entrypoint.sh && \
    chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80 22

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
