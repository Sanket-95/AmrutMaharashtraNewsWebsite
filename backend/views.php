<!-- //////Delete after testing -->
<?php
echo "<script>alert('Hello from PHP');</script>";
?>
<?php
// backend/views.php - SIMPLEST VERSION

session_start();

// Get ID from URL
$id = intval($_GET['id'] ?? 0);

// Update database if ID exists
if ($id > 0) {
    include '../components/db_config.php';
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn && !$conn->connect_error) {
         $conn->query("UPDATE news_articles SET view = view + 1 WHERE news_id = $id");
        $conn->close();
    }
}

// Redirect to news.php with ID in URL
header("Location: ../news.php?id=$id");
exit();
?>