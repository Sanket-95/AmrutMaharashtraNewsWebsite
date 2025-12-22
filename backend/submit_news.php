<?php
session_start();
// Database configuration
include '../components/db_config.php';

// Set default timezone to IST for all PHP date functions
date_default_timezone_set('Asia/Kolkata');

// Function to sanitize input
// function sanitize_input($data) {
//     if(empty($data)) return $data;
//     $data = trim($data);
//     $data = stripslashes($data);
//     $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
//     return $data;
// }

function sanitize_input($data) {
    if ($data === null) return $data;
    return trim($data);
}

// Function to create directory if not exists (cross-platform)
function create_directory($path) {
    if (!file_exists($path)) {
        if (!mkdir($path, 0777, true)) {
            return false;
        }
        chmod($path, 0777);
    }
    return true;
}

// Function to get file extension
function get_file_extension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

// Function to validate image
function validate_image($file, $max_size = 5242880) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['success' => false, 'message' => 'फाइल अपलोड करा'];
    }
    
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'फाइल आकार ५MB पेक्षा जास्त आहे'];
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        return ['success' => false, 'message' => 'फक्त JPG, PNG, WebP फाइल्सच परवानगी आहे'];
    }
    
    return ['success' => true];
}

// Function to generate unique filename using current datetime
function generate_filename($prefix = '') {
    $timestamp = date('Ymd_His');
    $microtime = explode(' ', microtime());
    $microseconds = substr($microtime[0], 2, 6);
    $random = rand(1000, 9999);
    return $prefix . $timestamp . '_' . $microseconds . '_' . $random;
}

// Function to convert datetime-local input to MySQL datetime (store as given, assume it's already correct)
function format_datetime_for_db($datetime_local) {
    if (empty($datetime_local)) {
        // Return current time in MySQL format
        return date('Y-m-d H:i:s');
    }
    
    // Input format: YYYY-MM-DDTHH:MM
    // Convert to YYYY-MM-DD HH:MM:SS
    $datetime = str_replace('T', ' ', $datetime_local);
    
    // If seconds are not provided, add :00
    if (strlen($datetime) === 16) { // YYYY-MM-DD HH:MM format
        $datetime .= ':00';
    }
    
    // Validate the format
    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $datetime)) {
        return $datetime;
    }
    
    // If invalid, return current time
    return date('Y-m-d H:i:s');
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitize inputs
        $region = sanitize_input($_POST['region']);
        $district = sanitize_input($_POST['district']);
        $category = sanitize_input($_POST['category']);
        $news_title = sanitize_input($_POST['news_title']);
        $news_summary = sanitize_input($_POST['news_summary']);
        $full_news = sanitize_input($_POST['full_news']);
        $publisher_name = sanitize_input($_POST['publisher_name']);
        $publish_date_input = sanitize_input($_POST['publish_date']);
        
        // Format the datetime for database (store exactly as user selected)
        $publish_date = format_datetime_for_db($publish_date_input);
        
        // Get topnews value (checkbox - 1 if checked, 0 if not)
        $topnews = isset($_POST['topnews']) ? 1 : 0;
        
        // Validate required fields
        if (empty($region) || empty($district) || empty($category) || empty($news_title) || 
            empty($news_summary) || empty($full_news) || empty($publisher_name) || empty($publish_date_input)) {
            throw new Exception('सर्व आवश्यक फील्ड भरा');
        }
        
        // Validate cover photo
        $cover_photo = $_FILES['cover_photo'];
        $cover_validation = validate_image($cover_photo);
        if (!$cover_validation['success']) {
            throw new Exception($cover_validation['message']);
        }
        
        // Initialize secondary photo variables
        $secondary_photo_url = NULL;
        
        // Check if secondary photo is uploaded
        if (isset($_FILES['news_image']) && !empty($_FILES['news_image']['tmp_name'])) {
            $secondary_photo = $_FILES['news_image'];
            $secondary_validation = validate_image($secondary_photo);
            if (!$secondary_validation['success']) {
                throw new Exception($secondary_validation['message']);
            }
        }
        
        // Base directory for photos (in root)
        $base_dir = dirname(dirname(__FILE__)) . '/photos/';
        
        // Use English district name for folder
        $district_folder = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $district));
        
        // Create district directory if not exists
        $district_dir = $base_dir . $district_folder . '/';
        if (!create_directory($district_dir)) {
            throw new Exception('जिल्हा डायरेक्टरी तयार करताना त्रुटी');
        }
        
        // Create coverphoto directory
        $coverphoto_dir = $district_dir . 'coverphoto/';
        if (!create_directory($coverphoto_dir)) {
            throw new Exception('कव्हर फोटो डायरेक्टरी तयार करताना त्रुटी');
        }
        
        // Create newsphoto directory
        $newsphoto_dir = $district_dir . 'newsphoto/';
        if (!create_directory($newsphoto_dir)) {
            throw new Exception('न्यूज फोटो डायरेक्टरी तयार करताना त्रुटी');
        }
        
        // Generate unique filename using current datetime
        $cover_filename = generate_filename('cover_');
        $cover_extension = get_file_extension($cover_photo['name']);
        $cover_filename_full = $cover_filename . '.' . $cover_extension;
        $cover_target_path = $coverphoto_dir . $cover_filename_full;
        
        // Move uploaded cover photo
        if (!move_uploaded_file($cover_photo['tmp_name'], $cover_target_path)) {
            throw new Exception('कव्हर फोटो सेव्ह करताना त्रुटी');
        }
        
        // Generate cover photo URL
        $cover_photo_url = 'photos/' . $district_folder . '/coverphoto/' . $cover_filename_full;
        
        // Process secondary photo if uploaded
        if (isset($secondary_photo)) {
            $secondary_filename = generate_filename('news_');
            $secondary_extension = get_file_extension($secondary_photo['name']);
            $secondary_filename_full = $secondary_filename . '.' . $secondary_extension;
            $secondary_target_path = $newsphoto_dir . $secondary_filename_full;
            
            if (!move_uploaded_file($secondary_photo['tmp_name'], $secondary_target_path)) {
                throw new Exception('अतिरिक्त फोटो सेव्ह करताना त्रुटी');
            }
            
            $secondary_photo_url = 'photos/' . $district_folder . '/newsphoto/' . $secondary_filename_full;
        }
        
        // Database connection
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception('डेटाबेस कनेक्शन त्रुटी');
        }
        
        $conn->set_charset("utf8mb4");
        
        // Set MySQL session timezone to IST to match PHP
        $conn->query("SET time_zone = '+05:30'");
        
        // Determine is_approved value based on user role
        $is_approved = 1; // Default to approved for admin and division_head
        if (isset($_SESSION['roll']) && $_SESSION['roll'] === 'district_user') {
            $is_approved = 0; // Not approved for district_user
        }
        
        // Prepare SQL statement - Use NOW() which will use the session timezone (IST)
        $sql = "INSERT INTO news_articles (
                    Region, 
                    district_name, 
                    category_name, 
                    title, 
                    cover_photo_url, 
                    secondary_photo_url, 
                    summary, 
                    content, 
                    published_by, 
                    approved_by, 
                    published_date, 
                    created_at, 
                    updated_at, 
                    is_approved, 
                    view,
                    topnews
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, ?, NOW(), NOW(), ?, 0, ?)";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('SQL स्टेटमेंट तयार करताना त्रुटी: ' . $conn->error);
        }
        
        // Bind parameters: 12 parameters total
        $stmt->bind_param(
            "ssssssssssii", // 12 parameters: 10 strings + 2 integers
            $region,
            $district,
            $category,
            $news_title,
            $cover_photo_url,
            $secondary_photo_url,
            $news_summary,
            $full_news,
            $publisher_name,
            $publish_date, // Store exactly what user selected
            $is_approved,
            $topnews
        );
        
        // Execute statement
        if (!$stmt->execute()) {
            throw new Exception('डेटाबेसमध्ये डेटा घालताना त्रुटी: ' . $stmt->error);
        }
        
        // For debugging
        error_log("User entered datetime: " . $publish_date_input);
        error_log("Formatted for DB: " . $publish_date);
        
        $stmt->close();
        $conn->close();
        
        // Redirect with success message
        header('Location: ../post_news.php?success=1');
        exit();
        
    } catch (Exception $e) {
        // Redirect with error message
        $error_message = urlencode($e->getMessage());
        header('Location: ../post_news.php?error=' . $error_message);
        exit();
    }
} else {
    header('Location: ../post_news.php');
    exit();
}