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
require_once './sql.php';
//Testing redis propose
echo "Welcome " . $user_ip_address . " total calls made " . $total_user_calls . " in " . $time_period . " seconds\n";

$data = json_decode(file_get_contents('php://input'), true);
// echo $data["tokenHolder"];
// echo $data["SubstrateAddress"];
// echo $data["AutoFinalSignature"];
if (($data["tokenHolder"]!='')&&($data["SubstrateAddress"]!='')&&($data["AutoFinalSignature"]!='')) {
    require_once './php-ecrecover/ecrecover_helper.php';
    $msg = $data["SubstrateAddress"];
    $signed = $data["AutoFinalSignature"];
    
    // performs the actual signature validation and if is valid will be inserted int 'requests' table for future processing
    if (personal_ecRecover($msg, $signed)==$data["tokenHolder"]) {
        // Escape user inputs for security
        $tokenHolder = mysqli_real_escape_string($conn, $data["tokenHolder"]);
        $SubstrateAddress = mysqli_real_escape_string($conn, $data["SubstrateAddress"]);
        $AutoFinalSignature = mysqli_real_escape_string($conn, $data["AutoFinalSignature"]);
        $datetime=date('Y-m-d H:i:s');
        // Attempt insert query execution
        $sql = "INSERT INTO requests (tokenHolder, SubstrateAddress, AutoFinalSignature, manuallysigned, ipaddress, datetime) VALUES ('$tokenHolder', '$SubstrateAddress', '$AutoFinalSignature',0,'$user_ip_address','$datetime')";
        if(mysqli_query($conn, $sql)){
                echo "Records added successfully.";
                } else{
                    echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
                }
    } else {
        echo "signature is not valid";
    }
    
} else {
    echo "invalid data submited";
    exit();
}


 

mysqli_close($conn);
?>