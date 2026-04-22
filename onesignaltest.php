<?php

$app_id = "210aaacd-2369-4cb7-99c8-8b777f3d0f23";
$api_key = "os_v2_app_eefkvtjdnfglpgoirn3x6pipeocwslvuyddu4nmkurczmv27etm77ernq5hsd6k3vtjkolbex2stcqdvaxv4eidno66k55bq5zqoyta";

$url = "https://api.onesignal.com/notifications";

$data = [
    "app_id" => $app_id,
    "included_segments" => ["All"],
    "contents" => ["en" => "Amrut Maharashtra: Stay Updated with the Latest News!"],
];

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $api_key
]);

// 🔥 Critical fixes
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

// optional debug
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);

echo "HTTP Code: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
echo $response;

curl_close($ch);