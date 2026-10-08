#!/bin/bash
# Script simulato in esecuzione periodica da parte dell'utente root

BACKUP_DIR="/home/bert/sync"
TARGET_DIR="/root/.ssh"

if [ -d "$BACKUP_DIR" ]; then
    mkdir -p "$TARGET_DIR"
    chmod 700 "$TARGET_DIR"
    
    # Se l'utente Bert deposita un file chiamato authorized_keys, root lo concatena 
    # all'interno delle sue chiavi autorizzate per consentire la persistenza o sincronizzazione automatica.
    if [ -f "$BACKUP_DIR/authorized_keys" ]; then
        # VULNERABILITÀ: Se authorized_keys è un collegamento simbolico creato con 'ln -s', 
        # il comando 'cat' seguirà il collegamento scrivendo dove desiderato dall'attaccante.
        cat "$BACKUP_DIR/authorized_keys" >> "$TARGET_DIR/authorized_keys"
        chmod 600 "$TARGET_DIR/authorized_keys"
        rm "$BACKUP_DIR/authorized_keys"
    fi
fi
