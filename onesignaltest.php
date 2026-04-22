<?php

echo "=== OneSignal Connectivity Test ===\n\n";

// Your credentials
$app_id = "210aaacd-2369-4cb7-99c8-8b777f3d0f23";
$rest_api_key = "os_v2_app_eefkvtjdnfglpgoirn3x6pipenop6fjui4fequ5aey3ue6rcbkqaiuzv74nddtiksotqcbbfpaamrl7cjftuezkdu7tz6u2abxyplly";

// OneSignal API URL
$url = "https://onesignal.com/api/v1/notifications";

// Payload
$data = [
    "app_id" => $app_id,
    "included_segments" => ["All"],
    "contents" => ["en" => "Test notification from Hostinger"]
];

// Initialize cURL
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

// Headers
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json; charset=utf-8",
    "Authorization: Basic $rest_api_key"
]);

// Timeout (important for debugging)
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);

// Enable verbose output
curl_setopt($ch, CURLOPT_VERBOSE, true);

// (Temporary) disable SSL check for testing
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// Execute request
$response = curl_exec($ch);

// Capture info
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

echo "HTTP Code: " . $http_code . "\n";

if ($error) {
    echo "cURL Error: " . $error . "\n";
} else {
    echo "Response:\n";
    echo $response . "\n";
}

curl_close($ch);

echo "\n=== Test Completed ===\n";

?>