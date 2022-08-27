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

require_once './php-ecrecover/ecrecover_helper.php';
$msg = 'Hello!!';
$signed = '0x7b87a3c4dd63bee43d4c880391bb0aaaf210c12356406152a30edc424b9c4de62cc64a266b6faf22691a36489651f1cbf7dee1f028ba24b7d48e5552eac4c93f1b';
echo personal_ecRecover($msg, $signed), "\n";


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