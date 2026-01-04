<?php
// components/increment_views.php

// Get news ID from URL
$news_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($news_id <= 0) {
    header("Location: index.php");
    exit();
}

// VIEW COUNT LOGIC
if (!isset($_SESSION['viewed_news'])) {
    $_SESSION['viewed_news'] = [];
}

// Check if this news has already been viewed in this session
if (!in_array($news_id, $_SESSION['viewed_news'])) {
    // Increment view count in database
    // This is originally commented out in the provided code snippet
    // $update_view_sql = "UPDATE news_articles SET view = view + 1 WHERE news_id = ?";
    //  This is now modified to add a random number between 0 and 9
    $update_view_sql = "
    UPDATE news_articles
    SET view = view + FLOOR(RAND() * 10)
    WHERE news_id = ?
    ";
    $update_stmt = $conn->prepare($update_view_sql);
    $update_stmt->bind_param("i", $news_id);
    
    if ($update_stmt->execute()) {
        // Add news ID to session array to prevent duplicate counts
        $_SESSION['viewed_news'][] = $news_id;
    }
    
    $update_stmt->close();
}
?>