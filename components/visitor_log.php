<?php
// include_once 'db_config.php';

// Cookie name
$cookie_name = "visitor_id";

// Check if visitor session cookie exists
if(!isset($_COOKIE[$cookie_name])) {

    // Generate unique visitor ID
    $visitor_id = uniqid('visitor_', true);

    // Set session cookie (expires when browser closes)
    setcookie($cookie_name, $visitor_id, 0, "/"); // 0 = session cookie

    // Get visitor info
    $ip = $_SERVER['REMOTE_ADDR'];

    // Convert local IPv6 loopback to IPv4 for local testing
    if ($ip === '::1') {
        $ip = '127.0.0.1';
    }

    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $visit_time = date('Y-m-d H:i:s');

    // Prepare SQL statement
    $stmt = $conn->prepare("INSERT INTO visitors_log (visitor_id, ip_address, user_agent, visit_time) VALUES (?, ?, ?, ?)");

    // Check for prepare errors
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
    } else {
        $stmt->bind_param("ssss", $visitor_id, $ip, $user_agent, $visit_time);

        // Execute and check for errors
        if(!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
        }

        $stmt->close();
    }
}
?>