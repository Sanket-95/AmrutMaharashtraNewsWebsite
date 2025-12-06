<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header('Location: ../login.php');
    exit();
}

// Define user info variables for easy access
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];
$user_roll = $_SESSION['roll'];
$user_region = $_SESSION['region'];
$user_district = $_SESSION['district'];
$user_username = $_SESSION['username'];

// Function to check if user has specific role
function hasRole($required_roll) {
    global $user_roll;
    return $user_roll === $required_roll;
}

// Function to check if user has access to specific region/district
function hasAccess($region, $district) {
    global $user_roll, $user_region, $user_district;
    
    // Admin has access to everything
    if ($user_roll === 'admin') {
        return true;
    }
    
    // Publisher/Reviewer only have access to their region/district
    if ($user_region === 'all' || $user_region === $region) {
        if ($user_district === 'all' || $user_district === $district) {
            return true;
        }
    }
    
    return false;
}
?>