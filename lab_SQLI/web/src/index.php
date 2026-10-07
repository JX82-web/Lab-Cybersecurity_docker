<?php
include('config.php');
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // VULNERABILITÀ TIME-BASED BLIND SQLi: Concatenazione diretta del parametro
    $query = "SELECT id, username, password FROM users WHERE username = '$username'";
    
    if ($conn->multi_query($query)) {
        $result = $conn->store_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (md5($password) === $row['password']) {
                $_SESSION['user'] = $row['username'];
                if ($row['username'] === 'admin') {
                    header("Location: admin.php");
                    exit();
                } else {
                    header("Location: dashboard.php");
                    exit();
                }
            } else {
                $message = "Credenziali errate.";
            }
        } else {
            $message = "Credenziali errate.";
        }
    } else {
        // Nascondiamo gli errori per forzare l'approccio Blind
        $message = "Credenziali errate.";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>CryptoPortal - Login</title>
</head>
<body>
    <h2>Accedi al portale</h2>
    <?php if(!empty($message)) echo "<p style='color:red;'>$message</p>"; ?>
    <form method="POST" action="">
        <label>Username:</label><br>
        <input type="text" name="username" required><br><br>
        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
