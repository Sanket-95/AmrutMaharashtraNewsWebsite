<?php
// save_comment.php - FIXED VERSION
session_start();
header('Content-Type: application/json');

// Database connection
include 'components/db_config.php';

// Get form data
$news_id = intval($_POST['news_id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$comment_text = trim($_POST['comment'] ?? '');

// Simple validation
if (empty($name) || empty($email) || empty($comment_text) || $news_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'कृपया सर्व फील्ड भरा'
    ]);
    exit();
}

// Filter email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'कृपया वैध ईमेल पत्ता टाका'
    ]);
    exit();
}

try {
    // INSERT with approve = 1 (immediately approved)
    $query = "INSERT INTO news_comments (news_id, name, email, comment, approve) 
              VALUES (?, ?, ?, ?, 1)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isss", $news_id, $name, $email, $comment_text);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'तुमची प्रतिक्रिया यशस्वीरित्या पोस्ट केली गेली आहे!',
            'comment_id' => $stmt->insert_id
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'डेटाबेस त्रुटी: ' . $conn->error
        ]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'त्रुटी: ' . $e->getMessage()
    ]);
}

$conn->close();
?>