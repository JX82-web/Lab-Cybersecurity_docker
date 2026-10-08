CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password_hash VARCHAR(64) NOT NULL,
    role VARCHAR(20) NOT NULL
);

CREATE TABLE IF NOT EXISTS inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    location VARCHAR(100) NOT NULL
);

-- Popolamento utenti (La password di bert in chiaro è: cybersecurity)
INSERT INTO users (username, password_hash, role) VALUES 
('admin', '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', 'admin'),
('bert', '64a1e1972b663b35a8c06b453ce018251efef00925fb414217f6087f179031b8', 'developer'),
('guest', '84983c60f4da043454b423d247470f1a9a84df0e10b1eca49da2ef3f4316cd19', 'guest');

-- Elementi fittizi per popolare l'inventario della ricerca
INSERT INTO inventory (item_name, quantity, location) VALUES
('Docker Sandbox Escape PoC', 1, 'Server_Room_A'),
('SSH Backup Key Backup', 2, 'Vault_B'),
('SQLi Logs', 142, 'Local_Storage'),
('Exploit Framework v2', 1, 'Secure_Drive');
