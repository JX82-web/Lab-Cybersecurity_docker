<?php
$db_host = 'sqli_db';
$db_user = 'web_user';
$db_pass = 'WebAppPasswordDB_991!';
$db_name = 'ctf_labs';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}
?>
