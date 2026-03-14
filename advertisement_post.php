<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// Login check
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Handle payment gateway return
if (isset($_GET['payment_status'])) {
    if ($_GET['payment_status'] == 'success') {
        if (isset($_GET['txn_id'])) {
            $_SESSION['payment_success_txn'] = $_GET['txn_id'];
        }
        $success = true;
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong>पेमेंट यशस्वी!</strong> तुमची जाहिरात यशस्वीरित्या जोडली गेली आहे.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
    } else if ($_GET['payment_status'] == 'failed') {
        $message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'पेमेंट अयशस्वी';
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>पेमेंट अयशस्वी!</strong> ' . $message . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
    }
}

// Include database connection and header
include 'components/db_config.php';
include 'components/header.php';
include 'components/navbar.php';
include 'components/login_navbar.php';

// Configuration
define('PRIMARY_ADS_PATH', 'components/primary_advertised/');
define('SECONDARY_ADS_PATH', 'components/secondary_advertised/');
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);

// Price configuration for different durations
define('BIG_AD_PRICE_10', 1500);
define('BIG_AD_PRICE_20', 2500);
define('BIG_AD_PRICE_30', 3000);

define('SMALL_AD_PRICE_10', 1);
define('SMALL_AD_PRICE_20', 1500);
define('SMALL_AD_PRICE_30', 2000);

// Duration in days
define('DURATION_10', 10);
define('DURATION_20', 20);
define('DURATION_30', 30);

// Ensure upload directories exist
if (!is_dir(PRIMARY_ADS_PATH)) mkdir(PRIMARY_ADS_PATH, 0755, true);
if (!is_dir(SECONDARY_ADS_PATH)) mkdir(SECONDARY_ADS_PATH, 0755, true);

$errors = [];
$success = false;
$payment_redirect = false;
$encrypted_data = '';

$clientCode='ACAD914';   // Please use the credentials shared by your Account Manager
$username='amrut.gom-4@gmail.com';     // Please use the credentials shared by your Account Manager
$password='ACAD914_SP25756';     // Please use the credentials shared by your Account Manager
$authKey='VkylGulAs8ysjQcwDU7vHCbSDz+05lxxh43s13/+P1A=';      // Please use the credentials shared by your Account Manager
$authIV='5rTyHyY/FDpKUCpiFe+d5K2XkDkXCb99v+5GDWwnoK2KFPIVq629dikwYbluXXze';       // Please use the credentials shared by your Account Manager

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if payment gateway is selected
    $payment_method = trim($_POST['payment_method'] ?? '');
    
    if ($payment_method === 'Payment Gateway') {
        // For payment gateway, validate and prepare for redirect
        $client_name = trim($_POST['client_name'] ?? '');
        $mobile_number = trim($_POST['mobile_number'] ?? '');
        $client_email = trim($_POST['client_email'] ?? '');
        $full_address = trim($_POST['full_address'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $district = trim($_POST['district'] ?? '');
        $business_type = trim($_POST['business_type'] ?? '');
        $ad_title = trim($_POST['ad_title'] ?? '');
        $ad_type = isset($_POST['ad_type']) ? (int)$_POST['ad_type'] : 0;
        $duration = isset($_POST['duration']) ? (int)$_POST['duration'] : 0;
        
        // Calculate amount based on ad type and duration
        if ($ad_type == 1) {
            $amount = ($duration == DURATION_10) ? BIG_AD_PRICE_10 : 
                     (($duration == DURATION_20) ? BIG_AD_PRICE_20 : 
                     (($duration == DURATION_30) ? BIG_AD_PRICE_30 : 0));
        } else {
            $amount = ($duration == DURATION_10) ? SMALL_AD_PRICE_10 : 
                     (($duration == DURATION_20) ? SMALL_AD_PRICE_20 : 
                     (($duration == DURATION_30) ? SMALL_AD_PRICE_30 : 0));
        }
        
        // Basic validation for payment gateway
        if (empty($client_name)) $errors[] = 'ग्राहकाचे नाव आवश्यक आहे.';
        if (empty($mobile_number)) $errors[] = 'मोबाईल नंबर आवश्यक आहे.';
        if (!preg_match('/^[0-9]{10}$/', $mobile_number)) $errors[] = 'वैध 10-अंकी मोबाईल नंबर प्रविष्ट करा.';
        if (empty($business_type)) $errors[] = 'व्यवसाय प्रकार आवश्यक आहे.';
        if (empty($full_address)) $errors[] = 'संपूर्ण पत्ता आवश्यक आहे.';
        if (empty($state)) $errors[] = 'राज्य आवश्यक आहे.';
        if (empty($district)) $errors[] = 'जिल्हा आवश्यक आहे.';
        if (empty($ad_title)) $errors[] = 'जाहिरात शीर्षक आवश्यक आहे.';
        if (!in_array($ad_type, [1, 2])) $errors[] = 'वैध प्रकार निवडा.';
        if (!in_array($duration, [DURATION_10, DURATION_20, DURATION_30])) $errors[] = 'वैध कालावधी निवडा.';
        
        // Validate file upload for payment gateway
        if (isset($_FILES['ad_image']) && $_FILES['ad_image']['error'] === UPLOAD_ERR_OK) {
            $file_ext = strtolower(pathinfo($_FILES['ad_image']['name'], PATHINFO_EXTENSION));
            $file_size = $_FILES['ad_image']['size'];
            
            if (!in_array($file_ext, ALLOWED_EXTENSIONS)) {
                $errors[] = 'फक्त JPG, JPEG, PNG, WEBP फाइल्स स्वीकारल्या जातात.';
            } elseif ($file_size > MAX_FILE_SIZE) {
                $errors[] = 'फाइल साइज 2MB पेक्षा कमी असावी.';
            }
        } else {
            $errors[] = 'जाहिरात प्रतिमा आवश्यक आहे.';
        }
        
        if (empty($errors)) {
            // Store form data in session for after payment
            $_SESSION['pending_ad_data'] = $_POST;
            $_SESSION['pending_ad_image'] = $_FILES['ad_image'];
            
            // Prepare payment gateway data
            $payerName = $client_name;
            $payerEmail = !empty($client_email) ? $client_email : 'customer@email.com';
            $payerMobile = $mobile_number;
            $payerAddress = $full_address . ', ' . $district . ', ' . $state;
            
            $clientTxnId = time() . rand(1000, 9999);
            $amountType = 'INR';
            $mcc = 5137;
            $channelId = 'W';

            // $callbackUrl = 'payment_gatway/SabPaisaPostPgResponse.php';
            // FULL absolute URL
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'];
            $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
            $callbackUrl = $protocol . $host . $basePath . '/payment_gatway/SabPaisaPostPgResponse.php';

            
            $encData = "?clientCode=" . $clientCode . 
                      "&transUserName=" . $username . 
                      "&transUserPassword=" . $password . 
                      "&payerName=" . urlencode($payerName) . 
                      "&payerMobile=" . $payerMobile . 
                      "&payerEmail=" . urlencode($payerEmail) . 
                      "&payerAddress=" . urlencode($payerAddress) . 
                      "&clientTxnId=" . $clientTxnId . 
                      "&amount=" . $amount . 
                      "&amountType=" . $amountType . 
                      "&mcc=" . $mcc . 
                      "&channelId=" . $channelId . 
                      "&callbackUrl=" . urlencode($callbackUrl);
            
            // Include the encryption class
            include 'payment_gatway/Authentication.php';
            $AES256HMACSHA384HEX = new AES256HMACSHA384HEX();
            $encrypted_data = $AES256HMACSHA384HEX->encrypt($authKey, $authIV, $encData);
            $payment_redirect = true;
        }
    } else {
        // Regular form processing (Cheque, Online Transfer, UPI)
        // Retrieve and sanitize inputs
        $client_name    = trim($_POST['client_name'] ?? '');
        $client_email   = trim($_POST['client_email'] ?? '');
        $mobile_number  = trim($_POST['mobile_number'] ?? '');
        $business_type  = trim($_POST['business_type'] ?? '');
        $full_address   = trim($_POST['full_address'] ?? '');
        $state          = trim($_POST['state'] ?? '');
        $district       = trim($_POST['district'] ?? '');
        $ad_title       = trim($_POST['ad_title'] ?? '');
        $ad_link        = trim($_POST['ad_link'] ?? '');
        $ad_type        = isset($_POST['ad_type']) ? (int)$_POST['ad_type'] : 0;
        $duration       = isset($_POST['duration']) ? (int)$_POST['duration'] : 0;
        $payment_method = trim($_POST['payment_method'] ?? '');
        $transaction_id = trim($_POST['transaction_id'] ?? '');
        
        // Calculate price based on ad type and duration
        if ($ad_type == 1) {
            $price = ($duration == DURATION_10) ? BIG_AD_PRICE_10 : 
                    (($duration == DURATION_20) ? BIG_AD_PRICE_20 : 
                    (($duration == DURATION_30) ? BIG_AD_PRICE_30 : 0));
        } else {
            $price = ($duration == DURATION_10) ? SMALL_AD_PRICE_10 : 
                    (($duration == DURATION_20) ? SMALL_AD_PRICE_20 : 
                    (($duration == DURATION_30) ? SMALL_AD_PRICE_30 : 0));
        }
        
        $start_date     = $_POST['start_date'] ?? '';
        $end_date       = $_POST['end_date'] ?? '';
        $created_by     = $_SESSION['name'] ?? 'Admin';

        // Validation
        if (empty($client_name)) $errors[] = 'ग्राहकाचे नाव आवश्यक आहे.';
        if (empty($mobile_number)) $errors[] = 'मोबाईल नंबर आवश्यक आहे.';
        if (!preg_match('/^[0-9]{10}$/', $mobile_number)) $errors[] = 'वैध 10-अंकी मोबाईल नंबर प्रविष्ट करा.';
        if (empty($business_type)) $errors[] = 'व्यवसाय प्रकार आवश्यक आहे.';
        if (empty($full_address)) $errors[] = 'संपूर्ण पत्ता आवश्यक आहे.';
        if (empty($state)) $errors[] = 'राज्य आवश्यक आहे.';
        if (empty($district)) $errors[] = 'जिल्हा आवश्यक आहे.';
        if (empty($ad_title)) $errors[] = 'जाहिरात शीर्षक आवश्यक आहे.';
        if (!in_array($ad_type, [1, 2])) $errors[] = 'वैध प्रकार निवडा.';
        if (!in_array($duration, [DURATION_10, DURATION_20, DURATION_30])) $errors[] = 'वैध कालावधी निवडा.';
        if (empty($payment_method)) $errors[] = 'पेमेंट पद्धत निवडा.';
        if (empty($transaction_id)) $errors[] = 'ट्रांझॅक्शन ID आवश्यक आहे.';
        if (empty($start_date)) $errors[] = 'सुरु तारीख आवश्यक आहे.';
        
        // Auto-calculate end date based on duration
        if (!empty($start_date) && $duration > 0) {
            $date = new DateTime($start_date);
            $date->modify('+' . $duration . ' days');
            $end_date = $date->format('Y-m-d');
        }

        // File upload handling
        $image_name = '';
        if (isset($_FILES['ad_image']) && $_FILES['ad_image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp  = $_FILES['ad_image']['tmp_name'];
            $file_size = $_FILES['ad_image']['size'];
            $file_ext  = strtolower(pathinfo($_FILES['ad_image']['name'], PATHINFO_EXTENSION));

            if (!in_array($file_ext, ALLOWED_EXTENSIONS)) {
                $errors[] = 'फक्त JPG, JPEG, PNG, WEBP फाइल्स स्वीकारल्या जातात.';
            } elseif ($file_size > MAX_FILE_SIZE) {
                $errors[] = 'फाइल साइज 2MB पेक्षा कमी असावी.';
            } else {
                $image_name = time() . '_' . uniqid() . '.' . $file_ext;
                $upload_dir = ($ad_type == 1) ? PRIMARY_ADS_PATH : SECONDARY_ADS_PATH;
                $upload_path = $upload_dir . $image_name;

                if (!move_uploaded_file($file_tmp, $upload_path)) {
                    $errors[] = 'फाइल अपलोड करताना त्रुटी.';
                    $image_name = '';
                }
            }
        } else {
            $errors[] = 'जाहिरात प्रतिमा आवश्यक आहे.';
        }

        // If no errors, insert into database
        if (empty($errors)) {
            $payment_status = 1; // 1 for paid, 0 for pending
            
            $sql = "INSERT INTO ads_management 
                    (client_name, client_email, mobile_number, business_type, full_address, state, district, ad_title, image_name, ad_link, ad_type, duration, payment_method, transaction_id, price, start_date, end_date, created_by, payment_status, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
            
            $stmt = $conn->prepare($sql);
            
            $stmt->bind_param(
                'ssssssssssiisssdssi',
                $client_name,
                $client_email,
                $mobile_number,
                $business_type,
                $full_address,
                $state,
                $district,
                $ad_title,
                $image_name,
                $ad_link,
                $ad_type,
                $duration,
                $payment_method,
                $transaction_id,
                $price,
                $start_date,
                $end_date,
                $created_by,
                $payment_status
            );

            if ($stmt->execute()) {
                $success = true;
            } else {
                $errors[] = 'डेटाबेसमध्ये त्रुटी: ' . $conn->error;
                if (!empty($image_name) && file_exists($upload_path)) {
                    unlink($upload_path);
                }
            }
        }
    }
}

// Handle payment gateway return (you need to implement this based on your callback)
if (isset($_GET['payment_status']) && $_GET['payment_status'] == 'success' && isset($_SESSION['pending_ad_data'])) {
    // Process the pending ad data after successful payment
    // This should be implemented based on your payment gateway callback
    // Similar to the above insertion but with payment_status = 1
}

?>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-orange text-white">
            <h5 class="mb-0"><i class="bi bi-plus-circle"></i> नवीन जाहिरात जोडा</h5>
        </div>
        <div class="card-body">
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    जाहिरात यशस्वीरित्या जोडली गेली. 
                    <a href="advertisement_post.php" class="alert-link">आणखी एक जोडा</a> 
                    <!-- किंवा  -->
                    <!-- <a href="advertisement_management.php" class="alert-link">व्यवस्थापनाकडे जा</a>. -->
                </div>
            <?php elseif ($payment_redirect): ?>
                <div class="text-center py-4">
                    <div class="spinner-border text-orange mb-3" style="color: #FF6600;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5>पेमेंट गेटवे वर पुनर्निर्देशित होत आहे...</h5>
                    <p>कृपया प्रतीक्षा करा</p>
                    <!-- <form action="https://stage-securepay.sabpaisa.in/SabPaisa/sabPaisaInit?v=1" method="post" id="paymentForm"> -->
                    <form action="https://securepay.sabpaisa.in/SabPaisa/sabPaisaInit?v=1" method="post" id="paymentForm">                        
                        <input type="hidden" name="encData" value="<?php echo $encrypted_data; ?>">
                        <input type="hidden" name="clientCode" value="<?php echo $clientCode; ?>">
                        <noscript>
                            <button type="submit" class="btn btn-orange">पेमेंट पेज वर जा</button>
                        </noscript>
                    </form>
                    <script>document.getElementById('paymentForm').submit();</script>
                </div>
            <?php else: ?>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" id="adForm">
                    <!-- Client Information Section -->
                    <h6 class="text-muted border-bottom pb-2 mb-3">
                        <i class="bi bi-person-badge me-2"></i>ग्राहक माहिती / Client Information
                    </h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ग्राहकाचे नाव *</label>
                            <input type="text" name="client_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['client_name'] ?? ''); ?>" 
                                   placeholder="ग्राहकाचे पूर्ण नाव" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ईमेल</label>
                            <input type="email" name="client_email" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['client_email'] ?? ''); ?>" 
                                   placeholder="email@example.com">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">मोबाईल नंबर *</label>
                            <input type="tel" name="mobile_number" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['mobile_number'] ?? ''); ?>" 
                                   pattern="[0-9]{10}" maxlength="10" 
                                   placeholder="9876543210" required>
                            <small class="text-muted">10 अंकी मोबाईल नंबर टाका</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">व्यवसाय प्रकार *</label>
                            <select name="business_type" class="form-select" required>
                                <option value="">-- व्यवसाय निवडा --</option>
                                <option value="Retail" <?php echo (isset($_POST['business_type']) && $_POST['business_type'] == 'Retail') ? 'selected' : ''; ?>>रिटेल / Retail</option>
                                <option value="Education" <?php echo (isset($_POST['business_type']) && $_POST['business_type'] == 'Education') ? 'selected' : ''; ?>>शिक्षण / Education</option>
                                <option value="Healthcare" <?php echo (isset($_POST['business_type']) && $_POST['business_type'] == 'Healthcare') ? 'selected' : ''; ?>>आरोग्य / Healthcare</option>
                                <option value="Real Estate" <?php echo (isset($_POST['business_type']) && $_POST['business_type'] == 'Real Estate') ? 'selected' : ''; ?>>रिअल इस्टेट / Real Estate</option>
                                <option value="Automobile" <?php echo (isset($_POST['business_type']) && $_POST['business_type'] == 'Automobile') ? 'selected' : ''; ?>>ऑटोमोबाईल / Automobile</option>
                                <option value="Food" <?php echo (isset($_POST['business_type']) && $_POST['business_type'] == 'Food') ? 'selected' : ''; ?>>खाद्यपदार्थ / Food</option>
                                <option value="Other" <?php echo (isset($_POST['business_type']) && $_POST['business_type'] == 'Other') ? 'selected' : ''; ?>>इतर / Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label">संपूर्ण पत्ता *</label>
                            <textarea name="full_address" class="form-control" rows="3" 
                                      placeholder="घर क्रमांक, रस्ता, शहर, पिनकोड" 
                                      required><?php echo htmlspecialchars($_POST['full_address'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <!-- State and District Section with Dropdowns -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">राज्य *</label>
                            <select name="state" id="state" class="form-select" required onchange="loadDistricts()">
                                <option value="">-- राज्य निवडा / Select State --</option>
                                <?php if (isset($_POST['state']) && !empty($_POST['state'])): ?>
                                    <option value="<?php echo htmlspecialchars($_POST['state']); ?>" selected>
                                        <?php echo htmlspecialchars($_POST['state']); ?>
                                    </option>
                                <?php endif; ?>
                            </select>
                            <small class="text-muted">भारतातील राज्ये / Indian States</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">जिल्हा *</label>
                            <select name="district" id="district" class="form-select" required>
                                <option value="">-- जिल्हा निवडा / Select District --</option>
                                <?php if (isset($_POST['district']) && !empty($_POST['district'])): ?>
                                    <option value="<?php echo htmlspecialchars($_POST['district']); ?>" selected>
                                        <?php echo htmlspecialchars($_POST['district']); ?>
                                    </option>
                                <?php endif; ?>
                            </select>
                            <small class="text-muted">निवडलेल्या राज्यातील जिल्हे / Districts of selected state</small>
                        </div>
                    </div>
                    
                    <!-- Advertisement Details Section -->
                    <h6 class="text-muted border-bottom pb-2 mb-3 mt-4">
                        <i class="bi bi-megaphone me-2"></i>जाहिरात माहिती / Advertisement Details
                    </h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">जाहिरात शीर्षक *</label>
                            <input type="text" name="ad_title" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['ad_title'] ?? ''); ?>" 
                                   placeholder="जाहिरात शीर्षक" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">लिंक</label>
                            <input type="url" name="ad_link" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['ad_link'] ?? ''); ?>" 
                                   placeholder="https://example.com">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">प्रकार *</label>
                            <select name="ad_type" id="ad_type" class="form-select" required onchange="updateDurationOptions()">
                                <option value="">-- प्रकार निवडा --</option>
                                <option value="1" <?php echo (isset($_POST['ad_type']) && $_POST['ad_type'] == 1) ? 'selected' : ''; ?>>मोठी जाहिरात / Big Ad</option>
                                <option value="2" <?php echo (isset($_POST['ad_type']) && $_POST['ad_type'] == 2) ? 'selected' : ''; ?>>छोटी जाहिरात / Small Ad</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">कालावधी *</label>
                            <select name="duration" id="duration" class="form-select" required onchange="updatePriceAndEndDate()">
                                <option value="">-- कालावधी निवडा --</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">किंमत (₹) *</label>
                            <input type="number" name="price" id="price" class="form-control bg-light" 
                                   value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" 
                                   readonly placeholder="प्रकार व कालावधी निवडा" style="background-color:#f8f9fa; font-weight:600; color:#28a745;">
                            <small class="text-muted">प्रकार व कालावधीनुसार किंमत स्वयं-निर्धारित</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">प्रतिमा *</label>
                            <input type="file" name="ad_image" class="form-control" 
                                   accept=".jpg,.jpeg,.png,.webp" required>
                            <small class="text-muted">जास्तीत जास्त 2MB, फक्त jpg/png/webp</small>
                        </div>
                    </div>
                    
                    <!-- Payment Section -->
                    <h6 class="text-muted border-bottom pb-2 mb-3 mt-4">
                        <i class="bi bi-credit-card me-2"></i>पेमेंट माहिती / Payment Details
                    </h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">पेमेंट पद्धत *</label>
                            <select name="payment_method" id="payment_method" class="form-select" required onchange="toggleTransactionId()">
                                <option value="">-- पद्धत निवडा --</option>                            
                                <option value="Cheque" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'Cheque') ? 'selected' : ''; ?>>धनादेश / Cheque</option>
                                <option value="Online Transfer" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'Online Transfer') ? 'selected' : ''; ?>>ऑनलाईन ट्रान्सफर / Online Transfer</option>
                                <option value="UPI" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'UPI') ? 'selected' : ''; ?>>UPI</option>
                                <option value="Payment Gateway" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'Payment Gateway') ? 'selected' : ''; ?>>💳 पेमेंट गेटवे / Payment Gateway</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3" id="transaction_id_container">
                            <label class="form-label">ट्रांझॅक्शन ID *</label>
                            <input type="text" name="transaction_id" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['transaction_id'] ?? ''); ?>" 
                                   placeholder="TX123456789" required>
                        </div>
                    </div>
                    
                    <!-- Date Section -->
                    <h6 class="text-muted border-bottom pb-2 mb-3 mt-4">
                        <i class="bi bi-calendar me-2"></i>तारखा / Dates
                    </h6>
                    
                    <div class="alert alert-info py-2 mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>जाहिरात कालावधी:</strong> निवडलेल्या कालावधीनुसार शेवटची तारीख आपोआप मोजली जाईल
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">सुरु तारीख *</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>" 
                                   onchange="updateEndDate()" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">शेवट तारीख *</label>
                            <input type="date" name="end_date" id="end_date" class="form-control bg-light" 
                                   value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>" 
                                   readonly required style="background-color:#f8f9fa;">
                            <small class="text-muted">सुरु तारखेनंतर निवडलेल्या कालावधीनुसार</small>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="row mt-4">
                        <div class="col-12 text-end">
                            <a href="advertisement_management.php" class="btn btn-secondary me-2">
                                <i class="bi bi-x-circle"></i> रद्द करा
                            </a>
                            <button type="submit" class="btn btn-orange">
                                <i class="bi bi-check-circle"></i> जाहिरात जोडा
                            </button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .bg-orange { background-color: #FF6600; }
    .btn-orange {
        background-color: #FF6600;
        color: white;
    }
    .btn-orange:hover {
        background-color: #e65c00;
        color: white;
    }
    .text-muted.border-bottom {
        color: #FF6600 !important;
        border-bottom-color: #FF6600 !important;
        font-weight: 600;
    }
    .form-control[readonly] {
        background-color: #f8f9fa;
        border-color: #dee2e6;
    }
    .alert-info {
        background-color: #e7f1ff;
        border-color: #b8daff;
        color: #004085;
    }
</style>

<script>
// Duration and price mapping
const durationPrices = {
    1: { // Big Ad
        10: <?php echo BIG_AD_PRICE_10; ?>,
        20: <?php echo BIG_AD_PRICE_20; ?>,
        30: <?php echo BIG_AD_PRICE_30; ?>
    },
    2: { // Small Ad
        10: <?php echo SMALL_AD_PRICE_10; ?>,
        20: <?php echo SMALL_AD_PRICE_20; ?>,
        30: <?php echo SMALL_AD_PRICE_30; ?>
    }
};

// Update duration options based on ad type
function updateDurationOptions() {
    const adType = document.getElementById('ad_type').value;
    const durationSelect = document.getElementById('duration');
    const priceField = document.getElementById('price');
    
    // Clear current options
    durationSelect.innerHTML = '<option value="">-- कालावधी निवडा --</option>';
    
    if (adType) {
        // Add duration options based on ad type
        const durations = [10, 20, 30];
        
        durations.forEach(days => {
            const option = document.createElement('option');
            option.value = days;
            
            if (adType == 1) {
                option.text = days + ' दिवस (₹' + durationPrices[1][days] + ')';
            } else {
                option.text = days + ' दिवस (₹' + durationPrices[2][days] + ')';
            }
            
            // Preserve previously selected duration if any
            const previousDuration = "<?php echo isset($_POST['duration']) ? $_POST['duration'] : ''; ?>";
            if (previousDuration && days == previousDuration) {
                option.selected = true;
            }
            
            durationSelect.appendChild(option);
        });
    }
    
    // Clear price when ad type changes
    priceField.value = '';
    
    // Update price and end date if duration is selected
    if (durationSelect.value) {
        updatePriceAndEndDate();
    }
}

// Update price based on ad type and duration
function updatePriceAndEndDate() {
    const adType = document.getElementById('ad_type').value;
    const duration = document.getElementById('duration').value;
    const priceField = document.getElementById('price');
    
    if (adType && duration) {
        const price = durationPrices[adType][duration];
        priceField.value = price;
    } else {
        priceField.value = '';
    }
    
    // Update end date if start date is selected
    updateEndDate();
}

// Update end date based on start date and duration
function updateEndDate() {
    const startDate = document.getElementById('start_date').value;
    const duration = document.getElementById('duration').value;
    const endDateField = document.getElementById('end_date');
    
    if (startDate && duration) {
        const date = new Date(startDate);
        date.setDate(date.getDate() + parseInt(duration));
        
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        
        endDateField.value = `${year}-${month}-${day}`;
    } else if (startDate) {
        // Clear end date if no duration selected
        endDateField.value = '';
    }
}

// Toggle transaction ID field based on payment method
function toggleTransactionId() {
    const paymentMethod = document.getElementById('payment_method').value;
    const transactionContainer = document.getElementById('transaction_id_container');
    const transactionInput = document.querySelector('input[name="transaction_id"]');
    
    if (paymentMethod === 'Payment Gateway') {
        transactionContainer.style.display = 'none';
        transactionInput.removeAttribute('required');
    } else {
        transactionContainer.style.display = 'block';
        transactionInput.setAttribute('required', 'required');
    }
}

// Load Indian states on page load
document.addEventListener('DOMContentLoaded', function() {
    loadIndianStates();
    updateDurationOptions();
    updateEndDate();
    toggleTransactionId();
    
    // If state was previously selected (e.g., form validation failed), load its districts
    const selectedState = document.getElementById('state').value;
    if (selectedState && selectedState !== '') {
        // Small delay to ensure states are loaded
        setTimeout(function() {
            loadDistricts();
        }, 500);
    }
});

// Function to load Indian states
function loadIndianStates() {
    fetch("https://countriesnow.space/api/v0.1/countries/states", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ country: "India" })
    })
    .then(res => res.json())
    .then(data => {
        const stateSelect = document.getElementById("state");
        
        // Clear existing options except the first one
        while (stateSelect.options.length > 1) {
            stateSelect.remove(1);
        }
        
        // Get previously selected state from PHP
        const previousState = "<?php echo isset($_POST['state']) ? addslashes($_POST['state']) : ''; ?>";
        
        // Sort states alphabetically
        const states = data.data.states.sort((a, b) => a.name.localeCompare(b.name));
        
        states.forEach(state => {
            const option = document.createElement("option");
            option.value = state.name;
            option.text = state.name;
            
            // Preserve previously selected state after form submission
            if (previousState && state.name === previousState) {
                option.selected = true;
            }
            
            stateSelect.appendChild(option);
        });
    })
    .catch(error => {
        console.error('Error loading states:', error);
        // Fallback options in case API fails
        const stateSelect = document.getElementById("state");
        const fallbackStates = [
            "Maharashtra", "Delhi", "Karnataka", "Tamil Nadu", 
            "Gujarat", "Uttar Pradesh", "Rajasthan", "West Bengal",
            "Madhya Pradesh", "Bihar", "Andhra Pradesh", "Telangana",
            "Kerala", "Punjab", "Haryana", "Odisha"
        ];
        
        // Get previously selected state
        const previousState = "<?php echo isset($_POST['state']) ? addslashes($_POST['state']) : ''; ?>";
        
        fallbackStates.sort().forEach(state => {
            const option = document.createElement("option");
            option.value = state;
            option.text = state;
            
            if (previousState && state === previousState) {
                option.selected = true;
            }
            
            stateSelect.appendChild(option);
        });
    });
}

// Function to load districts based on selected state
function loadDistricts() {
    const stateName = document.getElementById("state").value;
    const districtSelect = document.getElementById("district");
    
    // Clear district dropdown
    districtSelect.innerHTML = "<option value=''>-- जिल्हा निवडा / Select District --</option>";
    
    if (!stateName) {
        return;
    }
    
    // Show loading state
    const loadingOption = document.createElement("option");
    loadingOption.value = "";
    loadingOption.text = "लोड करत आहे... / Loading...";
    loadingOption.disabled = true;
    districtSelect.appendChild(loadingOption);
    
    fetch("https://countriesnow.space/api/v0.1/countries/state/cities", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            country: "India",
            state: stateName
        })
    })
    .then(res => res.json())
    .then(data => {
        // Clear loading option
        districtSelect.innerHTML = "<option value=''>-- जिल्हा निवडा / Select District --</option>";
        
        // Get previously selected district
        const previousDistrict = "<?php echo isset($_POST['district']) ? addslashes($_POST['district']) : ''; ?>";
        
        // Sort districts alphabetically
        const districts = data.data.sort((a, b) => a.localeCompare(b));
        
        districts.forEach(city => {
            const option = document.createElement("option");
            option.value = city;
            option.text = city;
            
            // Preserve previously selected district
            if (previousDistrict && city === previousDistrict) {
                option.selected = true;
            }
            
            districtSelect.appendChild(option);
        });
    })
    .catch(error => {
        console.error('Error loading districts:', error);
        districtSelect.innerHTML = "<option value=''>-- जिल्हा निवडा / Select District --</option>";
        
        // Add a fallback message
        const errorOption = document.createElement("option");
        errorOption.value = "";
        errorOption.text = "जिल्हे लोड करण्यात त्रुटी / Error loading districts";
        errorOption.disabled = true;
        districtSelect.appendChild(errorOption);
    });
}
</script>

<?php
include 'components/footer.php';
if (isset($conn)) {
    $conn->close();
}
?>