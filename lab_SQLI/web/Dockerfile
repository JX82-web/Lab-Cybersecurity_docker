FROM php:8.2-apache

# --------------------------------------------------
# 1. Installazione Pacchetti, Utility di Rete e Recon
# --------------------------------------------------
RUN apt-get update && apt-get install -y \
    default-mysql-client \
    libzip-dev \
    ncat \
    iputils-ping \
    openssh-server \
    zip \
    unzip \
    jq \
    python3 \
    libcap2-bin \
    sed \
    gawk \
    net-tools \
    iproute2 \
    curl \
    wget \
    socat \
    procps \
    lsof \
    util-linux \
    nano \
    vim-tiny \
    # Installiamo sia l'estensione zip che mysqli necessaria per PHP
    && docker-php-ext-install zip mysqli \
    && rm -rf /var/lib/apt/lists/*

# --------------------------------------------------
# 2. Creazione dell'utente di sistema michael
# --------------------------------------------------
RUN useradd -m -s /bin/bash michael && \
    echo "michael:michael123" | chpasswd

# --------------------------------------------------
# 3. Configurazione delle Flag (Stile HTB)
# --------------------------------------------------
RUN echo "HTB{311nd_5ql1_t0_z1p_5l1p_m1ch43l}" > /home/michael/user.txt && \
    chown michael:michael /home/michael/user.txt && \
    chmod 600 /home/michael/user.txt

RUN echo "HTB{c4p5_53tu1d_pwn3d_r00t_4cce55}" > /root/root.txt && \
    chown root:root /root/root.txt && \
    chmod 600 /root/root.txt

# --------------------------------------------------
# 4. Configurazione delle Linux Capabilities (Vulnerabilità)
# --------------------------------------------------
RUN REAL_PYTHON=$(readlink -f /usr/bin/python3) && setcap cap_setuid+ep "$REAL_PYTHON"

# --------------------------------------------------
# 5. Creazione directory e impostazione dei permessi Web
# --------------------------------------------------
RUN mkdir -p /var/www/html/uploads && \
    chown -R www-data:www-data /var/www/html && \
    chmod 755 /var/www/html && \
    chmod 777 /var/www/html/uploads

COPY entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh

EXPOSE 80 22

CMD ["/entrypoint.sh"]
