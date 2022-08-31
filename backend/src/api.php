<?php
error_reporting(E_ERROR | E_PARSE);

//doing rate limit check	
$redis = new Redis();
$redis->connect('redis', 6379);

$max_calls_limit  = 3;
$time_period      = 10;
$total_user_calls = 20;

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

//Testing rate-limit 
//echo "Debug: " . $user_ip_address . " total calls made " . $total_user_calls . " in " . $time_period . " seconds\n";

require_once './sql.php';
require './vendor/autoload.php';

$data = json_decode(file_get_contents('php://input'), true);
// echo $data["tokenHolder"];
// echo $data["SubstrateAddress"];
// echo $data["AutoFinalSignature"];

if (isset($data["tokenHolder"]) && isset($data["SubstrateAddress"]) && isset($data["AutoFinalSignature"])&& !empty($data["tokenHolder"]) && !empty($data["SubstrateAddress"]) && !empty($data["AutoFinalSignature"])) {
    require_once './php-ecrecover/ecrecover_helper.php';
    $msg = $data["SubstrateAddress"];
    $signed = $data["AutoFinalSignature"];
    $manuallysigned = $data["manuallysigned"];
      
    // Escape user inputs for security
    $tokenHolder = mysqli_real_escape_string($conn, $data["tokenHolder"]);
    $SubstrateAddress = mysqli_real_escape_string($conn, $data["SubstrateAddress"]);
    $AutoFinalSignature = mysqli_real_escape_string($conn, $data["AutoFinalSignature"]);
    
    $EthereumValidator = new EthereumValidator();

    if (!$EthereumValidator->isAddress($tokenHolder)) {
        echo "Token Holder address is invalid.";
        exit();
    }

    // performs the actual signature validation and if is valid will be inserted int 'requests' table for future processing
    try {
    if (personal_ecRecover($msg, $signed)==$data["tokenHolder"]) {
  
        
        //checking if the holder did not made the airdrop request before
        $check1 = mysqli_query($conn, "SELECT * FROM requests WHERE tokenHolder='".$tokenHolder."'");
        //checking the presence the in original KYL holder list
        $check2 = mysqli_query($conn, "SELECT * FROM holders WHERE HolderAddress='".$tokenHolder."'");

        if(mysqli_num_rows($check1) > 0){
            echo "ERROR: request already exists";
            exit();            
        }elseif (mysqli_num_rows($check2) == 0) {
            echo "Holder account not in the initial holders list. Checking for balance... is: ";
            
            $client = new \Etherscan\Client('8GXIRKHM77K82VRF68XF5MAD455CUCT3RV');
            $balance=$client->api('account')->tokenBalance('0x67B6D479c7bB412C54e03dCA8E1Bc6740ce6b99C',$tokenHolder);
            echo $balance['result'];
            if ($balance['result']==0) {
             
                exit();
            } 
         
        }
        
        // Attempt insert query execution
        $datetime=date('Y-m-d H:i:s');
        $sql = "INSERT INTO requests (tokenHolder, SubstrateAddress, AutoFinalSignature, manuallysigned, ipaddress, datetime) VALUES ('$tokenHolder', '$SubstrateAddress', '$AutoFinalSignature','$manuallysigned','$user_ip_address','$datetime')";
        if(mysqli_query($conn, $sql)){
                echo "Valid request! Thank you!";
                } else{
                    echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
                }
    } else {
        echo "ERROR: signature submitted is not valid";
    }
    } catch (Exception $e) {
        echo '"ERROR: Caught exception: ',  $e->getMessage(), "\n";
    }

} else {
    echo "ERROR: invalid data submited";
    exit();
}


mysqli_close($conn);
?>