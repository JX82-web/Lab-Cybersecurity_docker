<?php
session_start();

// Verifica reale della sessione impostata dall'exploit
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$conn = new mysqli('db', 'web_user', 'web_password_9876', 'tracking_db');
$search_result = "";

if (isset($_GET['search'])) {
    $search = $_GET['search'];
    
    // Blind SQL Injection vulnerabile
    $query = "SELECT * FROM inventory WHERE item_name = '" . $search . "'";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $search_result = "Item Status: Cataloged in secure storage.";
    } else {
        $search_result = "Item Status: Not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Internal Tracking Dashboard</title>
    <style>
        body { font-family: monospace; background-color: #050505; color: #00ff00; padding: 50px; }
        .container { max-width: 800px; margin: 0 auto; background: #111; padding: 20px; border: 1px solid #00ff00; }
        input[type="text"] { width: 70%; padding: 10px; background: #000; color: #00ff00; border: 1px solid #00ff00; }
        input[type="submit"] { padding: 10px 20px; background: #00ff00; color: #000; border: none; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Inventory Control Unit</h1>
        <p>Authenticated Session: <?php echo htmlspecialchars($_SESSION['username']); ?></p>
        
        <form method="GET" action="dashboard.php">
            <input type="text" name="search" placeholder="Item Name" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <input type="submit" value="Query">
        </form>

        <?php if (isset($_GET['search'])): ?>
            <div style="margin-top:20px; padding:10px; background:#000; border-left: 3px solid #00ff00;">
                <?php echo $search_result; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
