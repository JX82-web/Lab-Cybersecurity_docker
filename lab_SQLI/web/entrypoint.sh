#!/bin/bash

mkdir -p /var/www/html/uploads
chown -R www-data:www-data /var/www/html
chmod 777 /var/www/html/uploads

# avviare il server ssh in entrypoint
service ssh start

# comando apache nativo di avvio
apache2-foreground

