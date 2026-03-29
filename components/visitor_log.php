<?php
// include your DB connection
$cookie_name = "visitor_id";

if (!isset($_COOKIE[$cookie_name])) {

    $visitor_id = uniqid('visitor_', true);
    setcookie($cookie_name, $visitor_id, 0, "/");

    $ip = $_SERVER['REMOTE_ADDR'];
    if ($ip === '::1') $ip = '127.0.0.1'; // local testing

    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $visit_time = date('Y-m-d H:i:s');

    // 1️⃣ Check if IP already exists
    $stmt = $conn->prepare("SELECT country, region, city, zip FROM visitors_log WHERE ip_address=? LIMIT 1");
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $stmt->store_result();

    $country = $region = $city = $zip = '';

    if ($stmt->num_rows > 0) {
        // IP exists → fetch stored location
        $stmt->bind_result($country, $region, $city, $zip);
        $stmt->fetch();
    } else {
        // IP not in DB → call free API
        $geo_json = @file_get_contents("http://ip-api.com/json/{$ip}");
        if ($geo_json !== false) {
            $geo = json_decode($geo_json, true);
            if ($geo && isset($geo['status']) && $geo['status'] === 'success') {
                $country = $geo['country'] ?? '';
                $region  = $geo['regionName'] ?? '';
                $city    = $geo['city'] ?? '';
                $zip     = $geo['zip'] ?? '';
            }
        }
        // If API fails → country, region, city, zip remain empty
    }

    $stmt->close();

    // 2️⃣ Insert visitor info + location .
    $stmt = $conn->prepare(
        "INSERT INTO visitors_log (visitor_id, ip_address, user_agent, visit_time, country, region, city, zip) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );

    if ($stmt !== false) {
        $stmt->bind_param(
            "ssssssss",
            $visitor_id,
            $ip,
            $user_agent,
            $visit_time,
            $country,
            $region,
            $city,
            $zip
        );
        $stmt->execute();
        $stmt->close();
    } else {
        error_log("Prepare failed: " . $conn->error);
    }
}
?>