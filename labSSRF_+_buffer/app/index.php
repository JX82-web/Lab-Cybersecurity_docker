<?php
$secret_key = "pwnlab123"; 

// FUNZIONE HELPER: Genera Base64 senza padding '=' (Standard JWT)
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// SE NON ESISTE IL COOKIE, IL SERVER LO RILASCIA AUTOMATICAMENTE (Utente Guest)
if (!isset($_COOKIE['auth_token'])) {
    $header_json = json_encode(["alg" => "HS256", "typ" => "JWT"]);
    $payload_json = json_encode(["username" => "guest", "role" => "user"]);
    
    $header = base64url_encode($header_json);
    $payload = base64url_encode($payload_json);
    $signature = hash_hmac('sha256', $header . '.' . $payload, $secret_key);
    
    setcookie('auth_token', "$header.$payload.$signature", time() + 3600, "/");
    $_COOKIE['auth_token'] = "$header.$payload.$signature"; // Forza il caricamento immediato
}

// VERIFICA DEL TOKEN CON FIRMA CRITTOGRAFICA
$is_admin = false;
$username_corrente = "guest";

if (isset($_COOKIE['auth_token'])) {
    $token = $_COOKIE['auth_token'];
    $parts = explode('.', $token);
    
    if (count($parts) === 3) {
        $header_b64 = $parts[0];
        $payload_b64 = $parts[1];
        $signature_inviata = $parts[2];
        
        // Il server ricalcola la firma crittografica per verificare la manipolazione
        $signature_attesa = hash_hmac('sha256', $header_b64 . '.' . $payload_b64, $secret_key);
        
        if (hash_equals($signature_attesa, $signature_inviata)) {
            $payload = base64_decode($payload_b64);
            $user_data = json_decode($payload, true);
            
            if ($user_data && isset($user_data['role'])) {
                $username_corrente = $user_data['username'] ?? 'user';
                if ($user_data['role'] === 'admin') {
                    $is_admin = true;
                }
            }
        }
    }
}

// FUNZIONALITÀ SSRF (Accessibile a tutti)
$ssrf_output = "";
if (isset($_POST['url_target'])) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $_POST['url_target']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    $ssrf_output = curl_exec($ch);
    curl_close($ch);
}

// FUNZIONALITÀ RCE / COMMAND INJECTION (Solo per Admin)
$cmd_output = "";
if ($is_admin && isset($_POST['ping_ip'])) {
    $ip = $_POST['ping_ip'];
    if (preg_match('/[ ;|&]/', $ip)) {
        $cmd_output = "❌ Carattere speciale vietato!";
    } else {
        $cmd_output = shell_exec("ping -c 1 " . $ip);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Elite Pwn & Web Lab</title>
    <style>
        body { font-family: monospace; background: #000; color: #00ff66; padding: 30px; }
        .box { border: 1px solid #00ff66; padding: 20px; max-width: 800px; margin: 20px auto; }
        input[type="text"] { background: #111; border: 1px solid #00ff66; color: #fff; padding: 8px; width: 70%; }
        input[type="submit"] { background: #00ff66; color: #000; border: none; padding: 8px 15px; font-weight: bold; cursor: pointer; }
        pre { background: #111; padding: 10px; border: 1px dashed #00ff66; color: #ff00ff; white-space: pre-wrap; }
        .status { text-align: center; color: #fff; font-size: 1.2em; }
    </style>
</head>
<body>
    <div class="status">
        Identificato come: <strong><?php echo htmlspecialchars($username_corrente); ?></strong> 
        (Ruolo: <?php echo $is_admin ? "👑 Admin" : "👤 User"; ?>)
    </div>

    <div class="box">
        <h2>📡 Network Portal (Vettore SSRF)</h2>
        <form method="POST">
            <input type="text" name="url_target" placeholder="http://...">
            <input type="submit" value="Interroga">
        </form>
        <?php if (!empty($ssrf_output)): ?><pre><?php echo htmlspecialchars($ssrf_output); ?></pre><?php endif; ?>
    </div>

    <?php if ($is_admin): ?>
    <div class="box" style="border-color: #ff00ff; color: #ff00ff;">
        <h2>👑 Controllo Diagnostica (Command Injection -> RCE)</h2>
        <form method="POST">
            <input type="text" name="ping_ip" placeholder="127.0.0.1" style="border-color: #ff00ff;">
            <input type="submit" value="Esegui" style="background: #ff00ff; color: #000;">
        </form>
        <?php if (!empty($cmd_output)): ?><pre style="border-color: #ff00ff行业"><?php echo htmlspecialchars($cmd_output); ?></pre><?php endif; ?>
    </div>
    <?php endif; ?>
</body>
</html>
