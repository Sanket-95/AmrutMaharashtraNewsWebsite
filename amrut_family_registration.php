<?php
session_start();
include 'components/db_config.php';

// Set timezone to IST
date_default_timezone_set('Asia/Kolkata');

// Fetch districts from database
$districts = [];
$district_query = "SELECT division, marathiname FROM mdivision ORDER BY division ASC";
$district_result = mysqli_query($conn, $district_query);
if ($district_result && mysqli_num_rows($district_result) > 0) {
    while ($row = mysqli_fetch_assoc($district_result)) {
        $districts[] = $row;
    }
}

// Fetch talukas (districts from mdistrict table)
$talukas = [];
$taluka_query = "SELECT district, dmarathi FROM mdistrict ORDER BY district ASC";
$taluka_result = mysqli_query($conn, $taluka_query);
if ($taluka_result && mysqli_num_rows($taluka_result) > 0) {
    while ($row = mysqli_fetch_assoc($taluka_result)) {
        $talukas[] = $row;
    }
}

// Handle form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $family_head_name = mysqli_real_escape_string($conn, $_POST['family_head_name'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    $gender = mysqli_real_escape_string($conn, $_POST['gender'] ?? '');
    $caste_category = mysqli_real_escape_string($conn, $_POST['caste_category'] ?? '');
    $village_name = mysqli_real_escape_string($conn, $_POST['village_name'] ?? '');
    $address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
    $taluka = mysqli_real_escape_string($conn, $_POST['taluka'] ?? '');
    $district = mysqli_real_escape_string($conn, $_POST['district'] ?? '');
    $mobile_number = mysqli_real_escape_string($conn, $_POST['mobile_number'] ?? '');
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $current_occupation = mysqli_real_escape_string($conn, $_POST['current_occupation'] ?? '');
    $annual_income = mysqli_real_escape_string($conn, $_POST['annual_income'] ?? '');
    $want_amrut_benefit = mysqli_real_escape_string($conn, $_POST['want_amrut_benefit'] ?? '');
    $social_media_follow = isset($_POST['social_media_follow']) ? implode(', ', $_POST['social_media_follow']) : '';
    $volunteer_interest = mysqli_real_escape_string($conn, $_POST['volunteer_interest'] ?? '');
    $nation_building_participation = mysqli_real_escape_string($conn, $_POST['nation_building_participation'] ?? '');
    $promotion_method = mysqli_real_escape_string($conn, $_POST['promotion_method'] ?? '');
    $migration_status = mysqli_real_escape_string($conn, $_POST['migration_status'] ?? '');
    
    // Get IP address and user agent
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Validate required fields
    $errors = [];
    if (empty($family_head_name)) $errors[] = "कुटुंब प्रमुखाचे नाव आवश्यक आहे";
    if ($age < 1 || $age > 120) $errors[] = "वैध वय आवश्यक आहे";
    if (empty($gender)) $errors[] = "लिंग निवडा";
    if (empty($caste_category)) $errors[] = "जातीचा प्रवर्ग निवडा";
    if (empty($village_name)) $errors[] = "गावाचे नाव आवश्यक आहे";
    if (empty($address)) $errors[] = "पत्ता आवश्यक आहे";
    if (empty($taluka)) $errors[] = "तालुका निवडा";
    if (empty($district)) $errors[] = "जिल्हा निवडा";
    if (!preg_match('/^[0-9]{10}$/', $mobile_number)) $errors[] = "वैध 10 अंकी मोबाईल नंबर आवश्यक आहे";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "वैध ईमेल पत्ता आवश्यक आहे";
    if (empty($current_occupation)) $errors[] = "सध्याचे काम निवडा";
    if (empty($annual_income)) $errors[] = "वार्षिक उत्पन्न निवडा";
    if (empty($want_amrut_benefit)) $errors[] = "अमृत योजनेचा लाभ हवा आहे का निवडा";
    if (empty($social_media_follow)) $errors[] = "सोशल मीडिया पेज निवडा";
    if (empty($volunteer_interest)) $errors[] = "स्वयंसेवक स्वारस्य निवडा";
    if (empty($nation_building_participation)) $errors[] = "राष्ट्रनिर्माण सहभाग निवडा";
    if (empty($promotion_method)) $errors[] = "प्रचार पद्धत निवडा";
    if (empty($migration_status)) $errors[] = "स्थलांतर स्थिती निवडा";
    
    if (empty($errors)) {
        $insert_query = "INSERT INTO amrut_family_registration (
            family_head_name, age, gender, caste_category, village_name, address, 
            taluka, district, mobile_number, email, current_occupation, annual_income, 
            want_amrut_benefit, social_media_follow, volunteer_interest, 
            nation_building_participation, promotion_method, migration_status, 
            ip_address, user_agent
        ) VALUES (
            '$family_head_name', $age, '$gender', '$caste_category', '$village_name', '$address',
            '$taluka', '$district', '$mobile_number', '$email', '$current_occupation', '$annual_income',
            '$want_amrut_benefit', '$social_media_follow', '$volunteer_interest',
            '$nation_building_participation', '$promotion_method', '$migration_status',
            '$ip_address', '$user_agent'
        )";
        
        if (mysqli_query($conn, $insert_query)) {
            $success_message = "आपली नोंदणी यशस्वीरित्या झाली आहे! धन्यवाद.";
            // Clear form data
            $_POST = array();
        } else {
            $error_message = "नोंदणी करताना त्रुटी आली. कृपया पुन्हा प्रयत्न करा.";
            error_log("Registration error: " . mysqli_error($conn));
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="mr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>अमृत परिवार नोंदणी | AMRUT Maharashtra</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Mukta:wght@400;500;600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Toastr CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Mukta', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 15px;
        }
        
        /* Header Styles */
        .main-header {
            background: linear-gradient(135deg, #FF6600, #FF8C00);
            color: white;
            padding: 15px 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            border-radius: 10px;
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            text-align: center;
        }
        
        .logo-area h1 {
            font-size: 1.5rem;
            margin-bottom: 5px;
            font-weight: 700;
        }
        
        .logo-area p {
            font-size: 0.85rem;
            opacity: 0.95;
        }
        
        /* Form Container */
        .form-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            animation: slideUp 0.5s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .form-header {
            background: linear-gradient(135deg, #FF6600, #FF8C00);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .form-header h2 {
            font-size: 1.5rem;
            margin-bottom: 5px;
            font-weight: 700;
        }
        
        .form-header p {
            font-size: 0.85rem;
            opacity: 0.95;
        }
        
        .form-body {
            padding: 25px;
        }
        
        .form-section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .form-section:last-child {
            border-bottom: none;
        }
        
        .form-section h3 {
            color: #FF6600;
            font-size: 1.2rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-section h3 i {
            font-size: 1.3rem;
        }
        
        /* Two Column Layout */
        .row-2cols {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        /* Three Column Layout for Gender */
        .row-3cols {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        
        .form-group {
            margin-bottom: 0;
        }
        
        .full-width {
            grid-column: 1 / -1;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
            font-size: 0.9rem;
        }
        
        .required {
            color: #dc3545;
            margin-left: 5px;
        }
        
        .form-control, .form-select {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.9rem;
            font-family: 'Mukta', sans-serif;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: #FF6600;
            box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.1);
        }
        
        .radio-group, .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 8px 0;
        }
        
        .radio-option, .checkbox-option {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        
        .radio-option input, .checkbox-option input {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #FF6600;
        }
        
        .radio-option label, .checkbox-option label {
            margin-bottom: 0;
            cursor: pointer;
            font-weight: normal;
            font-size: 0.9rem;
        }
        
        .checkbox-group-multiple {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            padding: 10px 0;
        }
        
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #FF6600, #FF8C00);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Mukta', sans-serif;
            margin-bottom: 10px;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255, 102, 0, 0.4);
        }
        
        .btn-reset {
            width: 100%;
            padding: 12px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Mukta', sans-serif;
        }
        
        .btn-reset:hover {
            background: #5a6268;
        }
        
        /* Footer Styles */
        .main-footer {
            background: #2c3e50;
            color: white;
            padding: 20px 0 15px;
            margin-top: 40px;
            border-radius: 10px;
        }
        
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            text-align: center;
        }
        
        .footer-container p {
            margin-bottom: 8px;
            opacity: 0.8;
            font-size: 0.8rem;
        }
        
        /* Alert Messages */
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        
        /* Loading Spinner */
        .loading {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        
        .loading.active {
            display: flex;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #FF6600;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .form-body {
                padding: 15px;
            }
            
            .form-header h2 {
                font-size: 1.2rem;
            }
            
            .form-section h3 {
                font-size: 1rem;
            }
            
            .row-2cols {
                grid-template-columns: 1fr;
                gap: 15px;
                margin-bottom: 15px;
            }
            
            .row-3cols {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .radio-group, .checkbox-group-multiple {
                flex-direction: column;
                gap: 8px;
            }
            
            .form-group {
                margin-bottom: 15px;
            }
            
            .btn-submit, .btn-reset {
                padding: 12px;
                font-size: 1rem;
            }
        }
        
        @media (min-width: 769px) and (max-width: 1024px) {
            .form-body {
                padding: 20px;
            }
            
            .row-2cols {
                gap: 15px;
            }
        }
        
        /* Small screens */
        @media (max-width: 480px) {
            .form-header {
                padding: 15px;
            }
            
            .form-header h2 {
                font-size: 1rem;
            }
            
            .form-section h3 {
                font-size: 0.95rem;
            }
            
            .form-control, .form-select {
                padding: 8px 10px;
                font-size: 0.85rem;
            }
            
            label {
                font-size: 0.85rem;
            }
        }
        
        /* Gender section styling */
        .gender-section {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 10px;
            margin-top: 5px;
        }
        
        .gender-section .radio-group {
            margin-bottom: 0;
        }
        
        /* Helper text */
        .helper-text {
            font-size: 0.7rem;
            color: #6c757d;
            margin-top: 5px;
        }
    </style>
</head>
<body>

<!-- Loading Spinner -->
<div class="loading" id="loadingSpinner">
    <div class="spinner"></div>
</div>

<!-- Header -->
<header class="main-header">
    <div class="header-container">
        <div class="logo-area">
            <h1><i class="fas fa-users"></i> अमृत परिवार नोंदणी</h1>
            <p>अमृत महाराष्ट्र - आपल्या परिवाराची नोंदणी करा</p>
        </div>
    </div>
</header>

<!-- Main Form Container -->
<div class="form-container">
    <div class="form-header">
        <h2><i class="fas fa-pen-alt"></i> अमृत परिवार नोंदणी फॉर्म</h2>
        <p>कृपया सर्व माहिती अचूकपणे भरा</p>
    </div>
    
    <div class="form-body">
        <?php if ($success_message): ?>
            <div class="alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="registrationForm" onsubmit="showLoading()">
            <!-- Section 1: Personal Information -->
            <div class="form-section">
                <h3><i class="fas fa-user-circle"></i> वैयक्तिक माहिती</h3>
                
                <!-- Full Name - Full Width -->
                <div class="form-group full-width">
                    <label>कुटुंब प्रमुखाचे संपूर्ण नाव <span class="required">*</span></label>
                    <input type="text" class="form-control" name="family_head_name" required 
                           value="<?php echo htmlspecialchars($_POST['family_head_name'] ?? ''); ?>"
                           placeholder="उदा. रामेश्वर महादेव पाटील">
                </div>
                
                <!-- Age and Gender in Two Columns -->
                <div class="row-2cols">
                    <div class="form-group">
                        <label>वय <span class="required">*</span></label>
                        <input type="number" class="form-control" name="age" required min="1" max="120"
                               value="<?php echo htmlspecialchars($_POST['age'] ?? ''); ?>"
                               placeholder="वय (१ ते १२०)">
                    </div>
                    
                    <div class="form-group">
                        <label>लिंग <span class="required">*</span></label>
                        <div class="gender-section">
                            <div class="radio-group">
                                <label class="radio-option">
                                    <input type="radio" name="gender" value="स्त्री" <?php echo (($_POST['gender'] ?? '') == 'स्त्री') ? 'checked' : ''; ?> required>
                                    <span>स्त्री</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="gender" value="पुरुष" <?php echo (($_POST['gender'] ?? '') == 'पुरुष') ? 'checked' : ''; ?>>
                                    <span>पुरुष</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="gender" value="Other" <?php echo (($_POST['gender'] ?? '') == 'Other') ? 'checked' : ''; ?>>
                                    <span>Other</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Caste Category - Full Width -->
                <div class="form-group full-width">
                    <label>जातीचा प्रवर्ग <span class="required">*</span></label>
                    <select class="form-select" name="caste_category" required>
                        <option value="">-- निवडा --</option>
                        <option value="अनुसूचित जाती" <?php echo (($_POST['caste_category'] ?? '') == 'अनुसूचित जाती') ? 'selected' : ''; ?>>अनुसूचित जाती (SC)</option>
                        <option value="अनुसूचित जमाती" <?php echo (($_POST['caste_category'] ?? '') == 'अनुसूचित जमाती') ? 'selected' : ''; ?>>अनुसूचित जमाती (ST)</option>
                        <option value="मागासवर्गीय" <?php echo (($_POST['caste_category'] ?? '') == 'मागासवर्गीय') ? 'selected' : ''; ?>>मागासवर्गीय (OBC)</option>
                        <option value="अत्यंत मागास" <?php echo (($_POST['caste_category'] ?? '') == 'अत्यंत मागास') ? 'selected' : ''; ?>>अत्यंत मागास (VJ/NT)</option>
                        <option value="सामान्य" <?php echo (($_POST['caste_category'] ?? '') == 'सामान्य') ? 'selected' : ''; ?>>सामान्य (General)</option>
                        <option value="इतर" <?php echo (($_POST['caste_category'] ?? '') == 'इतर') ? 'selected' : ''; ?>>इतर</option>
                    </select>
                </div>
            </div>
            
            <!-- Section 2: Address Information -->
            <div class="form-section">
                <h3><i class="fas fa-map-marker-alt"></i> पत्ता माहिती</h3>
                
                <!-- Village Name and Address in Two Columns -->
                <div class="row-2cols">
                    <div class="form-group">
                        <label>गावाचे नाव <span class="required">*</span></label>
                        <input type="text" class="form-control" name="village_name" required
                               value="<?php echo htmlspecialchars($_POST['village_name'] ?? ''); ?>"
                               placeholder="गावाचे नाव">
                    </div>
                    
                    <div class="form-group">
                        <label>पत्ता <span class="required">*</span></label>
                        <input type="text" class="form-control" name="address" required
                               value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>"
                               placeholder="वॉर्ड नंबर, पोस्ट ऑफिस">
                    </div>
                </div>
                
                <!-- Taluka and District in Two Columns -->
                <div class="row-2cols">
                    <div class="form-group">
                        <label>तालुका <span class="required">*</span></label>
                        <select class="form-select" name="taluka" required>
                            <option value="">-- तालुका निवडा --</option>
                            <?php foreach ($talukas as $taluka): ?>
                                <option value="<?php echo htmlspecialchars($taluka['dmarathi']); ?>" 
                                    <?php echo (($_POST['taluka'] ?? '') == $taluka['dmarathi']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($taluka['dmarathi']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>जिल्हा <span class="required">*</span></label>
                        <select class="form-select" name="district" required>
                            <option value="">-- जिल्हा निवडा --</option>
                            <?php foreach ($districts as $district): ?>
                                <option value="<?php echo htmlspecialchars($district['division']); ?>" 
                                    <?php echo (($_POST['district'] ?? '') == $district['division']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($district['marathiname']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Section 3: Contact Information -->
            <div class="form-section">
                <h3><i class="fas fa-phone-alt"></i> संपर्क माहिती</h3>
                
                <!-- Mobile and Email in Two Columns -->
                <div class="row-2cols">
                    <div class="form-group">
                        <label>मोबाईल नंबर <span class="required">*</span></label>
                        <input type="tel" class="form-control" name="mobile_number" required pattern="[0-9]{10}"
                               value="<?php echo htmlspecialchars($_POST['mobile_number'] ?? ''); ?>"
                               placeholder="१० अंकी मोबाईल नंबर">
                        <div class="helper-text">उदा. ९८७६५४३२१०</div>
                    </div>
                    
                    <div class="form-group">
                        <label>ईमेल <span class="required">*</span></label>
                        <input type="email" class="form-control" name="email" required
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                               placeholder="उदा. example@email.com">
                        <div class="helper-text">वैध ईमेल पत्ता प्रविष्ट करा</div>
                    </div>
                </div>
            </div>
            
            <!-- Section 4: Professional Information -->
            <div class="form-section">
                <h3><i class="fas fa-briefcase"></i> व्यावसायिक माहिती</h3>
                
                <!-- Current Occupation -->
                <div class="form-group full-width">
                    <label>सध्या काय करता ? <span class="required">*</span></label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="current_occupation" value="खाजगी नोकरी" <?php echo (($_POST['current_occupation'] ?? '') == 'खाजगी नोकरी') ? 'checked' : ''; ?> required>
                            <span>खाजगी नोकरी</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="current_occupation" value="सरकारी नोकरी" <?php echo (($_POST['current_occupation'] ?? '') == 'सरकारी नोकरी') ? 'checked' : ''; ?>>
                            <span>सरकारी नोकरी</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="current_occupation" value="व्यवसाय" <?php echo (($_POST['current_occupation'] ?? '') == 'व्यवसाय') ? 'checked' : ''; ?>>
                            <span>व्यवसाय</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="current_occupation" value="शेती" <?php echo (($_POST['current_occupation'] ?? '') == 'शेती') ? 'checked' : ''; ?>>
                            <span>शेती</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="current_occupation" value="शिक्षण" <?php echo (($_POST['current_occupation'] ?? '') == 'शिक्षण') ? 'checked' : ''; ?>>
                            <span>शिक्षण</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="current_occupation" value="गृहिणी" <?php echo (($_POST['current_occupation'] ?? '') == 'गृहिणी') ? 'checked' : ''; ?>>
                            <span>गृहिणी</span>
                        </label>
                    </div>
                </div>
                
                <!-- Annual Income -->
                <div class="form-group full-width">
                    <label>कुटुंबाचे वार्षिक उत्पन्न <span class="required">*</span></label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="annual_income" value="रुपये आठ लाख पेक्षा जास्त" <?php echo (($_POST['annual_income'] ?? '') == 'रुपये आठ लाख पेक्षा जास्त') ? 'checked' : ''; ?> required>
                            <span>रुपये आठ लाख पेक्षा जास्त</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="annual_income" value="रुपये आठ लाख पेक्षा कमी" <?php echo (($_POST['annual_income'] ?? '') == 'रुपये आठ लाख पेक्षा कमी') ? 'checked' : ''; ?>>
                            <span>रुपये आठ लाख पेक्षा कमी</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Section 5: Amrut Scheme Information -->
            <div class="form-section">
                <h3><i class="fas fa-hand-holding-heart"></i> अमृत योजना माहिती</h3>
                
                <!-- Want Amrut Benefit -->
                <div class="form-group full-width">
                    <label>अमृत योजनेचा लाभ हवा आहे का ? <span class="required">*</span></label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="want_amrut_benefit" value="हो" <?php echo (($_POST['want_amrut_benefit'] ?? '') == 'हो') ? 'checked' : ''; ?> required>
                            <span>हो</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="want_amrut_benefit" value="नाही" <?php echo (($_POST['want_amrut_benefit'] ?? '') == 'नाही') ? 'checked' : ''; ?>>
                            <span>नाही</span>
                        </label>
                    </div>
                </div>
                
                <!-- Social Media Follow -->
                <div class="form-group full-width">
                    <label>अमृत संस्थेच्या कोणत्या सोशल मीडिया पेजला फॉलो केले आहे ? <span class="required">*</span></label>
                    <div class="checkbox-group-multiple">
                        <label class="checkbox-option">
                            <input type="checkbox" name="social_media_follow[]" value="Facebook" <?php echo (isset($_POST['social_media_follow']) && in_array('Facebook', $_POST['social_media_follow'])) ? 'checked' : ''; ?>>
                            <span>Facebook</span>
                        </label>
                        <label class="checkbox-option">
                            <input type="checkbox" name="social_media_follow[]" value="YouTube" <?php echo (isset($_POST['social_media_follow']) && in_array('YouTube', $_POST['social_media_follow'])) ? 'checked' : ''; ?>>
                            <span>YouTube</span>
                        </label>
                        <label class="checkbox-option">
                            <input type="checkbox" name="social_media_follow[]" value="Instagram" <?php echo (isset($_POST['social_media_follow']) && in_array('Instagram', $_POST['social_media_follow'])) ? 'checked' : ''; ?>>
                            <span>Instagram</span>
                        </label>
                        <label class="checkbox-option">
                            <input type="checkbox" name="social_media_follow[]" value="Website" <?php echo (isset($_POST['social_media_follow']) && in_array('Website', $_POST['social_media_follow'])) ? 'checked' : ''; ?>>
                            <span>Website</span>
                        </label>
                        <label class="checkbox-option">
                            <input type="checkbox" name="social_media_follow[]" value="None of the above" <?php echo (isset($_POST['social_media_follow']) && in_array('None of the above', $_POST['social_media_follow'])) ? 'checked' : ''; ?>>
                            <span>None of the above</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Section 6: Volunteer Information -->
            <div class="form-section">
                <h3><i class="fas fa-hands-helping"></i> स्वयंसेवक माहिती</h3>
                
                <!-- Volunteer Interest -->
                <div class="form-group full-width">
                    <label>तुम्हाला कीवा तुमच्या कुटुंबातील सदस्याला अमृत मित्र / अमृत सखी म्हणून स्वयंसेवी पद्धतीने काम करायचे आहे का? <span class="required">*</span></label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="volunteer_interest" value="होय" <?php echo (($_POST['volunteer_interest'] ?? '') == 'होय') ? 'checked' : ''; ?> required>
                            <span>होय</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="volunteer_interest" value="नाही" <?php echo (($_POST['volunteer_interest'] ?? '') == 'नाही') ? 'checked' : ''; ?>>
                            <span>नाही</span>
                        </label>
                    </div>
                </div>
                
                <!-- Nation Building Participation -->
                <div class="form-group full-width">
                    <label>तुम्ही कीवा तुमच्या कुटुंबातील सदस्य अमृत वर्गाच्या माध्यमातून राष्ट्रनिर्माण च्या कार्यात सहभागी होणार का ? <span class="required">*</span></label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="nation_building_participation" value="हो" <?php echo (($_POST['nation_building_participation'] ?? '') == 'हो') ? 'checked' : ''; ?> required>
                            <span>हो</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="nation_building_participation" value="नाही" <?php echo (($_POST['nation_building_participation'] ?? '') == 'नाही') ? 'checked' : ''; ?>>
                            <span>नाही</span>
                        </label>
                    </div>
                </div>
                
                <!-- Promotion Method -->
                <div class="form-group full-width">
                    <label>तुम्ही कीवा तुमच्या कुटुंबातील सदस्य अमृत च्या योजनांचा प्रचार प्रसार कसा करणार ? <span class="required">*</span></label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="promotion_method" value="सोशल मीडिया द्वारे" <?php echo (($_POST['promotion_method'] ?? '') == 'सोशल मीडिया द्वारे') ? 'checked' : ''; ?> required>
                            <span>सोशल मीडिया द्वारे</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="promotion_method" value="प्रत्यक्ष लोक संपर्कामधून" <?php echo (($_POST['promotion_method'] ?? '') == 'प्रत्यक्ष लोक संपर्कामधून') ? 'checked' : ''; ?>>
                            <span>प्रत्यक्ष लोक संपर्कामधून</span>
                        </label>
                    </div>
                </div>
                
                <!-- Migration Status -->
                <div class="form-group full-width">
                    <label>तुमच्या कुटुंबातील नोकरी व्यवसाय निमित्त कोणी स्थलांतरित झाले आहे का? <span class="required">*</span></label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="migration_status" value="हो" <?php echo (($_POST['migration_status'] ?? '') == 'हो') ? 'checked' : ''; ?> required>
                            <span>हो</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="migration_status" value="नाही" <?php echo (($_POST['migration_status'] ?? '') == 'नाही') ? 'checked' : ''; ?>>
                            <span>नाही</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Submit Buttons -->
            <button type="submit" class="btn-submit">
                <i class="fas fa-paper-plane"></i> नोंदणी करा
            </button>
            <button type="reset" class="btn-reset">
                <i class="fas fa-eraser"></i> फॉर्म रीसेट करा
            </button>
        </form>
    </div>
</div>

<!-- Footer -->
<footer class="main-footer">
    <div class="footer-container">
        <p><i class="fas fa-copyright"></i> <?php echo date('Y'); ?> अमृत महाराष्ट्र. सर्व हक्क राखीव.</p>
        <p><i class="fas fa-envelope"></i> amrutmaharashtraorg@gmail.com | <i class="fas fa-phone"></i> +91 9112226524</p>
        <p><i class="fas fa-globe"></i> https://amrutmaharashtra.org/</p>
    </div>
</footer>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    // Configure Toastr
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    function showLoading() {
        document.getElementById('loadingSpinner').classList.add('active');
    }

    function hideLoading() {
        document.getElementById('loadingSpinner').classList.remove('active');
    }

    // Form validation
    document.getElementById('registrationForm').addEventListener('submit', function(e) {
        // Validate mobile number
        const mobile = document.querySelector('input[name="mobile_number"]').value;
        if (!/^[0-9]{10}$/.test(mobile)) {
            e.preventDefault();
            toastr.error('कृपया 10 अंकी वैध मोबाईल नंबर प्रविष्ट करा');
            return false;
        }
        
        // Validate email
        const email = document.querySelector('input[name="email"]').value;
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            e.preventDefault();
            toastr.error('कृपया वैध ईमेल पत्ता प्रविष्ट करा');
            return false;
        }
        
        // Validate age
        const age = parseInt(document.querySelector('input[name="age"]').value);
        if (isNaN(age) || age < 1 || age > 120) {
            e.preventDefault();
            toastr.error('कृपया 1 ते 120 दरम्यान वैध वय प्रविष्ट करा');
            return false;
        }
        
        // Validate checkbox group
        const checkboxes = document.querySelectorAll('input[name="social_media_follow[]"]:checked');
        if (checkboxes.length === 0) {
            e.preventDefault();
            toastr.error('कृपया किमान एक सोशल मीडिया पेज निवडा');
            return false;
        }
        
        // Validate gender is selected
        const genderSelected = document.querySelector('input[name="gender"]:checked');
        if (!genderSelected) {
            e.preventDefault();
            toastr.error('कृपया लिंग निवडा');
            return false;
        }
        
        showLoading();
    });
    
    // Reset form confirmation
    document.querySelector('.btn-reset').addEventListener('click', function(e) {
        if (!confirm('तुम्हाला खात्री आहे की तुम्हाला फॉर्म रीसेट करायचा आहे? सर्व प्रविष्ट केलेली माहिती हटविली जाईल.')) {
            e.preventDefault();
        }
    });
    
    // Hide loading on page load
    window.addEventListener('load', function() {
        hideLoading();
    });
    
    // Display success message using toastr if exists
    <?php if ($success_message): ?>
    toastr.success('<?php echo $success_message; ?>');
    <?php endif; ?>
    
    <?php if ($error_message): ?>
    toastr.error('<?php echo addslashes($error_message); ?>');
    <?php endif; ?>
</script>
  

</body>
</html>