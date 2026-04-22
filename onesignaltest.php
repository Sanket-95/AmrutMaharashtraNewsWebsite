<?php

echo "=== OneSignal Hostinger Test ===\n\n";

// 🔐 Replace with your actual credentials
$app_id = "210aaacd-2369-4cb7-99c8-8b777f3d0f23";
$rest_api_key = "os_v2_app_eefkvtjdnfglpgoirn3x6pipeocwslvuyddu4nmkurczmv27etm77ernq5hsd6k3vtjkolbex2stcqdvaxv4eidno66k55bq5zqoyta";

// API URL
$url = "https://onesignal.com/api/v1/notifications";

// Payload
$data = [
    "app_id" => $app_id,
    "included_segments" => ["All"],
    "contents" => ["en" => "Test notification from Hostinger server"]
];

// Initialize cURL
$ch = curl_init();

// Set options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

// ✅ Correct headers (IMPORTANT FIX)
$headers = [
    'Content-Type: application/json; charset=utf-8',
    'Authorization: Key ' . $rest_api_key
];

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// ✅ Force HTTP/1.1 (fix for shared hosting issues)
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

// Timeouts
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

// Debug mode
curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLINFO_HEADER_OUT, true);

// ✅ SSL Fix (Production + fallback)
$ca_cert = __DIR__ . '/cacert.pem';

if (file_exists($ca_cert)) {
    curl_setopt($ch, CURLOPT_CAINFO, $ca_cert);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
} else {
    // fallback (only if cert not available)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    echo "⚠️ Warning: SSL verification disabled (add cacert.pem for production)\n\n";
}

// Execute request
$response = curl_exec($ch);

// Capture info
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

// Output
echo "HTTP Code: " . $http_code . "\n\n";

if ($error) {
    echo "❌ cURL Error:\n" . $error . "\n\n";
} else {
    echo "✅ Response:\n" . $response . "\n\n";
}

// Show sent headers (debug)
echo "=== Request Info ===\n";
print_r(curl_getinfo($ch));

curl_close($ch);

echo "\n=== Test Completed ===\n";