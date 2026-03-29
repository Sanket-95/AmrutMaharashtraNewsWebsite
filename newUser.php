<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Only admin can access this page
// if ($_SESSION['roll'] !== 'admin') {
//     header('Location: index.php');
//     exit();
// }
include 'components/db_config.php';
include 'components/header.php';
include 'components/navbar.php';
include 'components/login_navbar.php';


// Get current username for logging
$current_username = $_SESSION['username'] ?? '';

// Fetch all districts from mdistrict table for location dropdown
$districts = [];
$dist_sql = "SELECT d.*, 
             (SELECT marathiname FROM mdivision WHERE id = d.divisionid) as division_marathi 
             FROM mdistrict d 
             ORDER BY d.division, d.district";
$dist_result = mysqli_query($conn, $dist_sql);
if ($dist_result) {
    while ($row = mysqli_fetch_assoc($dist_result)) {
        $districts[] = $row;
    }
}

// Handle form submission for new user
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_user'])) {
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $location = mysqli_real_escape_string($conn, $_POST['location']);
        $roll = mysqli_real_escape_string($conn, $_POST['roll']);
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "अवैध ईमेल फॉरमॅट!";
        }
        // Check if email already exists
        else {
            $check_email_sql = "SELECT * FROM logincrd WHERE email = '$email'";
            $check_email_result = mysqli_query($conn, $check_email_sql);
            
            if (mysqli_num_rows($check_email_result) > 0) {
                $error = "हा ईमेल आधीपासून नोंदणीकृत आहे!";
            } else {
                // Hash password with MD5 (as per your existing system)
                $hashed_password = md5($password);
                
                $sql = "INSERT INTO logincrd (username, email, password, location, roll) 
                        VALUES ('$username', '$email', '$hashed_password', '$location', '$roll')";
                
                if (mysqli_query($conn, $sql)) {
                    $message = "यूजर यशस्वीरित्या तयार केला गेला!";
                } else {
                    $error = "त्रुटी: " . mysqli_error($conn);
                }
            }
        }
    }
    
    // Handle edit user
    if (isset($_POST['edit_user'])) {
        $uid = mysqli_real_escape_string($conn, $_POST['uid']);
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        
        // Note: location and roll are NOT updated from the edit form
        // They remain as they are in the database
        
        // Check if password update is requested
        if (!empty($_POST['new_password'])) {
            $new_password = md5($_POST['new_password']);
            $update_query = "UPDATE logincrd SET username='$username', email='$email', password='$new_password' WHERE uid='$uid'";
        } else {
            $update_query = "UPDATE logincrd SET username='$username', email='$email' WHERE uid='$uid'";
        }
        
        if (mysqli_query($conn, $update_query)) {
            $message = "यूजर यशस्वीरित्या अपडेट केला गेला!";
        } else {
            $error = "त्रुटी: " . mysqli_error($conn);
        }
    }
    
    // Handle delete user
    if (isset($_POST['delete_user'])) {
        $uid = mysqli_real_escape_string($conn, $_POST['uid']);
        
        // Don't allow admin to delete themselves
        if ($uid == $_SESSION['user_id']) {
            $error = "तुम्ही स्वतःला डिलीट करू शकत नाही!";
        } else {
            $delete_query = "DELETE FROM logincrd WHERE uid='$uid'";
            
            if (mysqli_query($conn, $delete_query)) {
                $message = "यूजर यशस्वीरित्या हटविला गेला!";
            } else {
                $error = "त्रुटी: " . mysqli_error($conn);
            }
        }
    }
}

// Pagination settings
$records_per_page = 8;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Get total users count for pagination
$total_users_sql = "SELECT COUNT(*) as total FROM logincrd";
$total_users_result = mysqli_query($conn, $total_users_sql);
$total_users = mysqli_fetch_assoc($total_users_result)['total'];
$total_pages = ceil($total_users / $records_per_page);

// Fetch users with pagination
$users = [];
$user_sql = "SELECT uid, username, email, location, roll FROM logincrd ORDER BY uid ASC LIMIT $offset, $records_per_page";
$user_result = mysqli_query($conn, $user_sql);
if ($user_result) {
    while ($row = mysqli_fetch_assoc($user_result)) {
        $users[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="mr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>यूजर नोंदणी - अमृत महाराष्ट्र</title>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts for Marathi -->
    <link href="https://fonts.googleapis.com/css2?family=Mukta:wght@400;500;600;700&family=Khand:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Toastify CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #fff5e6, #fff0e0);
            font-family: 'Mukta', sans-serif;
            min-height: 100vh;
        }
        
        .user-container {
            max-width: 1300px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .user-header {
            background: linear-gradient(135deg, #FF6600, #FF8C00);
            color: white;
            padding: 30px;
            border-radius: 20px 20px 0 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .user-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: shine 10s infinite;
        }
        
        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }
        
        .user-card {
            background: white;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 10px 30px rgba(255, 102, 0, 0.2);
            overflow: hidden;
            border: 3px solid #FF6600;
            border-top: none;
        }
        
        .form-section {
            background: #fff9f2;
            padding: 30px;
            border-bottom: 2px solid #FFD8B0;
        }
        
        .form-section h4 {
            color: #FF6600;
            font-family: 'Khand', sans-serif;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .form-label {
            color: #FF6600;
            font-weight: 600;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .form-control, .form-select {
            border: 2px solid #FFD8B0;
            border-radius: 10px;
            padding: 12px 15px;
            font-family: 'Mukta', sans-serif;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #FF6600;
            box-shadow: 0 0 0 0.25rem rgba(255, 102, 0, 0.25);
        }
        
        .password-input-group {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .password-input-group input {
            flex: 1;
            padding-right: 45px;
        }
        
        .password-toggle-icon {
            position: absolute;
            right: 15px;
            color: #FF6600;
            cursor: pointer;
            font-size: 18px;
            z-index: 10;
            background: transparent;
            border: none;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
        }
        
        .password-toggle-icon:hover {
            color: #FF8C00;
        }
        
        .submit-btn {
            background: linear-gradient(135deg, #FF6600, #FF8C00);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 5px 15px rgba(255, 102, 0, 0.3);
        }
        
        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 102, 0, 0.4);
        }
        
        .table-section {
            padding: 30px;
        }
        
        .table {
            font-family: 'Mukta', sans-serif;
        }
        
        .table thead {
            background: linear-gradient(135deg, #FF6600, #FF8C00);
            color: white;
        }
        
        .table thead th {
            font-weight: 600;
            font-size: 16px;
            padding: 15px;
            border: none;
        }
        
        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid #FFD8B0;
        }
        
        .role-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
        }
        
        .role-admin {
            background: linear-gradient(135deg, #dc3545, #fd7e14);
            color: white;
        }
        
        .role-division {
            background: linear-gradient(135deg, #17a2b8, #0dcaf0);
            color: white;
        }
        
        .role-district {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }
        
        .pagination {
            gap: 5px;
        }
        
        .page-link {
            color: #FF6600;
            border: 2px solid #FFD8B0;
            border-radius: 8px;
            padding: 8px 16px;
            font-family: 'Mukta', sans-serif;
            transition: all 0.3s ease;
        }
        
        .page-link:hover {
            background: #FF6600;
            color: white;
            border-color: #FF6600;
            transform: translateY(-2px);
        }
        
        .page-item.active .page-link {
            background: #FF6600;
            border-color: #FF6600;
            color: white;
        }
        
        .page-item.disabled .page-link {
            color: #ccc;
            border-color: #FFD8B0;
            background: #f8f9fa;
        }
        
        .toastify {
            font-family: 'Mukta', sans-serif !important;
            font-size: 16px !important;
            border-radius: 8px !important;
            padding: 12px 20px !important;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2) !important;
        }
        
        .small-note {
            font-size: 12px;
            color: #6c757d;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .password-requirements {
            background: #fff0e0;
            border-radius: 8px;
            padding: 10px;
            margin-top: 5px;
            font-size: 13px;
        }
        
        .password-requirements i {
            color: #FF6600;
            margin-right: 5px;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        .edit-btn {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .delete-btn {
            background: linear-gradient(135deg, #dc3545, #fd7e14);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .edit-btn:hover, .delete-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .info-badge {
            background: #fff0e0;
            color: #FF6600;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
        }
        
        .current-user-badge {
            background: #FF6600;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            margin-left: 5px;
        }
        
        .readonly-field {
            background-color: #f8f9fa !important;
            border: 2px solid #FFD8B0;
            border-radius: 10px;
            padding: 12px 15px;
            font-family: 'Mukta', sans-serif;
            color: #495057;
            cursor: default;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .readonly-field i {
            color: #FF6600;
            font-size: 16px;
        }
        
        .readonly-field span {
            flex: 1;
        }
        
        @media (max-width: 768px) {
            .user-container {
                margin: 20px auto;
            }
            
            .form-section, .table-section {
                padding: 20px;
            }
            
            .submit-btn {
                width: 100%;
            }
            
            .table thead {
                display: none;
            }
            
            .table tbody td {
                display: block;
                text-align: left;
                padding: 10px;
                border-bottom: 1px solid #FFD8B0;
            }
            
            .table tbody td:before {
                content: attr(data-label);
                float: left;
                font-weight: bold;
                color: #FF6600;
                margin-right: 10px;
                min-width: 80px;
            }
            
            .pagination {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .action-buttons {
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="user-container">
        <!-- Header -->
        <div class="user-header">
            <h2 style="font-family: 'Khand', sans-serif; font-size: 32px;">
                <i class="bi bi-person-plus-fill me-2"></i>यूजर नोंदणी
            </h2>
            <p style="font-size: 16px; opacity: 0.9;">नवीन यूजर तयार करा / व्यवस्थापन</p>
        </div>
        
        <!-- Main Card -->
        <div class="user-card">
            <!-- Add User Form Section -->
            <div class="form-section">
                <h4>
                    <i class="bi bi-plus-circle me-2"></i>
                    नवीन यूजर तयार करा
                </h4>
                
                <form method="POST" action="" id="userForm" onsubmit="return validateForm()">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-person me-1"></i> पूर्ण नाव *
                            </label>
                            <input type="text" class="form-control" name="username" 
                                   placeholder="यूजरचे पूर्ण नाव" required id="username">
                            <div class="small-note">
                                <i class="bi bi-info-circle"></i> उदा. Harshal Gadre, Omkar Hardas
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-envelope me-1"></i> ईमेल *
                            </label>
                            <input type="email" class="form-control" name="email" 
                                   placeholder="ईमेल आयडी" required id="email">
                            <div class="small-note">
                                <i class="bi bi-info-circle"></i> लॉगिनसाठी ईमेल वापरला जाईल
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-lock me-1"></i> पासवर्ड *
                            </label>
                            <div class="password-input-group">
                                <input type="password" class="form-control" name="password" 
                                       placeholder="पासवर्ड टाका" required id="password">
                                <i class="bi bi-eye-slash password-toggle-icon" id="togglePassword" onclick="togglePassword('password', 'togglePassword')"></i>
                            </div>
                            <div class="password-requirements">
                                <i class="bi bi-check-circle"></i> किमान 6 अक्षरे
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-lock-fill me-1"></i> पासवर्ड पुन्हा टाका *
                            </label>
                            <div class="password-input-group">
                                <input type="password" class="form-control" id="confirmPassword" 
                                       placeholder="पुन्हा पासवर्ड टाका" required>
                                <i class="bi bi-eye-slash password-toggle-icon" id="toggleConfirmPassword" onclick="togglePassword('confirmPassword', 'toggleConfirmPassword')"></i>
                            </div>
                            <small class="text-danger" id="passwordMatchError" style="display: none;">
                                <i class="bi bi-exclamation-triangle"></i> पासवर्ड जुळत नाहीत
                            </small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-geo-alt me-1"></i> स्थान / जिल्हा *
                            </label>
                            <select class="form-select" name="location" required id="location">
                                <option value="">-- जिल्हा निवडा --</option>
                                <?php 
                                $current_division = '';
                                foreach ($districts as $dist): 
                                    // Display division header if new division
                                    if ($current_division != $dist['division']):
                                        if ($current_division != '') echo '</optgroup>';
                                        $current_division = $dist['division'];
                                        $division_display = $dist['division'] . ' (' . ($dist['division_marathi'] ?? $dist['division']) . ')';
                                ?>
                                    <optgroup label="<?php echo htmlspecialchars($division_display); ?>">
                                <?php 
                                    endif; 
                                ?>
                                    <option value="<?php echo htmlspecialchars($dist['district']); ?>">
                                        <?php echo htmlspecialchars($dist['district']); ?> (<?php echo htmlspecialchars($dist['dmarathi']); ?>)
                                    </option>
                                <?php endforeach; ?>
                                <?php if ($current_division != '') echo '</optgroup>'; ?>
                            </select>
                            <div class="small-note">
                                <i class="bi bi-info-circle"></i> यूजरचे कार्यक्षेत्र निवडा
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-shield me-1"></i> भूमिका / रोल *
                            </label>
                            <select class="form-select" name="roll" required id="roll">
                                <option value="">-- भूमिका निवडा --</option>
                                <option value="admin">प्रशासक (Admin)</option>
                                <option value="division_head">विभाग प्रमुख (Division Head)</option>
                                <option value="district_user">जिल्हा वापरकर्ता (District User)</option>
                            </select>
                            <div class="small-note">
                                <i class="bi bi-info-circle"></i> यूजरची परवानगी पातळी निवडा
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert alert-warning" style="background: #fff0e0; border: 1px dashed #FF6600;">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-shield-lock" style="color: #FF6600; font-size: 20px;"></i>
                                    <span>
                                        <strong>महत्त्वाचे:</strong> पासवर्ड MD5 एन्क्रिप्शनमध्ये सेव्ह केला जाईल. 
                                        यूजर नंतर प्रोफाइलमध्ये पासवर्ड बदलू शकतो.
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12 d-flex justify-content-end">
                            <input type="hidden" name="create_user" value="1">
                            <button type="submit" class="submit-btn">
                                <i class="bi bi-person-plus"></i> यूजर तयार करा
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Users List Section -->
            <div class="table-section">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 style="color: #FF6600; font-family: 'Khand', sans-serif; margin: 0;">
                        <i class="bi bi-people me-2"></i>
                        नोंदणीकृत यूजर्स
                    </h4>
                    <span class="info-badge">
                        <i class="bi bi-person"></i> एकूण: <?php echo $total_users; ?> | पान <?php echo $page; ?>/<?php echo $total_pages; ?>
                    </span>
                </div>
                
                <?php if (empty($users)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-people" style="font-size: 80px; color: #FFA500;"></i>
                        <h5 style="color: #FF6600; margin-top: 20px;">कोणतेही यूजर नाहीत</h5>
                        <p class="text-muted">कृपया वरील फॉर्ममध्ये नवीन यूजर तयार करा.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>UID</th>
                                    <th>नाव</th>
                                    <th>ईमेल</th>
                                    <th>स्थान</th>
                                    <th>भूमिका</th>
                                    <th>कृती</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td data-label="UID">
                                        <strong>#<?php echo $user['uid']; ?></strong>
                                        <?php if ($user['uid'] == $_SESSION['user_id']): ?>
                                            <span class="current-user-badge">तुम्ही</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="नाव">
                                        <i class="bi bi-person-circle" style="color: #FF6600;"></i>
                                        <?php echo htmlspecialchars($user['username']); ?>
                                    </td>
                                    <td data-label="ईमेल">
                                        <i class="bi bi-envelope" style="color: #FF6600;"></i>
                                        <?php echo htmlspecialchars($user['email']); ?>
                                    </td>
                                    <td data-label="स्थान">
                                        <i class="bi bi-geo-alt" style="color: #FF6600;"></i>
                                        <?php echo htmlspecialchars($user['location']); ?>
                                    </td>
                                    <td data-label="भूमिका">
                                        <?php 
                                        $role_class = '';
                                        $role_text = '';
                                        if ($user['roll'] == 'admin') {
                                            $role_class = 'role-admin';
                                            $role_text = 'प्रशासक';
                                        } elseif ($user['roll'] == 'division_head') {
                                            $role_class = 'role-division';
                                            $role_text = 'विभाग प्रमुख';
                                        } else {
                                            $role_class = 'role-district';
                                            $role_text = 'जिल्हा वापरकर्ता';
                                        }
                                        ?>
                                        <span class="role-badge <?php echo $role_class; ?>">
                                            <?php echo $role_text; ?>
                                        </span>
                                    </td>
                                    <td data-label="कृती">
                                        <div class="action-buttons">
                                            <button class="edit-btn" onclick='editUser(<?php echo json_encode($user); ?>)'>
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <?php if ($user['uid'] != $_SESSION['user_id']): ?>
                                            <button class="delete-btn" onclick='deleteUser(<?php echo $user['uid']; ?>, "<?php echo htmlspecialchars($user['username']); ?>")'>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="pagination-container">
                        <nav aria-label="यूजर नेव्हिगेशन">
                            <ul class="pagination">
                                <!-- Previous button -->
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>" <?php echo $page <= 1 ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                                        <i class="bi bi-chevron-left"></i> मागील
                                    </a>
                                </li>
                                
                                <!-- Page numbers -->
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <!-- Next button -->
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>" <?php echo $page >= $total_pages ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                                        पुढील <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Edit User Modal (Modified with Readonly Location and Roll) -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #28a745, #20c997); color: white;">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2"></i>यूजर संपादित करा
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="" id="editUserForm">
                    <div class="modal-body">
                        <input type="hidden" name="uid" id="edit_uid">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-person me-1"></i> पूर्ण नाव *
                                </label>
                                <input type="text" class="form-control" name="username" 
                                       id="edit_username" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-envelope me-1"></i> ईमेल *
                                </label>
                                <input type="email" class="form-control" name="email" 
                                       id="edit_email" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-geo-alt me-1"></i> स्थान / जिल्हा
                                </label>
                                <div class="readonly-field">
                                    <i class="bi bi-lock"></i>
                                    <span id="edit_location_display"></span>
                                </div>
                                <input type="hidden" name="location" id="edit_location">
                                <div class="small-note">
                                    <i class="bi bi-info-circle"></i> स्थान बदलता येत नाही
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-shield me-1"></i> भूमिका / रोल
                                </label>
                                <div class="readonly-field">
                                    <i class="bi bi-lock"></i>
                                    <span id="edit_roll_display"></span>
                                </div>
                                <input type="hidden" name="roll" id="edit_roll">
                                <div class="small-note">
                                    <i class="bi bi-info-circle"></i> भूमिका बदलता येत नाही
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card" style="border: 2px dashed #FF6600;">
                                    <div class="card-header" style="background: #fff0e0; color: #FF6600;">
                                        <i class="bi bi-key me-2"></i>पासवर्ड बदला (ऐच्छिक)
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">
                                                    <i class="bi bi-lock me-1"></i> नवीन पासवर्ड
                                                </label>
                                                <div class="password-input-group">
                                                    <input type="password" class="form-control" name="new_password" 
                                                           placeholder="नवीन पासवर्ड टाका" id="edit_new_password">
                                                    <i class="bi bi-eye-slash password-toggle-icon" id="toggleEditPassword" onclick="togglePassword('edit_new_password', 'toggleEditPassword')"></i>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">
                                                    <i class="bi bi-lock-fill me-1"></i> पासवर्ड पुन्हा टाका
                                                </label>
                                                <div class="password-input-group">
                                                    <input type="password" class="form-control" id="edit_confirm_password" 
                                                           placeholder="पुन्हा पासवर्ड टाका">
                                                    <i class="bi bi-eye-slash password-toggle-icon" id="toggleEditConfirmPassword" onclick="togglePassword('edit_confirm_password', 'toggleEditConfirmPassword')"></i>
                                                </div>
                                                <small class="text-danger" id="editPasswordMatchError" style="display: none;">
                                                    <i class="bi bi-exclamation-triangle"></i> पासवर्ड जुळत नाहीत
                                                </small>
                                            </div>
                                        </div>
                                        <div class="small-note">
                                            <i class="bi bi-info-circle"></i> पासवर्ड बदलायचा नसेल तर रिकामे ठेवा
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">रद्द करा</button>
                        <button type="submit" name="edit_user" class="btn" style="background: #28a745; color: white;">
                            <i class="bi bi-check-circle"></i> अपडेट करा
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete User Modal -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #dc3545, #fd7e14); color: white;">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle me-2"></i>यूजर हटवा
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="uid" id="delete_uid">
                        <p style="font-size: 16px;">तुम्हाला <strong id="delete_username"></strong> हा यूजर कायमचा हटवायचा आहे का?</p>
                        <p class="text-danger"><strong>सावधान:</strong> ही क्रिया पूर्ववत करता येणार नाही!</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">रद्द करा</button>
                        <button type="submit" name="delete_user" class="btn btn-danger">
                            <i class="bi bi-trash"></i> हटवा
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Toastify JS -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    
    <script>
        // Toastify configuration
        function showToast(message, type = 'success') {
            let backgroundColor = '#28a745';
            let icon = '✅';
            
            if (type === 'error') {
                backgroundColor = '#dc3545';
                icon = '❌';
            } else if (type === 'info') {
                backgroundColor = '#17a2b8';
                icon = 'ℹ️';
            }
            
            Toastify({
                text: `${icon} ${message}`,
                duration: 3000,
                gravity: "top",
                position: "right",
                stopOnFocus: true,
                style: {
                    background: backgroundColor,
                    borderRadius: "8px",
                    fontFamily: "'Mukta', sans-serif",
                    fontSize: "16px",
                    padding: "12px 20px",
                    boxShadow: "0 5px 15px rgba(0,0,0,0.2)"
                }
            }).showToast();
        }
        
        // Toggle password visibility
        window.togglePassword = function(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            }
        };
        
        // Form validation for new user
        window.validateForm = function() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const errorElement = document.getElementById('passwordMatchError');
            
            if (password !== confirmPassword) {
                errorElement.style.display = 'block';
                showToast('पासवर्ड जुळत नाहीत!', 'error');
                return false;
            } else if (password.length < 6) {
                showToast('पासवर्ड किमान ६ अक्षरांचा असावा!', 'error');
                return false;
            } else {
                errorElement.style.display = 'none';
                return true;
            }
        };
        
        // Real-time password match check for new user
        document.getElementById('confirmPassword')?.addEventListener('keyup', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const errorElement = document.getElementById('passwordMatchError');
            
            if (confirmPassword && password !== confirmPassword) {
                errorElement.style.display = 'block';
            } else {
                errorElement.style.display = 'none';
            }
        });
        
        // Real-time password match check for edit user
        document.getElementById('edit_confirm_password')?.addEventListener('keyup', function() {
            const password = document.getElementById('edit_new_password').value;
            const confirmPassword = this.value;
            const errorElement = document.getElementById('editPasswordMatchError');
            
            if (confirmPassword && password !== confirmPassword) {
                errorElement.style.display = 'block';
            } else {
                errorElement.style.display = 'none';
            }
        });
        
        // Edit user form validation
        document.getElementById('editUserForm')?.addEventListener('submit', function(e) {
            const newPassword = document.getElementById('edit_new_password').value;
            const confirmPassword = document.getElementById('edit_confirm_password').value;
            const errorElement = document.getElementById('editPasswordMatchError');
            
            if (newPassword || confirmPassword) {
                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    errorElement.style.display = 'block';
                    showToast('पासवर्ड जुळत नाहीत!', 'error');
                } else if (newPassword.length < 6) {
                    e.preventDefault();
                    showToast('पासवर्ड किमान ६ अक्षरांचा असावा!', 'error');
                } else {
                    errorElement.style.display = 'none';
                }
            }
        });
        
        // Edit user function (updated for readonly display)
        window.editUser = function(userData) {
            document.getElementById('edit_uid').value = userData.uid;
            document.getElementById('edit_username').value = userData.username;
            document.getElementById('edit_email').value = userData.email;
            
            // Set hidden fields for location and roll (not editable)
            document.getElementById('edit_location').value = userData.location;
            document.getElementById('edit_roll').value = userData.roll;
            
            // Set display values for readonly fields
            document.getElementById('edit_location_display').innerHTML = userData.location;
            
            let roleText = '';
            if (userData.roll === 'admin') roleText = 'प्रशासक';
            else if (userData.roll === 'division_head') roleText = 'विभाग प्रमुख';
            else roleText = 'जिल्हा वापरकर्ता';
            document.getElementById('edit_roll_display').innerHTML = roleText;
            
            // Clear password fields
            document.getElementById('edit_new_password').value = '';
            document.getElementById('edit_confirm_password').value = '';
            document.getElementById('editPasswordMatchError').style.display = 'none';
            
            var editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
            editModal.show();
        };
        
        // Delete user function
        window.deleteUser = function(uid, username) {
            document.getElementById('delete_uid').value = uid;
            document.getElementById('delete_username').innerHTML = username;
            
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
            deleteModal.show();
        };
        
        // Show messages
        <?php if ($message): ?>
        showToast('<?php echo $message; ?>', 'success');
        <?php endif; ?>
        
        <?php if ($error): ?>
        showToast('<?php echo $error; ?>', 'error');
        <?php endif; ?>
    </script>
</body>
</html>

<?php
include 'components/footer.php';
?>