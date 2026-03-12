<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'components/header.php';
include 'components/navbar.php';
include 'components/login_navbar.php';
include 'components/db_config.php';

// Get user info from session
$uid = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? '';
$user_name = $_SESSION['name'] ?? '';
$user_email = $_SESSION['email'] ?? '';
$user_roll = $_SESSION['roll'] ?? '';
$user_location = $_SESSION['location'] ?? '';

// Handle profile update
$update_success = '';
$update_error = '';
$password_changed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $new_username = mysqli_real_escape_string($conn, $_POST['username']);
        // No validation for email - accept anything
        $new_email = mysqli_real_escape_string($conn, $_POST['email']);
        
        // Check if password update is requested
        if (!empty($_POST['new_password'])) {
            $new_password = md5($_POST['new_password']);
            $update_query = "UPDATE logincrd SET username='$new_username', email='$new_email', password='$new_password' WHERE uid='$uid'";
            $password_changed = true;
        } else {
            $update_query = "UPDATE logincrd SET username='$new_username', email='$new_email' WHERE uid='$uid'";
        }
        
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['username'] = $new_username;
            $_SESSION['name'] = $new_username;
            $_SESSION['email'] = $new_email;
            
            if ($password_changed) {
                // Password changed - set flag for JavaScript logout
                $update_success = "पासवर्ड यशस्वीरित्या बदलला! ४ सेकंदात लॉगआउट होईल...";
            } else {
                $update_success = "प्रोफाइल यशस्वीरित्या अपडेट केले गेले!";
                
                $username = $new_username;
                $user_name = $new_username;
                $user_email = $new_email;
            }
        } else {
            $update_error = "प्रोफाइल अपडेट करताना त्रुटी: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="mr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>प्रोफाइल - अमृत महाराष्ट्र</title>
    
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
        
        .profile1-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .profile1-header {
            background: linear-gradient(135deg, #FF6600, #FF8C00);
            color: white;
            padding: 30px;
            border-radius: 20px 20px 0 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .profile1-header::before {
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
        
        .profile1-avatar {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            border: 5px solid rgba(255,255,255,0.3);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        .profile1-avatar i {
            font-size: 60px;
            color: #FF6600;
        }
        
        .profile1-name {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
            font-family: 'Khand', sans-serif;
        }
        
        .profile1-role-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 16px;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255,255,255,0.3);
        }
        
        .profile1-card {
            background: white;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 10px 30px rgba(255, 102, 0, 0.2);
            overflow: hidden;
            border: 3px solid #FF6600;
            border-top: none;
        }
        
        .profile1-tabs {
            background: #fff5e6;
            padding: 20px 20px 0;
            border-bottom: 2px solid #FFA500;
        }
        
        .profile1-tab {
            display: inline-block;
            padding: 10px 25px;
            margin-right: 10px;
            background: white;
            border-radius: 10px 10px 0 0;
            color: #FF6600;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid #FFA500;
            border-bottom: none;
            transition: all 0.3s ease;
        }
        
        .profile1-tab.active {
            background: #FF6600;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 -3px 10px rgba(255, 102, 0, 0.2);
        }
        
        .tab-content {
            padding: 30px;
        }
        
        .info-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-card {
            background: #fff9f2;
            border-radius: 15px;
            padding: 20px;
            border: 1px solid #FFD8B0;
            transition: all 0.3s ease;
        }
        
        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(255, 102, 0, 0.1);
            border-color: #FF6600;
        }
        
        .info-label {
            color: #FF6600;
            font-size: 14px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-label i {
            font-size: 18px;
        }
        
        .info-value {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            padding-left: 26px;
        }
        
        .edit-btn {
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
        
        .edit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 102, 0, 0.4);
        }
        
        .edit-form {
            background: #fff9f2;
            border-radius: 15px;
            padding: 30px;
            border: 2px solid #FF6600;
        }
        
        .form-label {
            color: #FF6600;
            font-weight: 600;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .form-control {
            border: 2px solid #FFD8B0;
            border-radius: 10px;
            padding: 12px 15px;
            font-family: 'Mukta', sans-serif;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
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
        
        .readonly-field:hover {
            background-color: #f1f3f5 !important;
        }
        
        .password-section {
            background: #fff0e0;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            border: 1px dashed #FF6600;
        }
        
        .password-section h5 {
            color: #FF6600;
            margin-bottom: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .save-btn {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .cancel-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .save-btn:hover, .cancel-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
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
        
        .logout-timer {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: linear-gradient(135deg, #FF6600, #FF8C00);
            color: white;
            padding: 12px 20px;
            border-radius: 50px;
            font-family: 'Mukta', sans-serif;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            z-index: 9999;
            display: none;
            align-items: center;
            gap: 10px;
        }
        
        .logout-timer i {
            font-size: 20px;
        }
        
        .timer-spinner {
            width: 24px;
            height: 24px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top: 3px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .profile1-container {
                margin: 20px auto;
            }
            
            .profile1-name {
                font-size: 24px;
            }
            
            .profile1-avatar {
                width: 100px;
                height: 100px;
            }
            
            .profile1-avatar i {
                font-size: 50px;
            }
            
            .info-section {
                grid-template-columns: 1fr;
            }
            
            .profile1-tab {
                padding: 8px 15px;
                font-size: 14px;
            }
            
            .tab-content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Logout Timer Display -->
    <div class="logout-timer" id="logoutTimer">
        <div class="timer-spinner"></div>
        <span id="timerMessage">पासवर्ड बदलला! ४ सेकंदात लॉगआउट होईल...</span>
    </div>

    <div class="profile1-container">
        <!-- Profile Header -->
        <div class="profile1-header">
            <div class="profile1-avatar">
                <i class="bi bi-person-circle"></i>
            </div>
            <div class="profile1-name"><?php echo htmlspecialchars($user_name); ?></div>
            <div class="profile1-role-badge">
                <i class="bi bi-shield-check me-1"></i>
                <?php echo htmlspecialchars($user_roll); ?>
            </div>
        </div>
        
        <!-- Profile Content -->
        <div class="profile1-card">
            <div class="profile1-tabs">
                <span class="profile1-tab active" onclick="showTab('info')">
                    <i class="bi bi-info-circle me-1"></i> माहिती
                </span>
                <span class="profile1-tab" onclick="showTab('edit')">
                    <i class="bi bi-pencil-square me-1"></i> संपादन
                </span>
            </div>
            
            <div class="tab-content">
                <!-- Info Tab -->
                <div id="info-tab" class="tab-pane" style="display: block;">
                    <div class="info-section">
                        <div class="info-card">
                            <div class="info-label">
                                <i class="bi bi-person"></i>
                                वापरकर्ता नाव
                            </div>
                            <div class="info-value"><?php echo htmlspecialchars($username); ?></div>
                        </div>
                        
                        <div class="info-card">
                            <div class="info-label">
                                <i class="bi bi-envelope"></i>
                                ईमेल
                            </div>
                            <div class="info-value"><?php echo htmlspecialchars($user_email); ?></div>
                        </div>
                        
                        <div class="info-card">
                            <div class="info-label">
                                <i class="bi bi-geo-alt"></i>
                                स्थान
                            </div>
                            <div class="info-value"><?php echo htmlspecialchars($user_location); ?></div>
                        </div>
                        
                        <div class="info-card">
                            <div class="info-label">
                                <i class="bi bi-shield"></i>
                                भूमिका
                            </div>
                            <div class="info-value"><?php echo htmlspecialchars($user_roll); ?></div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <button class="edit-btn" onclick="showTab('edit')">
                            <i class="bi bi-pencil-square"></i>
                            प्रोफाइल संपादित करा
                        </button>
                    </div>
                </div>
                
                <!-- Edit Tab -->
                <div id="edit-tab" class="tab-pane" style="display: none;">
                    <form method="POST" action="" class="edit-form" id="profileEditForm">
                        <!-- Editable Fields -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-person me-1"></i> वापरकर्ता नाव *
                                </label>
                                <input type="text" class="form-control" name="username" 
                                       value="<?php echo htmlspecialchars($username); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-envelope me-1"></i> ईमेल *
                                </label>
                                <!-- Changed from type="email" to type="text" to accept any input -->
                                <input type="text" class="form-control" name="email" 
                                       value="<?php echo htmlspecialchars($user_email); ?>" 
                                       pattern=".*" 
                                       title="कोणतेही मूल्य स्वीकारले जाईल"
                                       required>
                                <div class="small-note">
                                    <i class="bi bi-info-circle"></i> कोणतेही मूल्य प्रविष्ट करा (ईमेल आवश्यक नाही)
                                </div>
                            </div>
                        </div>
                        
                        <!-- Read-only Fields with Lock Icon -->
                        <div class="row mt-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-geo-alt me-1"></i> स्थान
                                </label>
                                <div class="readonly-field">
                                    <i class="bi bi-lock"></i>
                                    <span><?php echo htmlspecialchars($user_location); ?></span>
                                </div>
                                <div class="small-note">
                                    <i class="bi bi-info-circle"></i> स्थान बदलता येत नाही
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-shield me-1"></i> भूमिका
                                </label>
                                <div class="readonly-field">
                                    <i class="bi bi-lock"></i>
                                    <span><?php echo htmlspecialchars($user_roll); ?></span>
                                </div>
                                <div class="small-note">
                                    <i class="bi bi-info-circle"></i> भूमिका बदलता येत नाही
                                </div>
                            </div>
                        </div>
                        
                        <!-- Password Change Section with Eye Icons -->
                        <div class="password-section">
                            <h5>
                                <i class="bi bi-key me-2"></i>
                                पासवर्ड बदला (ऐच्छिक)
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-lock me-1"></i> नवीन पासवर्ड
                                    </label>
                                    <div class="password-input-group">
                                        <input type="password" class="form-control" name="new_password" 
                                               placeholder="नवीन पासवर्ड टाका" id="newPassword">
                                        <i class="bi bi-eye-slash password-toggle-icon" id="toggleNewPassword" onclick="togglePassword('newPassword', 'toggleNewPassword')"></i>
                                    </div>
                                    <div class="small-note">
                                        <i class="bi bi-info-circle"></i> पासवर्ड बदलायचा नसेल तर रिकामे ठेवा
                                    </div>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-lock-fill me-1"></i> नवीन पासवर्ड पुन्हा टाका
                                    </label>
                                    <div class="password-input-group">
                                        <input type="password" class="form-control" id="confirmPassword" 
                                               placeholder="पुन्हा नवीन पासवर्ड टाका">
                                        <i class="bi bi-eye-slash password-toggle-icon" id="toggleConfirmPassword" onclick="togglePassword('confirmPassword', 'toggleConfirmPassword')"></i>
                                    </div>
                                    <small class="text-danger" id="passwordMatchError" style="display: none;">
                                        <i class="bi bi-exclamation-triangle"></i> पासवर्ड जुळत नाहीत
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="submit" name="update_profile" class="save-btn me-2" onclick="return validatePassword()">
                                <i class="bi bi-check-circle"></i> सुरक्षित करा
                            </button>
                            <button type="button" class="cancel-btn" onclick="showTab('info')">
                                <i class="bi bi-x-circle"></i> रद्द करा
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS (required for Bootstrap Icons functionality) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Toastify JS -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    
    <script>
        // Toastify configuration
        function showToast(message, type = 'success') {
            let backgroundColor = '#28a745'; // Success - green
            let icon = '✅';
            
            if (type === 'error') {
                backgroundColor = '#dc3545'; // Error - red
                icon = '❌';
            } else if (type === 'info') {
                backgroundColor = '#17a2b8'; // Info - blue
                icon = 'ℹ️';
            } else if (type === 'warning') {
                backgroundColor = '#ffc107'; // Warning - yellow
                icon = '⚠️';
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
        
        // Show/hide tabs function
        window.showTab = function(tabName) {
            document.getElementById('info-tab').style.display = 'none';
            document.getElementById('edit-tab').style.display = 'none';
            
            document.querySelectorAll('.profile1-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            document.getElementById(tabName + '-tab').style.display = 'block';
            
            const tabs = document.querySelectorAll('.profile1-tab');
            if (tabName === 'info') {
                tabs[0].classList.add('active');
            } else if (tabName === 'edit') {
                tabs[1].classList.add('active');
            }
        };
        
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
        
        // Password validation
        window.validatePassword = function() {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const errorElement = document.getElementById('passwordMatchError');
            
            if (newPassword || confirmPassword) {
                if (newPassword !== confirmPassword) {
                    errorElement.style.display = 'block';
                    showToast('पासवर्ड जुळत नाहीत!', 'error');
                    return false;
                } else if (newPassword.length < 6) {
                    showToast('पासवर्ड किमान ६ अक्षरांचा असावा!', 'error');
                    return false;
                } else {
                    errorElement.style.display = 'none';
                    return true;
                }
            }
            return true;
        };
        
        // Logout function with 4 second delay
        function delayedLogout() {
            const timerElement = document.getElementById('logoutTimer');
            const timerMessage = document.getElementById('timerMessage');
            
            // Show timer
            timerElement.style.display = 'flex';
            
            let secondsLeft = 4;
            
            // Update message every second
            const countdown = setInterval(function() {
                secondsLeft--;
                timerMessage.textContent = `पासवर्ड बदलला! ${secondsLeft} सेकंदात लॉगआउट होईल...`;
                
                if (secondsLeft <= 0) {
                    clearInterval(countdown);
                    
                    // Make AJAX call to logout
                    fetch('backend/logout.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        }
                    })
                    .then(() => {
                        // Redirect to login page
                        window.location.href = 'login.php?password_changed=1';
                    })
                    .catch(() => {
                        // If fetch fails, still redirect
                        window.location.href = 'login.php?password_changed=1';
                    });
                }
            }, 1000);
        }
        
        // Form submission validation
        document.getElementById('profileEditForm')?.addEventListener('submit', function(e) {
            if (!validatePassword()) {
                e.preventDefault();
            }
        });
        
        // Show success/error messages and handle password change logout
        <?php if ($update_success): ?>
        showToast('<?php echo $update_success; ?>', 'success');
        
        <?php if ($password_changed): ?>
        // Call delayed logout function
        delayedLogout();
        <?php endif; ?>
        
        <?php endif; ?>
        
        <?php if ($update_error): ?>
        showToast('<?php echo $update_error; ?>', 'error');
        <?php endif; ?>
        
        // Handle URL parameters
        function checkURLParameters() {
            const urlParams = new URLSearchParams(window.location.search);
            
            if (urlParams.get('tab') === 'edit') {
                showTab('edit');
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            checkURLParameters();
        });
    </script>
</body>
</html>

<?php
include 'components/footer.php';
?>