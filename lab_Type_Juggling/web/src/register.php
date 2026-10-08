<?php
// Forza il server a mostrare gli errori direttamente a schermo per il debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configura MySQLi per l'attivazione delle eccezioni in caso di fallimento
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$msg = "";

try {
    // Tentativo di connessione al database isolato
    $conn = new mysqli('db', 'web_user', 'web_password_9876', 'tracking_db');
} catch (Exception $e) {
    // Se il database è spento o irraggiungibile, stampa l'errore ed evita la schermata nera
    die("<span style='color:red; font-weight:bold;'>[!] ERRORE DI CONNESSIONE AL DATABASE:</span> " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $_POST['password'];
    
    if (!empty($user) && !empty($pass)) {
        $hash = hash('sha256', $pass);
        
        // Verifica la presenza dell'utente
        $check = $conn->query("SELECT id FROM users WHERE username = '$user'");
        if ($check->num_rows > 0) {
            $msg = "Errore: Questo username è già registrato.";
        } else {
            // Registrazione del profilo guest
            $conn->query("INSERT INTO users (username, password_hash, role) VALUES ('$user', '$hash', 'guest')");
            $msg = "Registrazione completata con successo! <a href='index.php' style='color:#00ff00;'>Accedi qui</a>";
        }
    } else {
        $msg = "Errore: Compila tutti i campi.";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Registrazione Portale</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #1a1a1a; color: #fff; text-align: center; padding-top: 100px; }
        .box { background: #2a2a2a; padding: 30px; display: inline-block; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.5); border: 1px solid #333; }
        input { display: block; margin: 10px auto; padding: 10px; width: 200px; border-radius: 4px; border: 1px solid #555; background: #111; color: #fff; }
        button { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        button:hover { background: #218838; }
        a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Registrazione Nuovo Utente</h2>
        <form method="POST" action="register.php">
            <input type="text" name="username" placeholder="Scegli Username" required>
            <input type="password" name="password" placeholder="Scegli Password" required>
            <button type="submit">Registrati</button>
        </form>
        <p id="info" style="color: #ffc107;"><?php echo $msg; ?></p>
        <br>
        <a href="index.php">Torna al Login</a>
    </div>
</body>
</html>
