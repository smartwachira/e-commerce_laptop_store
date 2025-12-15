<?php 

/**
 * Demo file: JWT Generation Logic
 * This file is for educational purposes to demostrate how JSON Web Tokens
 * are signed and structured. In a production environment, the  SECRET_KEY
 * would be stored in environment variable, not hardcoded here
 */
header("Content-Type: application/json");

// 1. Simulate  a User Login
$user_id = 1;
$email = "test@student.com";

// 2. Create the Header (Algorithm & Type)
$header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);

// 3. Create the Payload (Data)

$payload = json_encode([
    'user_id' => $user_id,
    'email' => $email,
    'exp' => time() +  3600

]);

// 4. Encode to Base64Url (Helper Function)
function base64UrlEncode($data) {
    return str_replace(['+','/','='], ['-','_',''], base64_encode($data));
}

$base64UrlHeader = base64UrlEncode($header);
$base64UrlPayload = base64UrlEncode($payload);

//5. Create the Signature (the security part)

$secret_key = "KCA_UNIVERSITY_SECRET_KEY";
$signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret_key, true);
$base64UrlSignature = base64UrlEncode($signature);

//6. Combine them
$jwt = "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";

echo json_encode([
    "status" => "success",
    "token" => $jwt
]);
?>

