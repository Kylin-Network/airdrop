<?php
require_once 'PointMathGMP.class.php';
require_once 'SECp256k1.class.php';
require_once 'Signature.class.php';
require_once 'Keccak.php';
use kornrunner\Keccak;

function personal_ecRecover($msg, $signed) {
    $personal_prefix_msg = "\x19Ethereum Signed Message:\n". strlen($msg). $msg;
    $hex = keccak256($personal_prefix_msg);
    return ecRecover($hex, $signed);
}

function ecRecover($hex, $signed) {
    
    $rHex   = substr($signed, 2, 64);
    $sHex   = substr($signed, 66, 64);
    $vValue = hexdec(substr($signed, 130, 2));
    $messageHex       = substr($hex, 2);
    $messageByteArray = unpack('C*', hex2bin($messageHex));
    $messageGmp       = gmp_init("0x" . $messageHex);
    $r = $rHex;		//hex string without 0x
    $s = $sHex; 	//hex string without 0x
    $v = $vValue; 	//27 or 28

    //with hex2bin it gives the same byte array as the javascript
    try {
    $rByteArray = unpack('C*', hex2bin($r));
    $sByteArray = unpack('C*', hex2bin($s));
    $rGmp = gmp_init("0x" . $r);
    $sGmp = gmp_init("0x" . $s);
    } catch (Exception $e) {
        echo 'ERROR: ',  $e->getMessage(), "\n";
        exit();
    } catch (Error $err) {
        echo 'ERROR: Invalid signature';
        exit();
    }

    if ($v != 27 && $v != 28) {
        $v += 27;
    }

    $recovery = $v - 27;
    if ($recovery !== 0 && $recovery !== 1) {
        throw new Exception('Invalid signature v value');
    }

    $publicKey = Signature::recoverPublicKey($rGmp, $sGmp, $messageGmp, $recovery);
    $publicKeyString = $publicKey["x"] . $publicKey["y"];

    return '0x'. substr(keccak256(hex2bin($publicKeyString)), -40);
}

function strToHex($string)
{
    $hex = unpack('H*', $string);
    return '0x' . array_shift($hex);
}

function keccak256($str) {
    return '0x'. Keccak::hash($str, 256);
}

class EthereumValidator
{
    public function isAddress(string $address): bool
    {
        // See: https://github.com/ethereum/web3.js/blob/7935e5f/lib/utils/utils.js#L415
        if ($this->matchesPattern($address)) {
            return $this->isAllSameCaps($address) ?: $this->isValidChecksum($address);
        }

        return false;
    }

    protected function matchesPattern(string $address): int
    {
        return preg_match('/^(0x)?[0-9a-f]{40}$/i', $address);
    }

    protected function isAllSameCaps(string $address): bool
    {
        return preg_match('/^(0x)?[0-9a-f]{40}$/', $address) || preg_match('/^(0x)?[0-9A-F]{40}$/', $address);
    }

    protected function isValidChecksum($address)
    {
        $address = str_replace('0x', '', $address);
        $hash = Keccak::hash(strtolower($address), 256);

        // See: https://github.com/web3j/web3j/pull/134/files#diff-db8702981afff54d3de6a913f13b7be4R42
        for ($i = 0; $i < 40; $i++ ) {
            if (ctype_alpha($address[$i])) {
                // Each uppercase letter should correlate with a first bit of 1 in the hash char with the same index,
                // and each lowercase letter with a 0 bit.
                $charInt = intval($hash[$i], 16);

                if ((ctype_upper($address[$i]) && $charInt <= 7) || (ctype_lower($address[$i]) && $charInt > 7)) {
                    return false;
                }
            }
        }

        return true;
    }
}
