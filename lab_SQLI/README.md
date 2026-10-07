# CryptoPortal - Laboratorio CTF Vulnerabile (Stile HTB)

Benvenuto su **CryptoPortal**, un laboratorio CTF multi-fase avanzato (stile Jeopardy) progettato per simulare uno scenario reale di penetration testing. Questo ambiente copre vulnerabilità delle applicazioni web, movimento laterale e post-exploitation su sistemi Linux.

## 🔗 Panoramica della Catena di Attacco
1. **Accesso Iniziale**: Sfruttamento di una vulnerabilità di **Time-Based Blind SQL Injection** sul pannello di login per estrarre gli hash delle password.
2. **Infiltrazione**: Caricamento di un file arbitrario tramite **Zip Slip / Path Traversal** per ottenere l'esecuzione di comandi remoti (RCE).
3. **Movimento Laterale**: Ispezione del database e cracking della password per passare dall'utente del server web (`www-data`) all'utente locale `michael`.
4. **Privilege Escalation**: Sfruttamento di una configurazione errata delle **Linux Capabilities** (`cap_setuid+ep`) su Python 3 per ottenere una shell di root.

---

## 🛠️ Configurazione e Avvio con Docker Compose

L'intero laboratorio è orchestrato tramite Docker Compose per garantire una segregazione di rete realistica tra l'applicazione Web e il database MySQL.

### Prerequisiti
Assicurati di avere installato sul tuo sistema:
- Docker
- Docker Compose

### Struttura dei File Richiesti
Prima di avviare, assicurati che la directory del tuo progetto contenga i seguenti file:
- `Dockerfile` (Configurazione del server web Apache, pacchetti e Linux Capabilities)
- `docker-compose.yml` (Orchestruzione dei container e della rete `172.18.0.0/16`)
- `index.php` & `admin.php` (I sorgenti dell'applicazione web vulnerabile)
- `init.sql` (Lo script di inizializzazione del database con le tabelle e gli hash)
- `entrypoint.sh` (Lo script di avvio dei servizi interni del container)

### Comando di Avvio
Per compilare le immagini e avviare l'infrastruttura del laboratorio in background, esegui il seguente comando nel tuo terminale:

```bash
docker-compose up --build -d
```

### Verifica dello Stato dei Container
Puoi verificare che entrambi i container (`ctf_sqli_web` e `ctf_sqli_db`) siano avviati correttamente e registrati sulla rete corretta eseguendo:

```bash
docker ps
```

L'applicazione web risponderà sulla porta locale `80` dell'host e sarà raggiungibile all'indirizzo `http://localhost`. Internamente alla rete Docker, il server web risponderà all'indirizzo IP statico `172.18.0.3`.

### Arresto del Laboratorio
Per spegnere l'ambiente e rimuovere i container e le reti isolate create, utilizza il comando:

```bash
docker-compose down
```

---

## 🚩 Flag da Trovare
- **User Flag**: Posizionata in `/home/michael/user.txt`
- **Root Flag**: Posizionata in `/root/root.txt`

---

## 📖 Guida alla Risoluzione (Spoiler!)

### Passo 1: Blind SQL Injection e Bypass dell'Autenticazione
La pagina di login (`index.php`) concatena direttamente l'input dell'utente all'interno della query SQL:
```php
$query = "SELECT id, username, password FROM users WHERE username = '$username'";
```
Dato che gli errori del database vengono nascosti dall'applicazione, è necessario utilizzare un approccio **Time-Based Blind SQLi** (manualmente o tramite strumenti come `sqlmap`) per fare il dump della tabella `users` e recuperare l'hash MD5 dell'amministratore.
- **Password dell'Admin**: `adminsec2026`

### Passo 2: Da Zip Slip a RCE
Una volta effettuato l'accesso a `admin.php`, la funzionalità di caricamento dei plugin è vulnerabile a **Zip Slip**. Lo script di estrazione non sanitizza i percorsi dei file contenuti nell'archivio ZIP:
```php
$destination = $target_dir . $filename;
file_put_contents($destination, $file_content);
```
Un attaccante può creare un archivio `.zip` modificato contenente un percorso relativo o un file PHP rinominato direttamente (es. `malicius.php`). Utilizzando una named pipe (`mkfifo`) o l'interpretazione diretta all'interno del codice per aggirare i vincoli di rete, si ottiene una reverse shell con i privilegi dell'utente `www-data`.

### Passo 3: Movimento Laterale (`www-data` ➡️ `michael`)
Una volta ottenuta la shell nel container, ispezionando le configurazioni dell'applicazione o interrogando il database MySQL locale si scopre una seconda tabella chiamata `internal_profiles`.
- Estrai l'hash SHA-256 dell'utente `michael`.
- Effettua il cracking dell'hash per scoprire la password in chiaro: `michael123`.
- Esegui il cambio utente nel terminale: `su michael`.
- **Cattura la User Flag** in `/home/michael/user.txt`.

### Passo 4: Privilege Escalation (`michael` ➡️ `root`)
Effettuando l'enumerazione delle misconfigurazioni di sistema tramite il comando `getcap -r / 2>/dev/null`, emerge una capability pericolosa assegnata al binario di Python:
```bash
/usr/bin/python3.x = cap_setuid+ep
```
L'utente `michael` può abusare di questa capability per forzare l'UID del processo a `0` (root) e lanciare una shell interattiva con massimi privilegi:
```bash
python3 -c 'import os; os.setuid(0); os.system("/bin/bash")'
```
- **Cattura la Root Flag** in `/root/root.txt`.

---

## 🔒 Mitigazione e Correzione delle Falla
1. **SQLi**: Implementare i **Prepared Statements** (Query Preparate) tramite PDO o MySQLi parametrizzato per separare la logica della query dai dati dell'utente.
2. **Zip Slip**: Sanitizzare rigorosamente i percorsi dei file estratti dagli archivi usando la funzione `basename()` e verificare che il percorso finale risolto rimanga all'interno della directory di destinazione prevista tramite `realpath()`.
3. **Capabilities**: Limitare rigidamente i privilegi di `cap_setuid` sugli interpreti di scripting. Se Python richiede capabilities specifiche, limitarne l'accesso ai soli utenti autorizzati o rimuovere del tutto la capability se non strettamente necessaria.

