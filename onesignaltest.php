<?php

$app_id = "210aaacd-2369-4cb7-99c8-8b777f3d0f23";
$rest_api_key = "wgtf5klwbubi5kchw7nngcyef";

$url = "https://onesignal.com/api/v1/notifications";

$data = [
    "app_id" => $app_id,
    "included_segments" => ["All"],
    "contents" => ["en" => "Final FIXED notification"]
];

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

// ✅ CORRECT AUTH FORMAT
$auth = base64_encode($rest_api_key . ":");

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json; charset=utf-8',
    'Authorization: Basic ' . $auth
]);

curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);

echo "HTTP Code: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
echo $response;

curl_close($ch);