<!-- URL Format : http://localhost/AmrutMaharashtra/amrut_family_registration.php?ref=1 -->
<?php
// Start output buffering at the very beginning
ob_start();

session_start();
// Get referral source from URL
$referral_source_id = null;

// Check for different URL patterns
if (isset($_GET['ref'])) {
    $referral_source_id = trim($_GET['ref']);
} elseif (isset($_GET['source'])) {
    $referral_source_id = trim($_GET['source']);
} elseif (isset($_GET['id'])) {
    $referral_source_id = trim($_GET['id']);
} else {
    // Pattern: ?123 (direct number after ?)
    $query_string = $_SERVER['QUERY_STRING'];
    if (preg_match('/^\d+$/', $query_string)) {
        $referral_source_id = $query_string;
    }
}

// Store in session to persist through form submission
$_SESSION['referral_source_id'] = $referral_source_id;
include 'components/db_config.php';
include 'components/header.php';
include 'components/formnavbar.php';

// Set timezone to IST
date_default_timezone_set('Asia/Kolkata');

// Fetch districts from database
$districts = [];
$district_query = "SELECT district as division, dmarathi as marathiname FROM mdistrict ORDER BY distid DESC";
$district_result = mysqli_query($conn, $district_query);
if ($district_result && mysqli_num_rows($district_result) > 0) {
    while ($row = mysqli_fetch_assoc($district_result)) {
        $districts[] = $row;
    }
}

// Handle form submission with PRG pattern
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $family_head_name = mysqli_real_escape_string($conn, $_POST['family_head_name'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    $gender = mysqli_real_escape_string($conn, $_POST['gender'] ?? '');
    $caste_category = mysqli_real_escape_string($conn, $_POST['caste_category'] ?? '');
    $caste_other = ($caste_category === 'इतर') ? mysqli_real_escape_string($conn, $_POST['caste_other'] ?? '') : '';
    if ($caste_category === 'इतर' && !empty($caste_other)) {
        $caste_category = $caste_other;
    }
    $village_name = mysqli_real_escape_string($conn, $_POST['village_name'] ?? '');
    $taluka = mysqli_real_escape_string($conn, $_POST['taluka'] ?? '');
    $district = mysqli_real_escape_string($conn, $_POST['district'] ?? '');
    $mobile_number = mysqli_real_escape_string($conn, $_POST['mobile_number'] ?? '');
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $current_occupation = mysqli_real_escape_string($conn, $_POST['current_occupation'] ?? '');
    $current_occupation_other = ($current_occupation === 'इतर') ? mysqli_real_escape_string($conn, $_POST['current_occupation_other'] ?? '') : '';
    if ($current_occupation === 'इतर' && !empty($current_occupation_other)) {
        $current_occupation = $current_occupation_other;
    }
    $annual_income = !empty($_POST['annual_income']) ? mysqli_real_escape_string($conn, $_POST['annual_income']) : null;
    $want_amrut_benefit = !empty($_POST['want_amrut_benefit']) ? mysqli_real_escape_string($conn, $_POST['want_amrut_benefit']) : null;
    $social_media_follow = isset($_POST['social_media_follow']) ? implode(', ', $_POST['social_media_follow']) : '';
    $volunteer_interest = !empty($_POST['volunteer_interest']) ? mysqli_real_escape_string($conn, $_POST['volunteer_interest']) : null;
    $nation_building_participation = !empty($_POST['nation_building_participation']) ? mysqli_real_escape_string($conn, $_POST['nation_building_participation']) : null;
    $promotion_method = isset($_POST['promotion_method']) ? implode(', ', $_POST['promotion_method']) : null;
    $migration_status = !empty($_POST['migration_status']) ? mysqli_real_escape_string($conn, $_POST['migration_status']) : null;
    $amrut_scheme_benefit = mysqli_real_escape_string($conn, $_POST['amrut_scheme_benefit'] ?? '');
    $amrut_scheme_interested = isset($_POST['amrut_scheme_interested']) ? implode(', ', $_POST['amrut_scheme_interested']) : '';
    $govt_scheme_interest = mysqli_real_escape_string($conn, $_POST['govt_scheme_interest'] ?? '');
    $survey_info_source = mysqli_real_escape_string($conn, $_POST['survey_info_source'] ?? '');
    $terms_accepted = isset($_POST['terms_accepted']) ? 1 : 0;
    
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
    if (empty($taluka)) $errors[] = "तालुका आवश्यक आहे";
    if (empty($district)) $errors[] = "जिल्हा निवडा";
    if (!preg_match('/^[0-9]{10}$/', $mobile_number)) $errors[] = "वैध 10 अंकी मोबाईल नंबर आवश्यक आहे";
    if (empty($current_occupation)) $errors[] = "सध्याचे काम निवडा";
    if (empty($social_media_follow)) $errors[] = "सोशल मीडिया पेज निवडा";
    if (empty($survey_info_source)) $errors[] = "सर्वेक्षणाची माहिती कोठून मिळाली ते निवडा";
    if ($terms_accepted != 1) $errors[] = "कृपया नियम व अटी मान्य करा";
    
    if (empty($errors)) {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Get referral source from session
            $referral_source_id = isset($_SESSION['referral_source_id']) ? mysqli_real_escape_string($conn, $_SESSION['referral_source_id']) : null;
           
            // Insert main registration
           $insert_query = "INSERT INTO amrut_family_registration (
                family_head_name, age, gender, caste_category, village_name, address, 
                taluka, district, mobile_number, email, current_occupation, annual_income, 
                want_amrut_benefit, social_media_follow, volunteer_interest, 
                nation_building_participation, promotion_method, migration_status, 
                form_filler_name, ip_address, user_agent, amrut_scheme_benefit, 
                amrut_scheme_interested, govt_scheme_interest, survey_info_source, terms_accepted, referral_source_id
            ) VALUES (
                '$family_head_name', $age, '$gender', '$caste_category', '$village_name', NULL,
                '$taluka', '$district', '$mobile_number', " . ($email ? "'$email'" : "NULL") . ", '$current_occupation', " . ($annual_income ? "'$annual_income'" : "NULL") . ",
                " . ($want_amrut_benefit ? "'$want_amrut_benefit'" : "NULL") . ", '$social_media_follow', " . ($volunteer_interest ? "'$volunteer_interest'" : "NULL") . ",
                " . ($nation_building_participation ? "'$nation_building_participation'" : "NULL") . ", " . ($promotion_method ? "'$promotion_method'" : "NULL") . ", " . ($migration_status ? "'$migration_status'" : "NULL") . ",
                NULL, '$ip_address', '$user_agent', '$amrut_scheme_benefit',
                '$amrut_scheme_interested', '$govt_scheme_interest', '$survey_info_source', $terms_accepted,
                " . ($referral_source_id ? "'$referral_source_id'" : "NULL") . "
            )";
            
            if (mysqli_query($conn, $insert_query)) {
                $registration_id = mysqli_insert_id($conn);
                
                // Insert family members
                if (isset($_POST['member_name']) && is_array($_POST['member_name'])) {
                    $member_names = $_POST['member_name'] ?? [];
                    $member_ages = $_POST['member_age'] ?? [];
                    $member_genders = $_POST['member_gender'] ?? [];
                    $member_relationships = $_POST['member_relationship'] ?? [];
                    $member_occupations = $_POST['member_occupation'] ?? [];
                    
                    for ($i = 0; $i < count($member_names); $i++) {
                        if (!empty($member_names[$i]) && !empty($member_ages[$i])) {
                            $member_name = mysqli_real_escape_string($conn, $member_names[$i]);
                            $member_age = intval($member_ages[$i]);
                            $member_gender = mysqli_real_escape_string($conn, $member_genders[$i] ?? '');
                            $member_relationship = mysqli_real_escape_string($conn, $member_relationships[$i] ?? '');
                            $member_occupation = mysqli_real_escape_string($conn, $member_occupations[$i] ?? '');
                            $member_number = $i + 1;
                            
                            $member_query = "INSERT INTO amrut_family_members (
                                registration_id, member_number, member_name, age, gender, relationship, occupation
                            ) VALUES (
                                $registration_id, $member_number, '$member_name', $member_age, '$member_gender', 
                                '$member_relationship', '$member_occupation'
                            )";
                            
                            if (!mysqli_query($conn, $member_query)) {
                                throw new Exception("Error inserting family member: " . mysqli_error($conn));
                            }
                        }
                    }
                }
                
                mysqli_commit($conn);
                $_SESSION['success_message'] = "आपली नोंदणी यशस्वीरित्या झाली आहे! धन्यवाद.";
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            } else {
                throw new Exception("Error inserting main registration: " . mysqli_error($conn));
            }
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $_SESSION['error_message'] = "नोंदणी करताना त्रुटी आली. कृपया पुन्हा प्रयत्न करा.";
            error_log("Registration error: " . $e->getMessage());
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }
    } else {
        $_SESSION['error_message'] = implode("<br>", $errors);
        $_SESSION['form_data'] = $_POST;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Check for session messages
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
    unset($_SESSION['form_data']);
}
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Get form data if available
$form_data = $_SESSION['form_data'] ?? [];
?>

<style>
/* Additional compact styles */
.form-container {
    max-width: 1200px;
    margin: 0 auto;
    background: white;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-top: 20px;
    margin-bottom: 20px;
}

.form-header {
    background: linear-gradient(135deg, #FF6600, #FF8C00);
    color: white;
    padding: 15px 20px;
    text-align: center;
}

.form-header h2 {
    font-size: 1.3rem;
    margin-bottom: 5px;
    font-weight: 600;
}

.form-header p {
    font-size: 0.8rem;
    opacity: 0.95;
    margin-bottom: 0;
}

.info-row {
    background: #fff3e6;
    padding: 12px 20px;
    text-align: center;
    border-bottom: 1px solid #ffe0cc;
}

.info-row p {
    margin: 0;
    font-size: 0.85rem;
    color: #cc5200;
    font-weight: 500;
}

.form-body {
    padding: 20px;
}

.form-section {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e0e0e0;
}

.form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.form-section h3 {
    color: #FF6600;
    font-size: 1rem;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
}

.form-section h3 i {
    font-size: 1.1rem;
}

.row-2cols {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 15px;
}

.row-4cols {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 15px;
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
    margin-bottom: 5px;
    color: #333;
    font-size: 0.85rem;
}

.required {
    color: #dc3545;
    margin-left: 3px;
}

.form-control, .form-select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 0.85rem;
    font-family: 'Mukta', sans-serif;
    transition: all 0.2s ease;
}

.form-control:focus, .form-select:focus {
    outline: none;
    border-color: #FF6600;
    box-shadow: 0 0 0 2px rgba(255, 102, 0, 0.1);
}

.radio-group, .checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    padding: 5px 0;
}

.radio-option, .checkbox-option {
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
}

.radio-option input, .checkbox-option input {
    width: 16px;
    height: 16px;
    cursor: pointer;
    accent-color: #FF6600;
    margin: 0;
}

.radio-option span, .checkbox-option span {
    font-size: 0.85rem;
}

.checkbox-group-multiple {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    padding: 5px 0;
}

.btn-submit {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #FF6600, #FF8C00);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: 'Mukta', sans-serif;
    margin-bottom: 8px;
}

.btn-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 10px rgba(255, 102, 0, 0.3);
}

.btn-reset {
    width: 100%;
    padding: 10px;
    background: #6c757d;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: 'Mukta', sans-serif;
}

.btn-reset:hover {
    background: #5a6268;
}

.btn-add-member {
    background: #28a745;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 6px;
    font-size: 0.85rem;
    cursor: pointer;
    margin-top: 10px;
    transition: all 0.3s ease;
}

.btn-add-member:hover {
    background: #218838;
}

.btn-remove-member {
    background: #dc3545;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.8rem;
    cursor: pointer;
    margin-top: 10px;
    transition: all 0.3s ease;
}

.btn-remove-member:hover {
    background: #c82333;
}

.alert-success, .alert-error {
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 6px;
    font-size: 0.85rem;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.member-section {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    border-left: 4px solid #FF6600;
}

.member-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.member-header h4 {
    font-size: 1rem;
    color: #FF6600;
    margin: 0;
    font-weight: 600;
}

.terms-section {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    border: 1px solid #e0e0e0;
}

.terms-text {
    max-height: 150px;
    overflow-y: auto;
    padding: 10px;
    background: white;
    border-radius: 6px;
    font-size: 0.8rem;
    line-height: 1.5;
    margin-bottom: 10px;
    border: 1px solid #eee;
}

.mobile-number-hint {
    font-size: 0.7rem;
    color: #6c757d;
    margin-top: 3px;
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
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #FF6600;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 992px) {
    .row-4cols {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
}

@media (max-width: 768px) {
    .form-body {
        padding: 15px;
    }
    
    .row-2cols, .row-4cols {
        grid-template-columns: 1fr;
        gap: 12px;
        margin-bottom: 12px;
    }
    
    .radio-group, .checkbox-group-multiple {
        flex-direction: column;
        gap: 8px;
    }
    
    .form-section h3 {
        font-size: 0.95rem;
        margin-bottom: 12px;
    }
    
    .form-control, .form-select {
        padding: 7px 10px;
        font-size: 0.8rem;
    }
    
    .btn-submit, .btn-reset {
        padding: 10px;
    }
    
    .member-section {
        padding: 12px;
    }
    
    .row-4cols {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .form-header h2 {
        font-size: 1.1rem;
    }
    
    .form-header p {
        font-size: 0.7rem;
    }
    
    .info-row p {
        font-size: 0.75rem;
    }
    
    .form-section h3 {
        font-size: 0.85rem;
    }
    
    .form-control, .form-select {
        padding: 6px 8px;
        font-size: 0.75rem;
    }
    
    .radio-option span, .checkbox-option span {
        font-size: 0.8rem;
    }
}

/* Hide Amrut Benefit section when caste is 'इतर' */
.amrut-benefit-section {
    display: block;
}

.amrut-benefit-section.hide-section {
    display: none;
}
</style>

<div class="container-fluid">
    <div class="form-container">
        <div class="form-header">
            <h2><i class="fas fa-pen-alt"></i> अमृत परिवार सर्वेक्षण २०२६</h2>
            <p>कृपया सर्व माहिती अचूकपणे भरा</p>
        </div>
        
        <div class="info-row">
            <p><i class="fas fa-info-circle"></i> अमृत संस्थेच्या योजना तसेच इतर शासकीय योजनांची माहिती आपल्या पर्यंत पोहोचविण्यासाठी अमृत परिवार सर्वेक्षण २०२६</p>
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
            
            <form method="POST" action="" id="registrationForm">
                <!-- Section 1: Personal Information -->
                <div class="form-section">
                    <h3><i class="fas fa-user-circle"></i> वैयक्तिक माहिती</h3>
                    
                    <div class="row-2cols">
                        <div class="form-group">
                            <label>कुटुंब प्रमुखाचे संपूर्ण नाव <span class="required">*</span></label>
                            <input type="text" class="form-control" name="family_head_name" required 
                                   value="<?php echo htmlspecialchars($form_data['family_head_name'] ?? ''); ?>"
                                   placeholder="उदा. रामेश्वर महादेव पाटील">
                        </div>
                        
                        <div class="form-group">
                            <label>जातीचा प्रवर्ग <span class="required">*</span></label>
                            <select class="form-select" name="caste_category" id="caste_category" required onchange="toggleOtherField('caste'); toggleAmrutBenefitSection(); updateSchemeOptions();">
                                <option value="">-- निवडा --</option>
                                <option value="ब्राह्मण" <?php echo (($form_data['caste_category'] ?? '') == 'ब्राह्मण') ? 'selected' : ''; ?>>ब्राह्मण</option>
                                <option value="कायस्थ" <?php echo (($form_data['caste_category'] ?? '') == 'कायस्थ') ? 'selected' : ''; ?>>कायस्थ</option>
                                <option value="कोमटी/वैश्य" <?php echo (($form_data['caste_category'] ?? '') == 'कोमटी/वैश्य') ? 'selected' : ''; ?>>कोमटी/वैश्य</option>
                                <option value="मारवाडी" <?php echo (($form_data['caste_category'] ?? '') == 'मारवाडी') ? 'selected' : ''; ?>>मारवाडी</option>
                                <option value="पटेल" <?php echo (($form_data['caste_category'] ?? '') == 'पटेल') ? 'selected' : ''; ?>>पटेल</option>
                                <option value="राजपूत" <?php echo (($form_data['caste_category'] ?? '') == 'राजपूत') ? 'selected' : ''; ?>>राजपूत</option>
                                <option value="यलमार" <?php echo (($form_data['caste_category'] ?? '') == 'यलमार') ? 'selected' : ''; ?>>यलमार</option>
                                <option value="अय्यंगार" <?php echo (($form_data['caste_category'] ?? '') == 'अय्यंगार') ? 'selected' : ''; ?>>अय्यंगार</option>
                                <option value="राजपुरोहित" <?php echo (($form_data['caste_category'] ?? '') == 'राजपुरोहित') ? 'selected' : ''; ?>>राजपुरोहित</option>
                                <option value="पाटीदार" <?php echo (($form_data['caste_category'] ?? '') == 'पाटीदार') ? 'selected' : ''; ?>>पाटीदार</option>
                                <option value="नायर" <?php echo (($form_data['caste_category'] ?? '') == 'नायर') ? 'selected' : ''; ?>>नायर</option>
                                <option value="नायडू" <?php echo (($form_data['caste_category'] ?? '') == 'नायडू') ? 'selected' : ''; ?>>नायडू</option>
                                <option value="कम्मा" <?php echo (($form_data['caste_category'] ?? '') == 'कम्मा') ? 'selected' : ''; ?>>कम्मा</option>
                                <option value="कानबी" <?php echo (($form_data['caste_category'] ?? '') == 'कानबी') ? 'selected' : ''; ?>>कानबी</option>
                                <option value="सिंधी" <?php echo (($form_data['caste_category'] ?? '') == 'सिंधी') ? 'selected' : ''; ?>>सिंधी</option>
                                <option value="बनिया" <?php echo (($form_data['caste_category'] ?? '') == 'बनिया') ? 'selected' : ''; ?>>बनिया</option>
                                <option value="बंगाली" <?php echo (($form_data['caste_category'] ?? '') == 'बंगाली') ? 'selected' : ''; ?>>बंगाली</option>
                                <option value="त्यागी" <?php echo (($form_data['caste_category'] ?? '') == 'त्यागी') ? 'selected' : ''; ?>>त्यागी</option>
                                <option value="सेनगूनथर" <?php echo (($form_data['caste_category'] ?? '') == 'सेनगूनथर') ? 'selected' : ''; ?>>सेनगूनथर</option>
                                <option value="गुजराथी" <?php echo (($form_data['caste_category'] ?? '') == 'गुजराथी') ? 'selected' : ''; ?>>गुजराथी</option>
                                <option value="ठाकूर" <?php echo (($form_data['caste_category'] ?? '') == 'ठाकूर') ? 'selected' : ''; ?>>ठाकूर</option>
                                <option value="जाट" <?php echo (($form_data['caste_category'] ?? '') == 'जाट') ? 'selected' : ''; ?>>जाट</option>
                                <option value="लोहाना" <?php echo (($form_data['caste_category'] ?? '') == 'लोहाना') ? 'selected' : ''; ?>>लोहाना</option>
                                <option value="हिंदू नेपाळी" <?php echo (($form_data['caste_category'] ?? '') == 'हिंदू नेपाळी') ? 'selected' : ''; ?>>हिंदू नेपाळी</option>
                                <option value="भूमिहार" <?php echo (($form_data['caste_category'] ?? '') == 'भूमिहार') ? 'selected' : ''; ?>>भूमिहार</option>
                                <option value="इतर" <?php echo (($form_data['caste_category'] ?? '') == 'इतर') ? 'selected' : ''; ?>>इतर</option>
                            </select>
                            <input type="text" class="form-control mt-2" id="caste_other" name="caste_other" style="display:none;" placeholder="कृपया जात प्रवर्ग नमूद करा" value="<?php echo htmlspecialchars($form_data['caste_other'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="row-2cols">
                        <div class="form-group">
                            <label>वय <span class="required">*</span></label>
                            <input type="number" class="form-control" name="age" required min="1" max="120"
                                   value="<?php echo htmlspecialchars($form_data['age'] ?? ''); ?>"
                                   placeholder="वय (१ ते १२०)">
                        </div>
                        
                        <div class="form-group">
                            <label>लिंग <span class="required">*</span></label>
                            <select class="form-select" name="gender" required>
                                <option value="">-- लिंग निवडा --</option>
                                <option value="स्त्री" <?php echo (($form_data['gender'] ?? '') == 'स्त्री') ? 'selected' : ''; ?>>स्त्री</option>
                                <option value="पुरुष" <?php echo (($form_data['gender'] ?? '') == 'पुरुष') ? 'selected' : ''; ?>>पुरुष</option>
                                <option value="Other" <?php echo (($form_data['gender'] ?? '') == 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Section 2: Address Information -->
                <div class="form-section">
                    <h3><i class="fas fa-map-marker-alt"></i> पत्ता माहिती</h3>
                    
                    <div class="row-2cols">
                        <div class="form-group">
                            <label>गावाचे नाव <span class="required">*</span></label>
                            <input type="text" class="form-control" name="village_name" required
                                   value="<?php echo htmlspecialchars($form_data['village_name'] ?? ''); ?>"
                                   placeholder="गावाचे नाव">
                        </div>
                        <div class="form-group">
                            <label>जिल्हा <span class="required">*</span></label>
                            <select class="form-select" name="district" required>
                                <option value="">-- जिल्हा निवडा --</option>
                                <?php foreach ($districts as $district): ?>
                                    <option value="<?php echo htmlspecialchars($district['division']); ?>" 
                                        <?php echo (($form_data['district'] ?? '') == $district['division']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($district['marathiname']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row-2cols">
                        <div class="form-group">
                            <label>तालुका <span class="required">*</span></label>
                            <input type="text" class="form-control" name="taluka" required
                                   value="<?php echo htmlspecialchars($form_data['taluka'] ?? ''); ?>"
                                   placeholder="तालुक्याचे नाव प्रविष्ट करा">
                        </div>
                    </div>
                </div>
                
                <!-- Section 3: Contact Information -->
                <div class="form-section">
                    <h3><i class="fas fa-phone-alt"></i> संपर्क माहिती</h3>
                    
                    <div class="row-2cols">
                        <div class="form-group">
                            <label>मोबाईल नंबर <span class="required">*</span></label>
                            <input type="tel" class="form-control" name="mobile_number" required pattern="[0-9]{10}" maxlength="10"
                                   value="<?php echo htmlspecialchars($form_data['mobile_number'] ?? ''); ?>"
                                   placeholder="१० अंकी मोबाईल नंबर" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10)">
                            <div class="mobile-number-hint">कृपया फक्त 10 अंकी नंबर प्रविष्ट करा</div>
                        </div>
                        
                        <div class="form-group">
                            <label>ईमेल</label>
                            <input type="email" class="form-control" name="email"
                                   value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                                   placeholder="example@email.com">
                        </div>
                    </div>
                </div>
                
                <!-- Section 4: Professional Information -->
                <div class="form-section">
                    <h3><i class="fas fa-briefcase"></i> व्यावसायिक माहिती</h3>
                    
                    <div class="row-2cols">
                        <div class="form-group">
                            <label>सध्या काय करता ? <span class="required">*</span></label>
                            <select class="form-select" name="current_occupation" id="current_occupation" required onchange="toggleOccupationOtherDropdown()">
                                <option value="">-- निवडा --</option>
                                <option value="खाजगी नोकरी" <?php echo (($form_data['current_occupation'] ?? '') == 'खाजगी नोकरी') ? 'selected' : ''; ?>>खाजगी नोकरी</option>
                                <option value="सरकारी नोकरी" <?php echo (($form_data['current_occupation'] ?? '') == 'सरकारी नोकरी') ? 'selected' : ''; ?>>सरकारी नोकरी</option>
                                <option value="व्यवसाय" <?php echo (($form_data['current_occupation'] ?? '') == 'व्यवसाय') ? 'selected' : ''; ?>>व्यवसाय</option>
                                <option value="शेती" <?php echo (($form_data['current_occupation'] ?? '') == 'शेती') ? 'selected' : ''; ?>>शेती</option>
                                <option value="शिक्षण" <?php echo (($form_data['current_occupation'] ?? '') == 'शिक्षण') ? 'selected' : ''; ?>>शिक्षण</option>
                                <option value="गृहिणी" <?php echo (($form_data['current_occupation'] ?? '') == 'गृहिणी') ? 'selected' : ''; ?>>गृहिणी</option>
                                <option value="इतर" <?php echo (($form_data['current_occupation'] ?? '') == 'इतर') ? 'selected' : ''; ?>>इतर</option>
                            </select>
                            <input type="text" class="form-control mt-2" id="occupation_other" name="current_occupation_other" style="display:none;" placeholder="कृपया आपले कार्य नमूद करा" value="<?php echo htmlspecialchars((!in_array($form_data['current_occupation'] ?? '', ['खाजगी नोकरी', 'सरकारी नोकरी', 'व्यवसाय', 'शेती', 'शिक्षण', 'गृहिणी', 'इतर']) ? ($form_data['current_occupation'] ?? '') : '')); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>कुटुंबाचे वार्षिक उत्पन्न</label>
                            <select class="form-select" name="annual_income">
                                <option value="">-- उत्पन्न निवडा --</option>
                                <option value="रुपये आठ लाख पेक्षा जास्त" <?php echo (($form_data['annual_income'] ?? '') == 'रुपये आठ लाख पेक्षा जास्त') ? 'selected' : ''; ?>>रुपये आठ लाख पेक्षा जास्त</option>
                                <option value="रुपये आठ लाख पेक्षा कमी" <?php echo (($form_data['annual_income'] ?? '') == 'रुपये आठ लाख पेक्षा कमी') ? 'selected' : ''; ?>>रुपये आठ लाख पेक्षा कमी</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Section 5: Family Members Information -->
                <div class="form-section">
                    <h3><i class="fas fa-users"></i> कुटुंबातील सदस्यांची माहिती</h3>
                    <div id="family-members-container">
                        <div class="member-section" id="member-1">
                            <div class="member-header">
                                <h4><i class="fas fa-user"></i> सदस्य 1</h4>
                            </div>
                            <div class="row-4cols">
                                <div class="form-group">
                                    <label>नाव</label>
                                    <input type="text" class="form-control" name="member_name[]" 
                                           value="<?php echo htmlspecialchars($form_data['member_name'][0] ?? ''); ?>"
                                           placeholder="सदस्याचे नाव">
                                </div>
                                <div class="form-group">
                                    <label>वय</label>
                                    <input type="number" class="form-control" name="member_age[]" min="1" max="120"
                                           value="<?php echo htmlspecialchars($form_data['member_age'][0] ?? ''); ?>"
                                           placeholder="वय">
                                </div>
                                <div class="form-group">
                                    <label>लिंग</label>
                                    <select class="form-select" name="member_gender[]">
                                        <option value="">-- निवडा --</option>
                                        <option value="स्त्री" <?php echo (($form_data['member_gender'][0] ?? '') == 'स्त्री') ? 'selected' : ''; ?>>स्त्री</option>
                                        <option value="पुरुष" <?php echo (($form_data['member_gender'][0] ?? '') == 'पुरुष') ? 'selected' : ''; ?>>पुरुष</option>
                                        <option value="Other" <?php echo (($form_data['member_gender'][0] ?? '') == 'Other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>कुटुंब प्रमुखाशी नाते</label>
                                    <select class="form-select" name="member_relationship[]">
                                        <option value="">-- निवडा --</option>
                                        <option value="आई" <?php echo (($form_data['member_relationship'][0] ?? '') == 'आई') ? 'selected' : ''; ?>>आई</option>
                                        <option value="बाबा" <?php echo (($form_data['member_relationship'][0] ?? '') == 'बाबा') ? 'selected' : ''; ?>>बाबा</option>
                                        <option value="मुलगा" <?php echo (($form_data['member_relationship'][0] ?? '') == 'मुलगा') ? 'selected' : ''; ?>>मुलगा</option>
                                        <option value="मुलगी" <?php echo (($form_data['member_relationship'][0] ?? '') == 'मुलगी') ? 'selected' : ''; ?>>मुलगी</option>
                                        <option value="भाऊ" <?php echo (($form_data['member_relationship'][0] ?? '') == 'भाऊ') ? 'selected' : ''; ?>>भाऊ</option>
                                        <option value="बहीण" <?php echo (($form_data['member_relationship'][0] ?? '') == 'बहीण') ? 'selected' : ''; ?>>बहीण</option>
                                        <option value="पती" <?php echo (($form_data['member_relationship'][0] ?? '') == 'पती') ? 'selected' : ''; ?>>पती</option>
                                        <option value="पत्नी" <?php echo (($form_data['member_relationship'][0] ?? '') == 'पत्नी') ? 'selected' : ''; ?>>पत्नी</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>सध्या काय करता</label>
                                    <select class="form-select" name="member_occupation[]">
                                        <option value="">-- निवडा --</option>
                                        <option value="खाजगी नोकरी" <?php echo (($form_data['member_occupation'][0] ?? '') == 'खाजगी नोकरी') ? 'selected' : ''; ?>>खाजगी नोकरी</option>
                                        <option value="सरकारी नोकरी" <?php echo (($form_data['member_occupation'][0] ?? '') == 'सरकारी नोकरी') ? 'selected' : ''; ?>>सरकारी नोकरी</option>
                                        <option value="शेती" <?php echo (($form_data['member_occupation'][0] ?? '') == 'शेती') ? 'selected' : ''; ?>>शेती</option>
                                        <option value="व्यवसाय" <?php echo (($form_data['member_occupation'][0] ?? '') == 'व्यवसाय') ? 'selected' : ''; ?>>व्यवसाय</option>
                                        <option value="शिक्षण" <?php echo (($form_data['member_occupation'][0] ?? '') == 'शिक्षण') ? 'selected' : ''; ?>>शिक्षण</option>
                                        <option value="गृहिणी" <?php echo (($form_data['member_occupation'][0] ?? '') == 'गृहिणी') ? 'selected' : ''; ?>>गृहिणी</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-add-member" onclick="addFamilyMember()">
                        <i class="fas fa-plus-circle"></i> कुटुंबातील सदस्य जोडा
                    </button>
                </div>
                
                <!-- Section 6: Amrut Scheme Information -->
                <div class="form-section amrut-benefit-section" id="amrutBenefitSection">
                    <h3><i class="fas fa-hand-holding-heart"></i> अमृत योजना माहिती</h3>
                    
                    <div class="form-group full-width" id="wantAmrutBenefitDiv">
                        <label>अमृत योजनेचा लाभ हवा आहे का ?</label>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="want_amrut_benefit" value="हो" <?php echo (($form_data['want_amrut_benefit'] ?? '') == 'हो') ? 'checked' : ''; ?>>
                                <span>हो</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="want_amrut_benefit" value="नाही" <?php echo (($form_data['want_amrut_benefit'] ?? '') == 'नाही') ? 'checked' : ''; ?>>
                                <span>नाही</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group full-width">
                        <label>अमृत संस्थेच्या कोणत्या सोशल मीडिया पेजला फॉलो केले आहे ? <span class="required">*</span></label>
                        <div class="checkbox-group-multiple">
                            <label class="checkbox-option">
                                <input type="checkbox" name="social_media_follow[]" value="Facebook" <?php echo (isset($form_data['social_media_follow']) && in_array('Facebook', (array)$form_data['social_media_follow'])) ? 'checked' : ''; ?>>
                                <span>Facebook</span>
                            </label>
                            <label class="checkbox-option">
                                <input type="checkbox" name="social_media_follow[]" value="YouTube" <?php echo (isset($form_data['social_media_follow']) && in_array('YouTube', (array)$form_data['social_media_follow'])) ? 'checked' : ''; ?>>
                                <span>YouTube</span>
                            </label>
                            <label class="checkbox-option">
                                <input type="checkbox" name="social_media_follow[]" value="Instagram" <?php echo (isset($form_data['social_media_follow']) && in_array('Instagram', (array)$form_data['social_media_follow'])) ? 'checked' : ''; ?>>
                                <span>Instagram</span>
                            </label>
                            <label class="checkbox-option">
                                <input type="checkbox" name="social_media_follow[]" value="Website" <?php echo (isset($form_data['social_media_follow']) && in_array('Website', (array)$form_data['social_media_follow'])) ? 'checked' : ''; ?>>
                                <span>Website</span>
                            </label>
                            <label class="checkbox-option">
                                <input type="checkbox" name="social_media_follow[]" value="None of the above" <?php echo (isset($form_data['social_media_follow']) && in_array('None of the above', (array)$form_data['social_media_follow'])) ? 'checked' : ''; ?>>
                                <span>None of the above</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group full-width">
                        <label>आपण अमृत च्या कोणत्या योजनेचा लाभ घेण्यास इच्छुक आहात ?</label>
                        <div class="checkbox-group-multiple" id="schemeCheckboxes">
                            <!-- All schemes will be shown/hidden based on caste -->
                        </div>
                    </div>
                    
                    <div class="form-group full-width">
                        <label>इतर शासकीय योजनांची माहिती सोशल मीडिया माध्यमातून जाणून घेण्यास इच्छुक आहात का ?</label>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="govt_scheme_interest" value="हो" <?php echo (($form_data['govt_scheme_interest'] ?? '') == 'हो') ? 'checked' : ''; ?>>
                                <span>हो</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="govt_scheme_interest" value="नाही" <?php echo (($form_data['govt_scheme_interest'] ?? '') == 'नाही') ? 'checked' : ''; ?>>
                                <span>नाही</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Section 7: Volunteer Information -->
                <div class="form-section">
                    <h3><i class="fas fa-hands-helping"></i> स्वयंसेवक माहिती</h3>
                    
                    <div class="alert alert-info" style="background: #e7f3ff; border: 1px solid #b8daff; color: #004085; padding: 10px; border-radius: 6px; margin-bottom: 15px;">
                        <i class="fas fa-info-circle"></i> अमृत या महाराष्ट्र शासनाच्या स्वायत्त संस्थेच्या ध्येय आणि उद्दिष्टांसाठी तसेच अमृत च्या सामाजिक उपक्रमात स्वेच्छेने स्वयंसेवक म्हणून सहभागी होवू इच्छिणाऱ्यानी पुढील माहिती भरावी.
                    </div>
                    
                    <div class="form-group full-width">
                        <label>तुम्हाला किंवा तुमच्या कुटुंबातील सदस्याला शासकीय संस्थेसोबत अमृत मित्र / अमृत सखी म्हणून स्वयंसेवी पद्धतीने काम करायचे आहे का?</label>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="volunteer_interest" value="होय" <?php echo (($form_data['volunteer_interest'] ?? '') == 'होय') ? 'checked' : ''; ?>>
                                <span>होय</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="volunteer_interest" value="नाही" <?php echo (($form_data['volunteer_interest'] ?? '') == 'नाही') ? 'checked' : ''; ?>>
                                <span>नाही</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group full-width">
                        <label>तुम्ही किंवा तुमच्या कुटुंबातील सदस्य अमृत वर्गाच्या माध्यमातून राष्ट्रपुनर्निर्माण च्या कार्यात सहभागी होवू इच्छिता का ?</label>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="nation_building_participation" value="हो" <?php echo (($form_data['nation_building_participation'] ?? '') == 'हो') ? 'checked' : ''; ?>>
                                <span>हो</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="nation_building_participation" value="नाही" <?php echo (($form_data['nation_building_participation'] ?? '') == 'नाही') ? 'checked' : ''; ?>>
                                <span>नाही</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group full-width">
                        <label>तुम्ही कीवा तुमच्या कुटुंबातील सदस्य अमृत च्या योजनांचा प्रचार प्रसार कसा करणार ? (ऐच्छिक)</label>
                        <div class="checkbox-group-multiple">
                            <label class="checkbox-option">
                                   <input type="checkbox" name="promotion_method[]" value="सोशल मीडिया द्वारे" <?php echo (isset($form_data['promotion_method']) && in_array('सोशल मीडिया द्वारे', (array)$form_data['promotion_method'])) ? 'checked' : ''; ?>>
                                <span>सोशल मीडिया द्वारे</span>
                            </label>
                            <label class="checkbox-option">
                                <input type="checkbox" name="promotion_method[]" value="प्रत्यक्ष लोक संपर्कामधून" <?php echo (isset($form_data['promotion_method']) && in_array('प्रत्यक्ष लोक संपर्कामधून', (array)$form_data['promotion_method'])) ? 'checked' : ''; ?>>
                                <span>प्रत्यक्ष लोक संपर्कामधून</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group full-width">
                        <label>तुमच्या कुटुंबातील नोकरी व्यवसाय निमित्त कोणी स्थलांतरित झाले आहे का?</label>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="migration_status" value="हो" <?php echo (($form_data['migration_status'] ?? '') == 'हो') ? 'checked' : ''; ?>>
                                <span>हो</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="migration_status" value="नाही" <?php echo (($form_data['migration_status'] ?? '') == 'नाही') ? 'checked' : ''; ?>>
                                <span>नाही</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group full-width">
                        <label>आपल्याला या सर्वेक्षणाची माहिती कोठून मिळाली ? <span class="required">*</span></label>
                        <select class="form-select" name="survey_info_source" id="survey_info_source" required>
                            <option value="">-- निवडा --</option>
                            <option value="वर्तमानपत्र" <?php echo (($form_data['survey_info_source'] ?? '') == 'वर्तमानपत्र') ? 'selected' : ''; ?>>वर्तमानपत्र</option>
                            <option value="WhatsApp" <?php echo (($form_data['survey_info_source'] ?? '') == 'WhatsApp') ? 'selected' : ''; ?>>WhatsApp</option>
                            <option value="Facebook" <?php echo (($form_data['survey_info_source'] ?? '') == 'Facebook') ? 'selected' : ''; ?>>Facebook</option>
                            <option value="Instagram" <?php echo (($form_data['survey_info_source'] ?? '') == 'Instagram') ? 'selected' : ''; ?>>Instagram</option>
                            <option value="अमृत चे वेबसाईटवरून" <?php echo (($form_data['survey_info_source'] ?? '') == 'अमृत चे वेबसाईटवरून') ? 'selected' : ''; ?>>अमृत चे वेबसाईटवरून</option>
                            <option value="अमृत जिल्हा टीम" <?php echo (($form_data['survey_info_source'] ?? '') == 'अमृत जिल्हा टीम') ? 'selected' : ''; ?>>अमृत जिल्हा टीम</option>
                            <option value="इतर" <?php echo (($form_data['survey_info_source'] ?? '') == 'इतर') ? 'selected' : ''; ?>>इतर</option>
                        </select>
                    </div>
                </div>
                
                <!-- Terms and Conditions -->
                <div class="terms-section">
                    <div class="terms-text">
                        <strong>नियम व अटी:</strong><br>
                        अमृत परिवार सर्वेक्षण 2026 मध्ये मी दिलेली माहिती ही पूर्णपणे स्वेच्छेने आणि जाणीवपूर्वक भरली आहे. ही सर्व माहिती अमृत संस्थेच्या कडे गोपनीय व सुरक्षित राहील, आणि ती फक्त संस्थेच्या अधिकृत हेतूंसाठी, म्हणजेच संस्थेच्या योजना पोहोचवण्यासाठी वापरली जाईल. मला संस्थेच्या सर्व योजना, उपक्रम, आणि सेवा यांची माहिती मिळावी, यासाठी मी माझ्या कुटुंबाची माहिती पुरवली आहे. मी या माहितीचा जबाबदारीने वापर होईल, याची खात्री देतो.
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="terms_accepted" id="terms_accepted" value="1" required>
                        <label class="form-check-label" for="terms_accepted">
                            <strong>मी वरील सर्व नियम व अटी वाचून समजून घेतल्या आहेत आणि त्या मान्य आहेत. <span class="required">*</span></strong>
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> नोंदणी करा
                </button>
                <button type="reset" class="btn-reset">
                    <i class="fas fa-eraser"></i> फॉर्म रीसेट करा
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Loading Spinner -->
<div class="loading" id="loadingSpinner">
    <div class="spinner"></div>
</div>

<!-- jQuery (required for Toastr) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    let memberCount = 1;
    
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
    
    function toggleOtherField(type) {
        if (type === 'caste') {
            const casteSelect = document.getElementById('caste_category');
            const casteOther = document.getElementById('caste_other');
            if (casteSelect.value === 'इतर') {
                casteOther.style.display = 'block';
                casteOther.required = true;
            } else {
                casteOther.style.display = 'none';
                casteOther.required = false;
                casteOther.value = '';
            }
        }
    }
    
    // Toggle Amrut Benefit Section based on caste selection
    function toggleAmrutBenefitSection() {
        const casteSelect = document.getElementById('caste_category');
        const wantAmrutBenefitDiv = document.getElementById('wantAmrutBenefitDiv');
        
        if (casteSelect.value === 'इतर') {
            wantAmrutBenefitDiv.style.display = 'none';
        } else {
            wantAmrutBenefitDiv.style.display = 'block';
        }
    }
    
    // Update scheme options based on caste selection
    function updateSchemeOptions() {
        const casteSelect = document.getElementById('caste_category');
        const schemeContainer = document.getElementById('schemeCheckboxes');
        const isOtherCaste = casteSelect.value === 'इतर';
        
        // All schemes
        const allSchemes = [
            'कौशल्य विकास प्रशिक्षण',
            'वैयक्तिक व्याज परतावा',
            'अमृत ड्रोन पायलट प्रशिक्षण',
            'अमृत पेठ E commerce platform',
            'अमृत पेठ थेट बाजारपेठ',
            'अमृत वर्ग',
            'अमृत पर्यटन',
            'अमृत मानस मित्र'
        ];
        
        // Schemes for "इतर" caste only
        const otherCasteSchemes = [
            'अमृत पेठ E commerce platform',
            'अमृत पेठ थेट बाजारपेठ',
            'अमृत वर्ग',
            'अमृत पर्यटन',
            'अमृत मानस मित्र'
        ];
        
        const schemesToShow = isOtherCaste ? otherCasteSchemes : allSchemes;
        const savedValues = <?php echo isset($form_data['amrut_scheme_interested']) ? json_encode((array)$form_data['amrut_scheme_interested']) : '[]'; ?>;
        
        let html = '<div class="checkbox-group-multiple">';
        schemesToShow.forEach(scheme => {
            const isChecked = savedValues.includes(scheme);
            html += `
                <label class="checkbox-option">
                    <input type="checkbox" name="amrut_scheme_interested[]" value="${scheme}" ${isChecked ? 'checked' : ''}>
                    <span>${scheme}</span>
                </label>
            `;
        });
        html += '</div>';
        
        schemeContainer.innerHTML = html;
    }
    
    // Toggle occupation other field for dropdown
    function toggleOccupationOtherDropdown() {
        const occupationSelect = document.getElementById('current_occupation');
        const occupationOther = document.getElementById('occupation_other');
        
        if (occupationSelect.value === 'इतर') {
            occupationOther.style.display = 'block';
            occupationOther.required = true;
        } else {
            occupationOther.style.display = 'none';
            occupationOther.required = false;
            occupationOther.value = '';
        }
    }
    
    // Initialize toggle on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleOtherField('caste');
        toggleOccupationOtherDropdown();
        toggleAmrutBenefitSection();
        updateSchemeOptions();
        
        const casteSelect = document.getElementById('caste_category');
        if (casteSelect && casteSelect.value === 'इतर') {
            document.getElementById('caste_other').style.display = 'block';
        }
        
        const occupationSelect = document.getElementById('current_occupation');
        if (occupationSelect && occupationSelect.value === 'इतर') {
            document.getElementById('occupation_other').style.display = 'block';
        }
    });

    function addFamilyMember() {
        memberCount++;
        const container = document.getElementById('family-members-container');
        const newMember = document.createElement('div');
        newMember.className = 'member-section';
        newMember.id = `member-${memberCount}`;
        newMember.innerHTML = `
            <div class="member-header">
                <h4><i class="fas fa-user"></i> सदस्य ${memberCount}</h4>
                <button type="button" class="btn-remove-member" onclick="removeFamilyMember(${memberCount})">
                    <i class="fas fa-trash"></i> काढा
                </button>
            </div>
            <div class="row-4cols">
                <div class="form-group">
                    <label>नाव</label>
                    <input type="text" class="form-control" name="member_name[]" placeholder="सदस्याचे नाव">
                </div>
                <div class="form-group">
                    <label>वय</label>
                    <input type="number" class="form-control" name="member_age[]" min="1" max="120" placeholder="वय">
                </div>
                <div class="form-group">
                    <label>लिंग</label>
                    <select class="form-select" name="member_gender[]">
                        <option value="">-- निवडा --</option>
                        <option value="स्त्री">स्त्री</option>
                        <option value="पुरुष">पुरुष</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>कुटुंब प्रमुखाशी नाते</label>
                    <select class="form-select" name="member_relationship[]">
                        <option value="">-- निवडा --</option>
                        <option value="आई">आई</option>
                        <option value="बाबा">बाबा</option>
                        <option value="मुलगा">मुलगा</option>
                        <option value="मुलगी">मुलगी</option>
                        <option value="भाऊ">भाऊ</option>
                        <option value="बहीण">बहीण</option>
                        <option value="पती">पती</option>
                        <option value="पत्नी">पत्नी</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>सध्या काय करता</label>
                    <select class="form-select" name="member_occupation[]">
                        <option value="">-- निवडा --</option>
                        <option value="खाजगी नोकरी">खाजगी नोकरी</option>
                        <option value="सरकारी नोकरी">सरकारी नोकरी</option>
                        <option value="शेती">शेती</option>
                        <option value="व्यवसाय">व्यवसाय</option>
                        <option value="शिक्षण">शिक्षण</option>
                        <option value="गृहिणी">गृहिणी</option>
                    </select>
                </div>
            </div>
        `;
        container.appendChild(newMember);
    }

    function removeFamilyMember(memberId) {
        const memberDiv = document.getElementById(`member-${memberId}`);
        if (memberDiv) {
            memberDiv.remove();
        }
    }

    // Form validation
    document.getElementById('registrationForm').addEventListener('submit', function(e) {
        const mobile = document.querySelector('input[name="mobile_number"]').value;
        if (!/^[0-9]{10}$/.test(mobile)) {
            e.preventDefault();
            toastr.error('कृपया 10 अंकी वैध मोबाईल नंबर प्रविष्ट करा');
            return false;
        }
        
        const age = parseInt(document.querySelector('input[name="age"]').value);
        if (isNaN(age) || age < 1 || age > 120) {
            e.preventDefault();
            toastr.error('कृपया 1 ते 120 दरम्यान वैध वय प्रविष्ट करा');
            return false;
        }
        
        const checkboxes = document.querySelectorAll('input[name="social_media_follow[]"]:checked');
        if (checkboxes.length === 0) {
            e.preventDefault();
            toastr.error('कृपया किमान एक सोशल मीडिया पेज निवडा');
            return false;
        }
        
        const genderSelected = document.querySelector('select[name="gender"]').value;
        if (!genderSelected) {
            e.preventDefault();
            toastr.error('कृपया लिंग निवडा');
            return false;
        }
        
        const surveySource = document.querySelector('select[name="survey_info_source"]').value;
        if (!surveySource) {
            e.preventDefault();
            toastr.error('कृपया सर्वेक्षणाची माहिती कोठून मिळाली ते निवडा');
            return false;
        }
        
        const termsAccepted = document.getElementById('terms_accepted').checked;
        if (!termsAccepted) {
            e.preventDefault();
            toastr.error('कृपया नियम व अटी मान्य करा');
            return false;
        }
        
        showLoading();
    });
    
    document.querySelector('.btn-reset')?.addEventListener('click', function(e) {
        if (!confirm('तुम्हाला खात्री आहे की तुम्हाला फॉर्म रीसेट करायचा आहे?')) {
            e.preventDefault();
        }
    });
    
    window.addEventListener('load', function() {
        hideLoading();
    });
    
    <?php if ($success_message): ?>
    toastr.success('<?php echo addslashes($success_message); ?>');
    <?php endif; ?>
    
    <?php if ($error_message): ?>
    toastr.error('<?php echo addslashes($error_message); ?>');
    <?php endif; ?>
</script>

<?php
include 'components/footer.php';
?>