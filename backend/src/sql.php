<?php
$host = 'mysql';
$user = 'root';
$pass = 'root';
 
$conn = mysqli_connect($host, $user, $pass,'KylinAirdrop');
if (!$conn) {
    exit('Connection failed: '.mysqli_connect_error().PHP_EOL);
}

?>
 