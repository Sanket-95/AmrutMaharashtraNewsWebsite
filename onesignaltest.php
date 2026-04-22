<?php

$app_id = "210aaacd-2369-4cb7-99c8-8b777f3d0f23";
$rest_api_key = "bvpstnckseoufjqtmy6qifha2";

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "app_id" => $app_id,
    "included_segments" => ["All"],
    "contents" => ["en" => "Final test after key reset"]
]));

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json; charset=utf-8",
    "Authorization: Basic " . $rest_api_key
]);

$response = curl_exec($ch);

echo "HTTP Code: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
echo $response;

curl_close($ch);