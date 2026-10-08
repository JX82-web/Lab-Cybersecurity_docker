-- Creazione del database se non esiste (sebbene già gestito dal compose)
CREATE DATABASE IF NOT EXISTS ctf_labs;
USE ctf_labs;

-- --------------------------------------------------
-- Tabella 1: Utenti del Portale Web (Target della Blind SQLi)
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(32) NOT NULL -- Hash MD5
);

-- Inserimento dell'utente Amministratore del sito
-- Password in chiaro: adminsec2026
-- Hash MD5: 9c96898d9cb6100c6d594b2a4bf74b9d
INSERT INTO users (username, password) VALUES ('admin', '07d10604216a46ce7439b32cfa7bcdd6');


-- --------------------------------------------------
-- Tabella 2: Dati Interni (Target della Post-Exploitation)
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS internal_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20) NOT NULL,
    username VARCHAR(50) NOT NULL,
    password_hash VARCHAR(64) NOT NULL -- Hash SHA-256
);

-- Inserimento delle credenziali di sistema di michael
-- Password in chiaro: michael123
-- Hash SHA-256: 59c63c3ca16f31ca11fc7df0bf0a13dd2f7cb72d9e0374e2d31b9d4e5f2a99d9
INSERT INTO internal_profiles (username, employee_id, password_hash) 
VALUES ('michael', 'EMP-8821', 'dba68c802200b20277107d7eb0d86b125a0d053ed9b8f05b852a39b2e1e61887');
