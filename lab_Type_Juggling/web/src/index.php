<?php
session_start();

define('SECRET_TOKEN', '0e123456789012345678901234567890'); 
$conn = new mysqli('db', 'web_user', 'web_password_9876', 'tracking_db');
$error_msg = "";

// Parsing della richiesta JSON originale per il Type Juggling
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if (strpos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['username']) && isset($input['token'])) {
        
        // Falla di Type Juggling: se 'token' è il booleano true, il confronto == passa.
        if ($input['username'] === 'admin' && $input['token'] == SECRET_TOKEN) {
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = 'admin';
            
            header('Content-Type: application/json');
            echo json_encode(["success" => true, "redirect" => "dashboard.php"]);
            exit;
        } else {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(["success" => false, "error" => "Auth Failed"]);
            exit;
        }
    }
}

// Se l'utente è già loggato, lo manda alla dashboard
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enterprise Portal - Login</title>
    <style>
        body { font-family: sans-serif; background-color: #1a1a1a; color: #fff; text-align: center; padding-top: 100px; }
        .box { background: #2a2a2a; padding: 40px; display: inline-block; border-radius: 4px; border: 1px solid #333; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Enterprise Authentication Portal</h2>
        <p>API Authentication Required. Submit credentials via JSON endpoint.</p>
    </div>
</body>
</html>
