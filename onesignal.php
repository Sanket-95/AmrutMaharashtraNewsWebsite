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
// HELPER: Generic cURL request (mirrors Python requests library)
// -------------------------------

function curlRequest($method, $url, $api_key, $body = null) {
    $ch = curl_init();

    // Exactly match what Python's requests library sends
    $headers = [
        "Authorization: Basic {$api_key}",
        "Content-Type: application/json",
        "Accept: application/json",
        "User-Agent: python-requests/2.31.0"   // ← Key fix: Python sends this by default
    ];

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }

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
// STEP 2: Fetch subscribers from OneSignal (same as Python)
// -------------------------------

$fetch_url = "https://api.onesignal.com/players?app_id={$APP_ID}";

echo "🔍 Fetching subscribers from OneSignal...\n";

$res = curlRequest('GET', $fetch_url, $API_KEY);

echo "Status Code: " . $res['http_code'] . "\n";
echo "Raw Response: " . substr($res['response'], 0, 500) . "\n";

if (!empty($res['curl_err'])) {
    echo "❌ cURL Error: " . $res['curl_err'] . "\n";
}

if ($res['http_code'] !== 200) {
    echo "❌ API Error while fetching subscribers\n";
    $conn->close();
    exit;
}

$data    = json_decode($res['response'], true);
$players = $data['players'] ?? [];

// Collect only valid subscribed users (same logic as Python)
$subscription_ids = [];
foreach ($players as $player) {
    if (isset($player['invalid_identifier']) && $player['invalid_identifier'] === false) {
        $subscription_ids[] = $player['id'];
    }
}

echo "✅ Total Active Subscribers: " . count($subscription_ids) . "\n";

if (empty($subscription_ids)) {
    echo "❌ No active subscribers found\n";
    $conn->close();
    exit;
}

// -------------------------------
// STEP 3: Send notifications (same payload structure as Python)
// -------------------------------

$sent_news_ids = [];

foreach ($news_articles as $index => $article) {
    $news_id  = $article['news_id'];
    $title    = $article['title'];
    $summary  = $article['summary'];
    $news_url = "https://amrutmaharashtra.org/news.php?id={$news_id}";

    if ($index > 0) {
        echo "⏱️  Waiting 10 seconds before next notification...\n";
        sleep(10);
    }

    // Identical payload structure to your working Python script
    $payload = json_encode([
        "app_id"             => $APP_ID,
        "include_player_ids" => $subscription_ids,   // ← Same as Python
        "headings"           => ["en" => $title],
        "contents"           => ["en" => $summary],
        "url"                => $news_url
    ], JSON_UNESCAPED_UNICODE);                       // ← Keeps Marathi text readable

    $res = curlRequest('POST', "https://api.onesignal.com/notifications", $API_KEY, $payload);

    $notification_number = $index + 1;
    $total               = count($news_articles);

    echo "🚀 Send Status for News ID {$news_id}: " . $res['http_code'] . "\n";
    echo "📨 Response: " . $res['response'] . "\n";
    echo "🔗 Deep Link: {$news_url}\n";

    if (!empty($res['curl_err'])) {
        echo "❌ cURL Error: " . $res['curl_err'] . "\n";
    }

    if ($res['http_code'] === 200) {
        $sent_news_ids[] = $news_id;
        echo "✅ Notification {$notification_number}/{$total} sent successfully\n";
    } else {
        echo "❌ Failed to send notification for News ID {$news_id}\n";
    }
}

// -------------------------------
// STEP 4: Update onsignal flag
// -------------------------------

if (!empty($sent_news_ids)) {
    $placeholders = implode(',', array_fill(0, count($sent_news_ids), '?'));
    $update_query = "UPDATE news_articles SET onsignal = 1 WHERE news_id IN ({$placeholders})";

    $stmt  = $conn->prepare($update_query);
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