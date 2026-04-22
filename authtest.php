<?php
$ch = curl_init("https://httpbin.org/headers");

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer TEST123'
]);

$response = curl_exec($ch);
echo $response;
curl_close($ch);