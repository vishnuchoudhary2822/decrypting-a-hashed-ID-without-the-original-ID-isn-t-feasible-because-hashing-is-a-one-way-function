<?php
// Base62 characters
const BASE62 = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

// Convert binary data to Base62
function base62Encode($data) {
    $base62 = BASE62;
    $value = 0;
    $length = strlen($data);
    for ($i = 0; $i < $length; $i++) {
        $value = ($value << 8) + ord($data[$i]);
    }
    $result = '';
    while ($value > 0) {
        $result = $base62[$value % 62] . $result;
        $value = intdiv($value, 62);
    }
    return $result;
}

// Convert Base62 to binary data
function base62Decode($data) {
    $base62 = BASE62;
    $value = 0;
    $length = strlen($data);
    for ($i = 0; $i < $length; $i++) {
        $value = $value * 62 + strpos($base62, $data[$i]);
    }
    $result = '';
    while ($value > 0) {
        $result = chr($value & 0xff) . $result;
        $value >>= 8;
    }
    return $result;
}

function encryptId($id, $secretKey) {
    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    $iv = openssl_random_pseudo_bytes($ivLength);
    if ($iv === false) {
        echo "Failed to generate IV\n";
        return '';
    }

    $encrypted = openssl_encrypt($id, 'aes-256-cbc', $secretKey, OPENSSL_RAW_DATA, $iv);
    if ($encrypted === false) {
        echo "Encryption failed\n";
        return '';
    }

    $encoded = base62Encode($iv . $encrypted);
    if ($encoded === '') {
        echo "Base62 encoding failed\n";
    }
    return $encoded;
}

function decryptId($encryptedId, $secretKey) {
    $data = base62Decode($encryptedId);
    if ($data === '') {
        echo "Base62 decoding failed\n";
        return '';
    }

    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($data, 0, $ivLength);
    $encrypted = substr($data, $ivLength);

    $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $secretKey, OPENSSL_RAW_DATA, $iv);
    if ($decrypted === false) {
        echo "Decryption failed\n";
        return '';
    }
    return $decrypted;
}

// Example usage
$secretKey = "37112f4a0dd5cd8aee773d";
$id = "12345"; // Using a string for simplicity

// Encrypt the ID
$encryptedId = encryptId($id, $secretKey);
echo "Encrypted ID: " . $encryptedId . "\n";
echo "Length of Encrypted ID: " . strlen($encryptedId) . "\n";

// Decrypt the ID
$decryptedId = decryptId($encryptedId, $secretKey);
echo "Decrypted ID: " . $decryptedId . "\n";
?>
