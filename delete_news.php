<?php
// delete_news_simple.php
session_start();
require 'components/db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'प्रवेश नाकारला. कृपया लॉगिन करा.';
    header('Location: login.php');
    exit();
}

// Only admin and division_head can delete news
$allowed_roles = ['admin', 'division_head', 'Admin'];
if (!in_array($_SESSION['roll'], $allowed_roles)) {
    $_SESSION['error'] = 'तुम्हाला ही क्रिया करण्याची परवानगी नाही.';
    header('Location: index.php');
    exit();
}

// Get parameters
$news_id = intval($_GET['news_id'] ?? 0);
$status = $_GET['status'] ?? 'pending';
$return_url = $_GET['return'] ?? "newsapproval.php?status=$status";

if ($news_id <= 0) {
    $_SESSION['error'] = 'अवैध बातमी आयडी.';
    header("Location: $return_url");
    exit();
}

$user_roll = $_SESSION['roll'] ?? '';
$user_location = $_SESSION['location'] ?? '';

// Include the function to get region from location (from newsapproval.php)
function getRegionFromLocation($location) {
    $location = strtolower($location);
    $regions = ['kokan', 'pune', 'sambhajinagar', 'nashik', 'amaravati', 'nagpur'];
    
    if (in_array($location, $regions)) {
        return $location;
    }
    
    $districtToRegion = [
        'palghar' => 'kokan', 'thane' => 'kokan', 'mumbai_city' => 'kokan', 'mumbai' => 'kokan', 
        'mumbai_suburban' => 'kokan', 'raigad' => 'kokan', 'ratnagiri' => 'kokan', 'sindhudurg' => 'kokan',
        'pune' => 'pune', 'satara' => 'pune', 'kolhapur' => 'pune', 'sangli' => 'pune', 'solapur' => 'pune',
        'chhatrapati_sambhajinagar' => 'sambhajinagar', 'beed' => 'sambhajinagar', 'jalna' => 'sambhajinagar',
        'parbhani' => 'sambhajinagar', 'hingoli' => 'sambhajinagar', 'nanded' => 'sambhajinagar',
        'latur' => 'sambhajinagar', 'dharashiv' => 'sambhajinagar', 'nashik' => 'nashik', 'dhule' => 'nashik',
        'nandurbar' => 'nashik', 'ahmednagar' => 'nashik', 'jalgaon' => 'nashik', 'ahilyanagar' => 'nashik',
        'amaravati' => 'amaravati', 'akola' => 'amaravati', 'buldhana' => 'amaravati', 'washim' => 'amaravati',
        'yavatmal' => 'amaravati', 'nagpur' => 'nagpur', 'wardha' => 'nagpur', 'bhandara' => 'nagpur',
        'gondia' => 'nagpur', 'chandrapur' => 'nagpur', 'gadchiroli' => 'nagpur'
    ];
    
    return isset($districtToRegion[$location]) ? $districtToRegion[$location] : $location;
}

// Get user's region if division_head
$user_region = '';
if ($user_roll === 'division_head' && !empty($user_location)) {
    $user_region = getRegionFromLocation($user_location);
}

// Check if news exists and user has permission
$check_sql = "SELECT * FROM news_articles WHERE news_id = ?";
$check_stmt = $conn->prepare($check_sql);
if ($check_stmt) {
    $check_stmt->bind_param("i", $news_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $news = $result->fetch_assoc();
    $check_stmt->close();
    
    if ($news) {
        // Check permissions
        $can_delete = false;
        if ($user_roll === 'admin' || $user_roll === 'Admin') {
            $can_delete = true;
        } elseif ($user_roll === 'division_head') {
            $news_region = strtolower($news['Region'] ?? '');
            if ($news_region === strtolower($user_region)) {
                $can_delete = true;
            }
        }
        
        if ($can_delete) {
            // Delete associated cover photo if it exists
            $cover_photo_url = $news['cover_photo_url'] ?? '';
            if (!empty($cover_photo_url) && strpos($cover_photo_url, 'http') === false) {
                $file_path = realpath($cover_photo_url);
                if ($file_path && file_exists($file_path)) {
                    $filename = basename($file_path);
                    if (strpos($filename, 'default_') === false && 
                        strpos($filename, 'placeholder') === false &&
                        strpos($filename, 'logo') === false) {
                        @unlink($file_path);
                    }
                }
            }
            
            // Delete from database
            $delete_sql = "DELETE FROM news_articles WHERE news_id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            if ($delete_stmt) {
                $delete_stmt->bind_param("i", $news_id);
                if ($delete_stmt->execute()) {
                    $_SESSION['success'] = 'बातमी यशस्वीरित्या हटवली गेली.';
                } else {
                    $_SESSION['error'] = 'बातमी हटवताना त्रुटी.';
                }
                $delete_stmt->close();
            } else {
                $_SESSION['error'] = 'डेटाबेस त्रुटी.';
            }
        } else {
            $_SESSION['error'] = 'तुम्हाला ही बातमी हटवण्याची परवानगी नाही.';
        }
    } else {
        $_SESSION['error'] = 'बातमी आढळली नाही.';
    }
} else {
    $_SESSION['error'] = 'डेटाबेस कनेक्शन त्रुटी.';
}

$conn->close();
header("Location: $return_url");
exit();
?>