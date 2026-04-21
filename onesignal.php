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

// Local DB (active)
// $db_host     = "localhost";
// $db_user     = "root";
// $db_password = "";
// $db_name     = "amrutmaharashtra";

// Production database configuration   for .org
//  define('DB_HOST', 'localhost');
//  define('DB_USER', 'u153621952_Amrut1234');
//  define('DB_PASS', 'Mahaamrut@123456789');
//  define('DB_NAME', 'u153621952_Mahaamrutdb');

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
// STEP 2: Fetch subscribers from OneSignal
// -------------------------------

$fetch_url = "https://api.onesignal.com/players?app_id={$APP_ID}";

$headers = [
    "Authorization: Basic {$API_KEY}",
    "Content-Type: application/json"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $fetch_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$response_body = curl_exec($ch);
$http_code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status Code: {$http_code}\n";
echo "Raw Response: " . substr($response_body, 0, 500) . "\n";

if ($http_code !== 200) {
    echo "❌ API Error while fetching subscribers\n";
    $conn->close();
    exit;
}

$data    = json_decode($response_body, true);
$players = $data['players'] ?? [];

// Collect only valid/subscribed users
$subscription_ids = [];
foreach ($players as $player) {
    if (isset($player['invalid_identifier']) && $player['invalid_identifier'] === false) {
        $subscription_ids[] = $player['id'];
    }
}

echo "✅ Total Active Subscribers: " . count($subscription_ids) . "\n";

// -------------------------------
// STEP 3: Send notifications for each article with delay
// -------------------------------

if (empty($subscription_ids)) {
    echo "❌ No active subscribers found\n";
    $conn->close();
    exit;
}

$sent_news_ids = [];

foreach ($news_articles as $index => $article) {
    $news_id  = $article['news_id'];
    $title    = $article['title'];
    $summary  = $article['summary'];
    $news_url = "https://amrutmaharashtra.org/news.php?id={$news_id}";

    // 10 second delay between notifications (skipped for first one)
    if ($index > 0) {
        echo "⏱️  Waiting 10 seconds before sending next notification...\n";
        sleep(10);
    }

    $payload = json_encode([
        "app_id"             => $APP_ID,
        "include_player_ids" => $subscription_ids,
        "headings"           => ["en" => $title],
        "contents"           => ["en" => $summary],
        "url"                => $news_url
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.onesignal.com/notifications");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $send_response = curl_exec($ch);
    $send_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "🚀 Send Status for News ID {$news_id}: {$send_http_code}\n";
    echo "📨 Response: {$send_response}\n";
    echo "🔗 Deep Link: {$news_url}\n";

    $notification_number = $index + 1;
    $total               = count($news_articles);

    if ($send_http_code === 200) {
        $sent_news_ids[] = $news_id;
        echo "✅ Notification {$notification_number}/{$total} sent successfully\n";
    } else {
        echo "❌ Failed to send notification for News ID {$news_id}\n";
    }
}

// -------------------------------
// STEP 4: Update onsignal flag for sent articles
// -------------------------------

if (!empty($sent_news_ids)) {
    $placeholders = implode(',', array_fill(0, count($sent_news_ids), '?'));
    $update_query = "UPDATE news_articles SET onsignal = 1 WHERE news_id IN ({$placeholders})";

    $stmt = $conn->prepare($update_query);

    // Bind all IDs dynamically
    $types = str_repeat('i', count($sent_news_ids));
    $stmt->bind_param($types, ...$sent_news_ids);
    $stmt->execute();

    echo "✅ Updated onsignal = 1 for " . count($sent_news_ids) . " articles\n";
    $stmt->close();
} else {
    echo "❌ No articles were successfully sent\n";
}

// -------------------------------
// CLEANUP
// -------------------------------

$conn->close();
echo "🔒 Database connection closed\n";