<?php
// backend/approve_news.php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Only admin and division_head can approve news
$allowed_roles = ['admin', 'division_head', 'Admin'];
if (!in_array($_SESSION['roll'], $allowed_roles)) {
    header('Location: ../newsapproval.php?status=pending&error=परवानगी%20नाही');
    exit();
}

include '../components/db_config.php';

// Get form data
$news_id = $_POST['news_id'] ?? 0;
$action = $_POST['action'] ?? ''; // 'approve' or 'disapprove'
$comment = $_POST['approval_comment'] ?? '';
$approver_name = $_SESSION['name'] ?? '';

// Validate
if ($news_id <= 0 || !in_array($action, ['approve', 'disapprove'])) {
    header('Location: ../newsapproval.php?status=pending&error=अवैध%20विनंती');
    exit();
}

// Function to get region from location
function getRegionFromLocation($location) {
    $location = strtolower($location);
    
    // List of all regions
    $regions = ['kokan', 'pune', 'sambhajinagar', 'nashik', 'amaravati', 'nagpur'];
    
    // If location is directly a region, return it
    if (in_array($location, $regions)) {
        return $location;
    }
    
    // District to region mapping
    $districtToRegion = [
        // Kokan region districts
        'palghar' => 'kokan',
        'thane' => 'kokan',
        'mumbai_city' => 'kokan',
        'mumbai' => 'kokan',
        'mumbai_suburban' => 'kokan',
        'raigad' => 'kokan',
        'ratnagiri' => 'kokan',
        'sindhudurg' => 'kokan',
        
        // Pune region districts
        'pune' => 'pune',
        'satara' => 'pune',
        'kolhapur' => 'pune',
        'sangli' => 'pune',
        'solapur' => 'pune',
        
        // Sambhajinagar region districts
        'chhatrapati_sambhajinagar' => 'sambhajinagar',
        'beed' => 'sambhajinagar',
        'jalna' => 'sambhajinagar',
        'parbhani' => 'sambhajinagar',
        'hingoli' => 'sambhajinagar',
        'nanded' => 'sambhajinagar',
        'latur' => 'sambhajinagar',
        'dharashiv' => 'sambhajinagar',
        
        // Nashik region districts
        'nashik' => 'nashik',
        'dhule' => 'nashik',
        'nandurbar' => 'nashik',
        'ahmednagar' => 'nashik',
        'jalgaon' => 'nashik',
        'ahilyanagar' => 'nashik',
        
        // Amaravati region districts
        'amaravati' => 'amaravati',
        'akola' => 'amaravati',
        'buldhana' => 'amaravati',
        'washim' => 'amaravati',
        'yavatmal' => 'amaravati',
        
        // Nagpur region districts
        'nagpur' => 'nagpur',
        'wardha' => 'nagpur',
        'bhandara' => 'nagpur',
        'gondia' => 'nagpur',
        'chandrapur' => 'nagpur',
        'gadchiroli' => 'nagpur'
    ];
    
    return isset($districtToRegion[$location]) ? $districtToRegion[$location] : $location;
}

// Check if user has permission for this news
if ($_SESSION['roll'] === 'division_head') {
    // For division_head, check if news belongs to their region
    $check_sql = "SELECT Region FROM news_articles WHERE news_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $news_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $news = $check_result->fetch_assoc();
        
        // Get user's region from location
        $user_region = getRegionFromLocation($_SESSION['location']);
        
        if (strtolower($news['Region']) !== strtolower($user_region)) {
            header('Location: ../newsapproval.php?status=pending&error=आपल्याला%20या%20बातमीवर%20परवानगी%20नाही');
            exit();
        }
    } else {
        header('Location: ../newsapproval.php?status=pending&error=बातमी%20सापडली%20नाही');
        exit();
    }
    $check_stmt->close();
}

// Update news status in news_articles table
$new_status = ($action === 'approve') ? 1 : 2;
$update_sql = "UPDATE news_articles 
               SET is_approved = ?, 
                   approved_by = ?,
                   updated_at = NOW()
               WHERE news_id = ?";
               
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("isi", $new_status, $approver_name, $news_id);

if ($update_stmt->execute()) {
    // Success - redirect back
    if ($action === 'approve') {
        header('Location: ../newsapproval_details.php?news_id=' . $news_id . '&approved=1');
    } else {
        header('Location: ../newsapproval_details.php?news_id=' . $news_id . '&disapproved=1');
    }
    exit();
} else {
    // Error
    header('Location: ../newsapproval_details.php?news_id=' . $news_id . '&error=अपडेट%20करण्यात%20अपयशी');
    exit();
}

$update_stmt->close();
$conn->close();
?>