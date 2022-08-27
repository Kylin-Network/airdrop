<?php
$host = 'mysql';
$user = 'dummy';
$pass = 'dummy';
 
$conn = mysqli_connect($host, $user, $pass);
if (!$conn) {
    exit('Connection failed: '.mysqli_connect_error().PHP_EOL);
}
 
$data = json_decode(file_get_contents('php://input'), true);
echo $data["tokenHolder"];
echo $data["SubstrateAddress"];
echo $data["AutoFinalSignature"];

?>