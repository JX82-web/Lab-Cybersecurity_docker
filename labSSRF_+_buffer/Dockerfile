FROM php:8.2-apache

# --------------------------------------------------
# Pacchetti necessari
# --------------------------------------------------
RUN apt-get update && apt-get install -y \
    curl \
    procps \
    netcat-traditional \
    ncat \
    gcc \
    nano \
    vim \
    python3 \
    python3-pwntools \
    python3.13-venv \
    ltrace \
    gdb \
    net-tools \
    iproute2 \
    cron \
    util-linux \
    tar \
    sudo \
    passwd \
    login \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# --------------------------------------------------
# Utente CTF
# --------------------------------------------------
RUN useradd -m -s /bin/bash user

# --------------------------------------------------
# Flag
# --------------------------------------------------
RUN printf '%s\n' \
    'er{W3B_SSRF_M4N1PUL4T10N_D0N3:2a6b991448f7e13c3b432426cb77ab9d}' \
    > /home/user/user.txt \
    && chmod 600 /home/user/user.txt

RUN printf '%s\n' \
    'root{PWN_0V3RFL0W_R00T_7721:0d2a458078a69e3d18006e470883db9d}' \
    > /root/root.txt \
    && chmod 600 /root/root.txt

RUN chown -R user:user /home/user/user.txt

# --------------------------------------------------
# SUID Buffer Overflow (Codice C Standard Pulito)
# --------------------------------------------------
RUN cat > /tmp/vuln.c << '_EOF'
#include <stdio.h>
#include <unistd.h>
#include <string.h>
#include <stdlib.h>

void vuln(void)
{
    char buffer[64];

    printf("Inserisci il payload: ");
    fflush(stdout);

    read(STDIN_FILENO, buffer, 256);
}

int main(int argc, char *argv[])
{
    setgid(0);
    setuid(0);

    vuln();

    return 0;
}
_EOF

RUN gcc /tmp/vuln.c \
    -o /usr/local/bin/pwn_service \
    -fno-stack-protector \
    -z execstack \
    -no-pie \
    && rm -f /tmp/vuln.c

RUN chown root:root /usr/local/bin/pwn_service \
    && chmod 4755 /usr/local/bin/pwn_service

# --------------------------------------------------
# Configurazione di Supervisor (Gestore dei Servizi)
# --------------------------------------------------
RUN mkdir -p /etc/supervisor/conf.d
RUN cat > /etc/supervisor/conf.d/pwn.conf << '_EOF'
[program:pwn_service]
command=ncat -l 0.0.0.0 9001 --exec /usr/local/bin/pwn_service
autostart=true
autorestart=true
stderr_logfile=/var/log/pwn_err.log
stdout_logfile=/var/log/pwn_out.log
_EOF

# --------------------------------------------------
# Directory secret / cron
# --------------------------------------------------
RUN mkdir -p /home/user/secret

RUN cat > /home/user/secret/.secret.sh << '_EOF'
#!/bin/bash
/bin/tar -cf "/home/user/backup_$(date +%Y%m%d_%H%M%S).tar" \
    -C /home/user/secret .
_EOF

RUN chown www-data:user /home/user/secret \
    && chmod 755 /home/user/secret/ \
    && chown www-data:user /home/user/secret/.secret.sh \
    && chmod +x /home/user/secret/.secret.sh \
    && sed -i '/pam_loginuid.so/s/^/#/' /etc/pam.d/cron  

RUN echo '*/2 * * * * user /home/user/secret/.secret.sh' > /etc/cron.d/ctf-backup \
    && chmod 644 /etc/cron.d/ctf-backup \
    && chmod 755 /home/user

# --------------------------------------------------
# Entry point
# --------------------------------------------------
RUN cat > /usr/local/bin/ctf-entrypoint.sh << '_EOF'
#!/bin/bash
set -eu

# Avvia il servizio cron
cron

# Avvia il vero gestore servizi Supervisor in background
supervisord -c /etc/supervisor/supervisord.conf &

# Apache in foreground (PID 1)
exec apache2-foreground
_EOF

RUN chmod 755 /usr/local/bin/ctf-entrypoint.sh

# --------------------------------------------------
# Apache
# --------------------------------------------------
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/ctf-entrypoint.sh"]
