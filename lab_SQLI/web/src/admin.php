<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$status = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["zipfile"])) {
    $target_dir = "/var/www/html/uploads/";
    $target_file = $target_dir . basename($_FILES["zipfile"]["name"]);
    $ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if ($ext !== "zip") {
        $status = "Errore: Sono consentiti solo file ZIP.";
    } else {
        if (move_uploaded_file($_FILES["zipfile"]["tmp_name"], $target_file)) {
            $zip = new ZipArchive;
            if ($zip->open($target_file) === TRUE) {
                
                
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $filename = $zip->getNameIndex($i);
                    $destination = $target_dir . $filename;
                    $dir = dirname($destination);
                    
                    
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    
                    
                    $file_content = $zip->getFromIndex($i);
                    if ($file_content !== false) {
                        file_put_contents($destination, $file_content);
                    }
                }
                
                $zip->close();
                $status = "Plugin caricato ed estratto con successo!";
            } else {
                $status = "Errore nell'apertura dello ZIP.";
            }
            
            if (file_exists($target_file)) {
                unlink($target_file);
            }
        } else {
            $status = "Errore nel caricamento del file.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Pannello Amministratore</title>
</head>
<body>
    <h2>Pannello Controllo Plugin</h2>
    <?php if(!empty($status)) echo "<p style='font-weight:bold;'>Stato: $status</p>"; ?>
    <form method="POST" action="" enctype="multipart/form-data">
        <input type="file" name="zipfile" required><br><br>
        <button type="submit">Installa (.zip)</button>
    </form>
    <br><a href="logout.php">Logout</a>
</body>
</html>

