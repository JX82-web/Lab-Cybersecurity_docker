<?php
session_start();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>
<body>
    <h2>Dashboard Pubblica</h2>
    <?php if(isset($_SESSION['user'])): ?>
        <p>Loggato come: <strong><?php echo htmlspecialchars($_SESSION['user']); ?></strong></p>
        <a href="admin.php">Vai al Pannello Admin</a> | <a href="logout.php">Logout</a>
    <?php else: ?>
        <p>Navigazione come Ospite. <a href="index.php">Accedi come Amministratore</a>.</p>
    <?php endif; ?>
</body>
</html>
