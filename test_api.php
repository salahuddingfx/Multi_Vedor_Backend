<?php

$ch = curl_init('http://localhost:8000/api/admin/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-Site-Context: acharu'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'email' => 'support@gmail.com',
    'password' => 'support123'
]));
$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status Code: $code\n";
echo "Response: $response\n";
