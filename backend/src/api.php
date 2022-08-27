<?php
//doing rate limit check	
$redis = new Redis();
$redis->connect('redis', 6379);

$max_calls_limit  = 3;
$time_period      = 10;
$total_user_calls = 0;

if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $user_ip_address = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $user_ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $user_ip_address = $_SERVER['REMOTE_ADDR'];
}

if (!$redis->exists($user_ip_address)) {
    $redis->set($user_ip_address, 1);
    $redis->expire($user_ip_address, $time_period);
    $total_user_calls = 1;
} else {
    $redis->INCR($user_ip_address);
    $total_user_calls = $redis->get($user_ip_address);
    if ($total_user_calls > $max_calls_limit) {
        echo "User " . $user_ip_address . " limit exceeded.";
        exit();
    }
}
// end doing rate limit check

//Testing redis propose
echo "Welcome " . $user_ip_address . " total calls made " . $total_user_calls . " in " . $time_period . " seconds";

$host = 'mysql';
$user = 'dummy';
$pass = 'dummy';
 
$conn = mysqli_connect($host, $user, $pass);
if (!$conn) {
    exit('Connection failed: '.mysqli_connect_error().PHP_EOL);
}
 
$data = json_decode(file_get_contents('php://input'), true);
// echo $data["tokenHolder"];
// echo $data["SubstrateAddress"];
// echo $data["AutoFinalSignature"];

?>