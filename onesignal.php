<?php

// --------------------------------------------------------------------------------
// OneSignal Push Notification Sender
// Run via terminal: php send_notifications.php
// --------------------------------------------------------------------------------

$APP_ID  = "210aaacd-2369-4cb7-99c8-8b777f3d0f23";
$API_KEY = "os_v2_app_eefkvtjdnfglpgoirn3x6pipeocwslvuyddu4nmkurczmv27etm77ernq5hsd6k3vtjkolbex2stcqdvaxv4eidno66k55bq5zqoyta";

// -------------------------------
// DATABASE CONNECTION
// -------------------------------

// Local DB
// $db_host     = "localhost";
// $db_user     = "root";
// $db_password = "";
// $db_name     = "amrutmaharashtra";

// Server DB
$db_host     = "localhost";
$db_user     = "u153621952_Amrut1234";
$db_password = "Mahaamrut@123456789";
$db_name     = "u153621952_Mahaamrutdb";

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("❌ Database Connection Failed: " . $conn->connect_error . "\n");
}

echo "✅ Database connected successfully\n";

// -------------------------------
// STEP 1: Fetch unpublished news articles
// -------------------------------

$fetch_news_query = "
    SELECT news_id, title, summary
    FROM news_articles
    WHERE category_name IN ('Govt_Schemes', 'Tourism', 'Articles', 'Beneficiary Story')
      AND (onsignal <> 1 OR onsignal IS NULL)
      AND DATE(published_date) = CURDATE()
      AND is_approved = 1
    ORDER BY published_date ASC
    LIMIT 10
";

$result = $conn->query($fetch_news_query);

if (!$result || $result->num_rows === 0) {
    echo "❌ No unpublished news articles found\n";
    $conn->close();
    exit;
}

$news_articles = [];
while ($row = $result->fetch_assoc()) {
    $news_articles[] = $row;
}

echo "📰 Found " . count($news_articles) . " news articles to send\n";

// -------------------------------
// HELPER: Send cURL request
// -------------------------------

function sendCurlPost($url, $payload, $auth_header) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: {$auth_header}",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err  = curl_error($ch);
    curl_close($ch);

    return [
        'http_code' => $http_code,
        'response'  => $response,
        'curl_err'  => $curl_err
    ];
}

// -------------------------------
// STEP 2: Send notifications
// -------------------------------

$sent_news_ids = [];

// Try both auth formats in case one fails
$auth_formats = [
    "Key {$API_KEY}",
    "Basic {$API_KEY}",
    "Bearer {$API_KEY}",
];

foreach ($news_articles as $index => $article) {
    $news_id  = $article['news_id'];
    $title    = $article['title'];
    $summary  = $article['summary'];
    $news_url = "https://amrutmaharashtra.org/news.php?id={$news_id}";

    if ($index > 0) {
        echo "⏱️  Waiting 10 seconds before next notification...\n";
        sleep(10);
    }

    $payload = json_encode([
        "app_id"            => $APP_ID,
        "included_segments" => ["All"],
        "headings"          => ["en" => $title],
        "contents"          => ["en" => $summary],
        "url"               => $news_url
    ]);

    echo "\n--- News ID: {$news_id} ---\n";
    echo "📋 Title: {$title}\n";
    echo "🔗 URL: {$news_url}\n";
    echo "📦 Payload: {$payload}\n\n";

    $success = false;

    foreach ($auth_formats as $auth) {
        echo "🔐 Trying auth format: [{$auth}]\n";

        $result = sendCurlPost(
            "https://api.onesignal.com/notifications",
            $payload,
            $auth
        );

        echo "   HTTP Code : " . $result['http_code'] . "\n";
        echo "   Response  : " . $result['response'] . "\n";

        if (!empty($result['curl_err'])) {
            echo "   cURL Error: " . $result['curl_err'] . "\n";
        }

        if ($result['http_code'] === 200) {
            $success = true;
            echo "✅ SUCCESS with auth format: [{$auth}]\n";
            break; // Stop trying other formats
        }
    }

    $notification_number = $index + 1;
    $total               = count($news_articles);

    if ($success) {
        $sent_news_ids[] = $news_id;
        echo "✅ Notification {$notification_number}/{$total} sent for News ID {$news_id}\n";
    } else {
        echo "❌ All auth formats failed for News ID {$news_id}\n";
        echo "👉 Check your OneSignal App ID and API Key at: https://app.onesignal.com\n";
    }
}

// -------------------------------
// STEP 3: Update onsignal flag
// -------------------------------

if (!empty($sent_news_ids)) {
    $placeholders = implode(',', array_fill(0, count($sent_news_ids), '?'));
    $update_query = "UPDATE news_articles SET onsignal = 1 WHERE news_id IN ({$placeholders})";

    $stmt = $conn->prepare($update_query);
    $types = str_repeat('i', count($sent_news_ids));
    $stmt->bind_param($types, ...$sent_news_ids);
    $stmt->execute();

    echo "\n✅ Updated onsignal = 1 for " . count($sent_news_ids) . " articles\n";
    $stmt->close();
} else {
    echo "\n❌ No articles were successfully sent\n";
}

// -------------------------------
// CLEANUP
// -------------------------------

$conn->close();
echo "🔒 Database connection closed\n";