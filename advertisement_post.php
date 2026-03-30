<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', 'payment_errors.log');

session_start();

// Login check
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Include database connection and header
include 'components/db_config.php';
include 'components/header.php';
include 'components/navbar.php';
include 'components/login_navbar.php';

// Configuration
define('PRIMARY_ADS_PATH', 'components/primary_advertised/');
define('SECONDARY_ADS_PATH', 'components/secondary_advertised/');
define('SOCIAL_MEDIA_ADS_PATH', 'components/primary_advertised_social_media/');
define('FOOTER_ADS_PATH', 'components/secondary_advertised_footer/');
define('TEMP_UPLOAD_PATH', 'components/temp_uploads/');
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);

// Price configuration (GST included)
define('BIG_AD_PRICE_10', 1770);
// define('BIG_AD_PRICE_10', 1);
define('BIG_AD_PRICE_20', 2950);
define('BIG_AD_PRICE_30', 3540);

define('SMALL_AD_PRICE_10', 1180);
define('SMALL_AD_PRICE_20', 1770);
define('SMALL_AD_PRICE_30', 2360);

// Duration in days
define('DURATION_10', 10);
define('DURATION_20', 20);
define('DURATION_30', 30);

// Ensure upload directories exist
$directories = [
    PRIMARY_ADS_PATH, 
    SECONDARY_ADS_PATH, 
    SOCIAL_MEDIA_ADS_PATH, 
    FOOTER_ADS_PATH,
    TEMP_UPLOAD_PATH
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        error_log("Created directory: " . $dir);
    }
}

$errors = [];
$success = false;
$payment_redirect = false;
$encrypted_data = '';

// SabPaisa Credentials
$clientCode = 'ACAD914';
$username = 'amrut.gom-4@gmail.com';
$password = 'ACAD914_SP25756';
$authKey = 'VkylGulAs8ysjQcwDU7vHCbSDz+05lxxh43s13/+P1A=';
$authIV = '5rTyHyY/FDpKUCpiFe+d5K2XkDkXCb99v+5GDWwnoK2KFPIVq629dikwYbluXXze';

// Handle payment gateway return (from callback)
if (isset($_GET['payment_status'])) {
    error_log("=== Payment Return Debug ===");
    error_log("Session ID: " . session_id());
    error_log("GET Data: " . print_r($_GET, true));
    error_log("Pending ad data: " . print_r($_SESSION['pending_ad_data'] ?? [], true));
    error_log("Pending main image: " . print_r($_SESSION['pending_ad_image'] ?? [], true));
    error_log("Pending social image: " . print_r($_SESSION['pending_social_image'] ?? [], true));
    error_log("Pending footer image: " . print_r($_SESSION['pending_footer_image'] ?? [], true));
    
    if ($_GET['payment_status'] == 'success') {
        $txn_id = isset($_GET['txn_id']) ? $_GET['txn_id'] : '';
        $amount = isset($_GET['amount']) ? $_GET['amount'] : '';
        $payment_mode = isset($_GET['payment_mode']) ? $_GET['payment_mode'] : 'Payment Gateway';
        
        // Check if this transaction was already processed
        $check_sql = "SELECT id FROM ads_management WHERE transaction_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param('s', $txn_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows == 0) {
            // Transaction not found in database, we need to insert it
            // Get the pending data from session
            $ad_data = $_SESSION['pending_ad_data'] ?? null;
            
            if ($ad_data) {
                // Extract data from session
                $client_name = $conn->real_escape_string($ad_data['client_name'] ?? '');
                $gst_number = $conn->real_escape_string($ad_data['gst_number'] ?? '');
                $client_email = $conn->real_escape_string($ad_data['client_email'] ?? '');
                $mobile_number = $conn->real_escape_string($ad_data['mobile_number'] ?? '');
                $business_type = $conn->real_escape_string($ad_data['business_type'] ?? '');
                $full_address = $conn->real_escape_string($ad_data['full_address'] ?? '');
                $state = $conn->real_escape_string($ad_data['state'] ?? '');
                $district = $conn->real_escape_string($ad_data['district'] ?? '');
                $ad_title = $conn->real_escape_string($ad_data['ad_title'] ?? '');
                $ad_link = $conn->real_escape_string($ad_data['ad_link'] ?? '');
                $ad_type = (int)($ad_data['ad_type'] ?? 0);
                $duration = (int)($ad_data['duration'] ?? 0);
                $price = (float)($ad_data['amount'] ?? 0);
                $start_date = $ad_data['start_date'] ?? date('Y-m-d');
                $created_by = $conn->real_escape_string($ad_data['created_by'] ?? 'Admin');
                
                // Calculate end date
                $end_date = date('Y-m-d', strtotime($start_date . ' + ' . $duration . ' days'));
                
                // Handle image uploads from session
                $image_name = '';
                $social_media_image = '';
                $footer_image_name = '';
                
                // Get image data from session
                $ad_image = $_SESSION['pending_ad_image'] ?? null;
                $social_image = $_SESSION['pending_social_image'] ?? null;
                $footer_image = $_SESSION['pending_footer_image'] ?? null;
                
                // Define upload directories
                $primary_upload_dir = 'components/primary_advertised/';
                $secondary_upload_dir = 'components/secondary_advertised/';
                $social_upload_dir = 'components/primary_advertised_social_media/';
                $footer_upload_dir = 'components/secondary_advertised_footer/';
                
                // Ensure directories exist
                foreach ([$primary_upload_dir, $secondary_upload_dir, $social_upload_dir, $footer_upload_dir] as $dir) {
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                        error_log("Created directory: " . $dir);
                    }
                }
                
                // Process main image - using 'path' instead of 'tmp_name'
                if ($ad_image && isset($ad_image['path']) && file_exists($ad_image['path'])) {
                    $file_ext = $ad_image['ext'] ?? 'jpg';
                    $image_name = time() . '_' . uniqid() . '.' . $file_ext;
                    $upload_dir = ($ad_type == 1) ? $primary_upload_dir : $secondary_upload_dir;
                    $upload_path = $upload_dir . $image_name;
                    
                    error_log("Moving main image from: " . $ad_image['path'] . " to: " . $upload_path);
                    
                    // Use rename to move the file
                    if (rename($ad_image['path'], $upload_path)) {
                        error_log("Main image moved successfully: " . $image_name);
                        chmod($upload_path, 0644);
                    } else {
                        error_log("Failed to move main image. Error: " . print_r(error_get_last(), true));
                        $image_name = '';
                    }
                } else {
                    error_log("Main image not found in temp location");
                    if ($ad_image) {
                        error_log("Ad image data: " . print_r($ad_image, true));
                    }
                }
                
                // Process social media image for big ads
                if ($ad_type == 1) {
                    if ($social_image && isset($social_image['path']) && file_exists($social_image['path'])) {
                        $file_ext = $social_image['ext'] ?? 'jpg';
                        $social_media_image = time() . '_social_' . uniqid() . '.' . $file_ext;
                        $upload_path = $social_upload_dir . $social_media_image;
                        
                        error_log("Moving social image from: " . $social_image['path'] . " to: " . $upload_path);
                        
                        if (rename($social_image['path'], $upload_path)) {
                            error_log("Social image moved successfully: " . $social_media_image);
                            chmod($upload_path, 0644);
                        } else {
                            error_log("Failed to move social image. Error: " . print_r(error_get_last(), true));
                            $social_media_image = '';
                        }
                    } else {
                        error_log("Social image not found in temp location for big ad");
                    }
                }
                
                // Process footer image for small ads
                if ($ad_type == 2) {
                    if ($footer_image && isset($footer_image['path']) && file_exists($footer_image['path'])) {
                        $file_ext = $footer_image['ext'] ?? 'jpg';
                        $footer_image_name = time() . '_footer_' . uniqid() . '.' . $file_ext;
                        $upload_path = $footer_upload_dir . $footer_image_name;
                        
                        error_log("Moving footer image from: " . $footer_image['path'] . " to: " . $upload_path);
                        
                        if (rename($footer_image['path'], $upload_path)) {
                            error_log("Footer image moved successfully: " . $footer_image_name);
                            chmod($upload_path, 0644);
                        } else {
                            error_log("Failed to move footer image. Error: " . print_r(error_get_last(), true));
                            $footer_image_name = '';
                        }
                    } else {
                        error_log("Footer image not found in temp location for small ad");
                    }
                }
                
                error_log("Final image names - Main: " . $image_name . ", Social: " . $social_media_image . ", Footer: " . $footer_image_name);
                
                // Insert into database
                $sql = "INSERT INTO ads_management 
                        (client_name, gst_number, client_email, mobile_number, business_type, 
                         full_address, state, district, ad_title, image_name, social_media_image, 
                         footer_image, ad_link, ad_type, duration, payment_method, transaction_id, 
                         price, start_date, end_date, created_by, payment_status, is_active) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Payment Gateway', 
                                ?, ?, ?, ?, ?, 1, 1)";
                
                $stmt = $conn->prepare($sql);
                
                if (!$stmt) {
                    error_log("Prepare failed: " . $conn->error);
                    echo '<div class="alert alert-danger">Database prepare error: ' . $conn->error . '</div>';
                } else {
                    $stmt->bind_param(
                        'sssssssssssssiisssss',
                        $client_name,
                        $gst_number,
                        $client_email,
                        $mobile_number,
                        $business_type,
                        $full_address,
                        $state,
                        $district,
                        $ad_title,
                        $image_name,
                        $social_media_image,
                        $footer_image_name,
                        $ad_link,
                        $ad_type,
                        $duration,
                        $txn_id,
                        $price,
                        $start_date,
                        $end_date,
                        $created_by
                    );
                    
                    if ($stmt->execute()) {
                        $insert_id = $conn->insert_id;
                        error_log("SUCCESS: Record inserted with ID: $insert_id for transaction: $txn_id");
                        
                        // Clean up any remaining temp files
                        if ($ad_image && isset($ad_image['path']) && file_exists($ad_image['path'])) {
                            unlink($ad_image['path']);
                        }
                        if ($social_image && isset($social_image['path']) && file_exists($social_image['path'])) {
                            unlink($social_image['path']);
                        }
                        if ($footer_image && isset($footer_image['path']) && file_exists($footer_image['path'])) {
                            unlink($footer_image['path']);
                        }
                        
                        // Clear session data after successful insert
                        unset($_SESSION['pending_ad_data']);
                        unset($_SESSION['pending_ad_image']);
                        unset($_SESSION['pending_social_image']);
                        unset($_SESSION['pending_footer_image']);
                        
                        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <strong>पेमेंट यशस्वी!</strong> तुमची जाहिरात यशस्वीरित्या जोडली गेली आहे. 
                                Transaction ID: ' . htmlspecialchars($txn_id) . '
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                              </div>';
                        $success = true;
                    } else {
                        error_log("ERROR: Execute failed - " . $stmt->error);
                        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong>त्रुटी!</strong> डेटाबेसमध्ये माहिती जोडताना त्रुटी: ' . $stmt->error . '
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                              </div>';
                    }
                    $stmt->close();
                }
            } else {
                error_log("ERROR: No pending ad data in session");
                echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>सूचना!</strong> पेमेंट यशस्वी झाले परंतु जाहिरात माहिती सापडली नाही. कृपया प्रशासकाशी संपर्क साधा.
                        Transaction ID: ' . htmlspecialchars($txn_id) . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                      </div>';
            }
        } else {
            // Transaction already exists
            $row = $check_result->fetch_assoc();
            error_log("Transaction already exists in database with ID: " . $row['id']);
            echo '<div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>माहिती!</strong> ही जाहिरात आधीच जोडली गेली आहे.
                    Transaction ID: ' . htmlspecialchars($txn_id) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>';
            $success = true;
        }
        $check_stmt->close();
        
    } else if ($_GET['payment_status'] == 'failed') {
        $message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'पेमेंट अयशस्वी';
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>पेमेंट अयशस्वी!</strong> ' . $message . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if payment gateway is selected
    $payment_method = trim($_POST['payment_method'] ?? '');
    
    if ($payment_method === 'Payment Gateway') {
        // For payment gateway, validate and prepare for redirect
        $client_name = trim($_POST['client_name'] ?? '');
        $gst_number = trim($_POST['gst_number'] ?? '');
        $mobile_number = trim($_POST['mobile_number'] ?? '');
        $client_email = trim($_POST['client_email'] ?? '');
        $full_address = trim($_POST['full_address'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $district = trim($_POST['district'] ?? '');
        $business_type = trim($_POST['business_type'] ?? '');
        $ad_title = trim($_POST['ad_title'] ?? '');
        $ad_link = trim($_POST['ad_link'] ?? '');
        $ad_type = isset($_POST['ad_type']) ? (int)$_POST['ad_type'] : 0;
        $duration = isset($_POST['duration']) ? (int)$_POST['duration'] : 0;
        $start_date = $_POST['start_date'] ?? date('Y-m-d');
        
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
        if (empty($start_date)) $errors[] = 'सुरु तारीख आवश्यक आहे.';
        
        // Optional GST validation if provided
        if (!empty($gst_number)) {
            if (!preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}[Z]{1}[0-9A-Z]{1}$/', $gst_number)) {
                $errors[] = 'वैध GST क्रमांक प्रविष्ट करा (उदा. 27AAPFU0939F1Z5)';
            }
        }
        
        // Validate main image upload for payment gateway
        $main_image_tmp = null;
        $main_image_name = null;
        $main_image_ext = null;
        
        if (isset($_FILES['ad_image']) && $_FILES['ad_image']['error'] === UPLOAD_ERR_OK) {
            $file_ext = strtolower(pathinfo($_FILES['ad_image']['name'], PATHINFO_EXTENSION));
            $file_size = $_FILES['ad_image']['size'];
            
            if (!in_array($file_ext, ALLOWED_EXTENSIONS)) {
                $errors[] = 'फक्त JPG, JPEG, PNG, WEBP फाइल्स स्वीकारल्या जातात.';
            } elseif ($file_size > MAX_FILE_SIZE) {
                $errors[] = 'फाइल साइज 2MB पेक्षा कमी असावी.';
            } else {
                $main_image_tmp = $_FILES['ad_image']['tmp_name'];
                $main_image_name = $_FILES['ad_image']['name'];
                $main_image_ext = $file_ext;
            }
        } else {
            $errors[] = 'मुख्य वेबसाईटवर प्रमुख बॅनर प्रतिमा आवश्यक आहे.';
        }
        
        // Validate social media image for big ads
        $social_image_tmp = null;
        $social_image_name = null;
        $social_image_ext = null;
        
        if ($ad_type == 1) {
            if (isset($_FILES['social_media_image']) && $_FILES['social_media_image']['error'] === UPLOAD_ERR_OK) {
                $file_ext = strtolower(pathinfo($_FILES['social_media_image']['name'], PATHINFO_EXTENSION));
                $file_size = $_FILES['social_media_image']['size'];
                
                if (!in_array($file_ext, ALLOWED_EXTENSIONS)) {
                    $errors[] = 'सोशल मीडिया प्रतिमेसाठी फक्त JPG, JPEG, PNG, WEBP फाइल्स स्वीकारल्या जातात.';
                } elseif ($file_size > MAX_FILE_SIZE) {
                    $errors[] = 'सोशल मीडिया प्रतिमेची साइज 2MB पेक्षा कमी असावी.';
                } else {
                    $social_image_tmp = $_FILES['social_media_image']['tmp_name'];
                    $social_image_name = $_FILES['social_media_image']['name'];
                    $social_image_ext = $file_ext;
                }
            } else {
                $errors[] = 'सोशल मीडियावर प्रसारित होणारी बातमी प्रतिमा आवश्यक आहे.';
            }
        }
        
        // Validate footer image for small ads
        $footer_image_tmp = null;
        $footer_image_name = null;
        $footer_image_ext = null;
        
        if ($ad_type == 2) {
            if (isset($_FILES['footer_image']) && $_FILES['footer_image']['error'] === UPLOAD_ERR_OK) {
                $file_ext = strtolower(pathinfo($_FILES['footer_image']['name'], PATHINFO_EXTENSION));
                $file_size = $_FILES['footer_image']['size'];
                
                if (!in_array($file_ext, ALLOWED_EXTENSIONS)) {
                    $errors[] = 'फूटर प्रतिमेसाठी फक्त JPG, JPEG, PNG, WEBP फाइल्स स्वीकारल्या जातात.';
                } elseif ($file_size > MAX_FILE_SIZE) {
                    $errors[] = 'फूटर प्रतिमेची साइज 2MB पेक्षा कमी असावी.';
                } else {
                    $footer_image_tmp = $_FILES['footer_image']['tmp_name'];
                    $footer_image_name = $_FILES['footer_image']['name'];
                    $footer_image_ext = $file_ext;
                }
            } else {
                $errors[] = 'फूटर जाहिरात प्रतिमा आवश्यक आहे.';
            }
        }
        
        if (empty($errors)) {
            // Store form data in session for after payment
            $_SESSION['pending_ad_data'] = [
                'client_name' => $client_name,
                'gst_number' => $gst_number,
                'client_email' => $client_email,
                'mobile_number' => $mobile_number,
                'business_type' => $business_type,
                'full_address' => $full_address,
                'state' => $state,
                'district' => $district,
                'ad_title' => $ad_title,
                'ad_link' => $ad_link,
                'ad_type' => $ad_type,
                'duration' => $duration,
                'amount' => $amount,
                'start_date' => $start_date,
                'created_by' => $_SESSION['name'] ?? 'Admin'
            ];
            
            // IMPORTANT: Move uploaded files to permanent temp location
            if ($main_image_tmp && file_exists($main_image_tmp)) {
                $temp_image_name = 'main_' . time() . '_' . uniqid() . '.' . $main_image_ext;
                $temp_image_path = TEMP_UPLOAD_PATH . $temp_image_name;
                
                if (move_uploaded_file($main_image_tmp, $temp_image_path)) {
                    $_SESSION['pending_ad_image'] = [
                        'path' => $temp_image_path,
                        'name' => $main_image_name,
                        'ext' => $main_image_ext,
                        'temp_name' => $temp_image_name
                    ];
                    error_log("Moved main image to temp: " . $temp_image_path);
                } else {
                    error_log("Failed to move main image to temp");
                }
            }
            
            if ($social_image_tmp && file_exists($social_image_tmp)) {
                $temp_social_name = 'social_' . time() . '_' . uniqid() . '.' . $social_image_ext;
                $temp_social_path = TEMP_UPLOAD_PATH . $temp_social_name;
                
                if (move_uploaded_file($social_image_tmp, $temp_social_path)) {
                    $_SESSION['pending_social_image'] = [
                        'path' => $temp_social_path,
                        'name' => $social_image_name,
                        'ext' => $social_image_ext,
                        'temp_name' => $temp_social_name
                    ];
                    error_log("Moved social image to temp: " . $temp_social_path);
                }
            }
            
            if ($footer_image_tmp && file_exists($footer_image_tmp)) {
                $temp_footer_name = 'footer_' . time() . '_' . uniqid() . '.' . $footer_image_ext;
                $temp_footer_path = TEMP_UPLOAD_PATH . $temp_footer_name;
                
                if (move_uploaded_file($footer_image_tmp, $temp_footer_path)) {
                    $_SESSION['pending_footer_image'] = [
                        'path' => $temp_footer_path,
                        'name' => $footer_image_name,
                        'ext' => $footer_image_ext,
                        'temp_name' => $temp_footer_name
                    ];
                    error_log("Moved footer image to temp: " . $temp_footer_path);
                }
            }
            
            // Prepare payment gateway data
            $payerName = $client_name;
            $payerEmail = !empty($client_email) ? $client_email : 'customer@email.com';
            $payerMobile = $mobile_number;
            $payerAddress = $full_address . ', ' . $district . ', ' . $state;
            
            $clientTxnId = time() . rand(1000, 9999);
            $amountType = 'INR';
            $mcc = 5137;
            $channelId = 'W';

            // FULL absolute URL for callback
            $callbackUrl = 'https://amrutmaharashtra.org/payment_gatway/SabPaisaPostPgResponse.php';
            
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
        $gst_number     = trim($_POST['gst_number'] ?? '');
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
        
        // Optional GST validation if provided
        if (!empty($gst_number)) {
            if (!preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}[Z]{1}[0-9A-Z]{1}$/', $gst_number)) {
                $errors[] = 'वैध GST क्रमांक प्रविष्ट करा (उदा. 27AAPFU0939F1Z5)';
            }
        }
        
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
        if (!in_array($payment_method, ['Payment Gateway', 'Amrut Scheme'])) {
            if (empty($transaction_id)) $errors[] = 'ट्रांझॅक्शन ID आवश्यक आहे.';
        }
        if (empty($start_date)) $errors[] = 'सुरु तारीख आवश्यक आहे.';
        
        // Auto-calculate end date based on duration
        if (!empty($start_date) && $duration > 0) {
            $date = new DateTime($start_date);
            $date->modify('+' . $duration . ' days');
            $end_date = $date->format('Y-m-d');
        }

        // File upload handling for main image
        $image_name = '';
        $social_media_image = '';
        $footer_image = '';
        
        // Upload main image
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
                    $errors[] = 'मुख्य प्रतिमा अपलोड करताना त्रुटी.';
                    $image_name = '';
                } else {
                    chmod($upload_path, 0644);
                }
            }
        } else {
            $errors[] = 'मुख्य वेबसाईटवर प्रमुख बॅनर प्रतिमा आवश्यक आहे.';
        }
        
        // Upload social media image for big ads
        if ($ad_type == 1) {
            if (isset($_FILES['social_media_image']) && $_FILES['social_media_image']['error'] === UPLOAD_ERR_OK) {
                $file_tmp  = $_FILES['social_media_image']['tmp_name'];
                $file_size = $_FILES['social_media_image']['size'];
                $file_ext  = strtolower(pathinfo($_FILES['social_media_image']['name'], PATHINFO_EXTENSION));

                if (!in_array($file_ext, ALLOWED_EXTENSIONS)) {
                    $errors[] = 'सोशल मीडिया प्रतिमेसाठी फक्त JPG, JPEG, PNG, WEBP फाइल्स स्वीकारल्या जातात.';
                } elseif ($file_size > MAX_FILE_SIZE) {
                    $errors[] = 'सोशल मीडिया प्रतिमेची साइज 2MB पेक्षा कमी असावी.';
                } else {
                    $social_media_image = time() . '_social_' . uniqid() . '.' . $file_ext;
                    $upload_path = SOCIAL_MEDIA_ADS_PATH . $social_media_image;

                    if (!move_uploaded_file($file_tmp, $upload_path)) {
                        $errors[] = 'सोशल मीडिया प्रतिमा अपलोड करताना त्रुटी.';
                        $social_media_image = '';
                    } else {
                        chmod($upload_path, 0644);
                    }
                }
            } else {
                $errors[] = 'सोशल मीडियावर प्रसारित होणारी बातमी प्रतिमा आवश्यक आहे.';
            }
        }
        
        // Upload footer image for small ads
        if ($ad_type == 2) {
            if (isset($_FILES['footer_image']) && $_FILES['footer_image']['error'] === UPLOAD_ERR_OK) {
                $file_tmp  = $_FILES['footer_image']['tmp_name'];
                $file_size = $_FILES['footer_image']['size'];
                $file_ext  = strtolower(pathinfo($_FILES['footer_image']['name'], PATHINFO_EXTENSION));

                if (!in_array($file_ext, ALLOWED_EXTENSIONS)) {
                    $errors[] = 'फूटर प्रतिमेसाठी फक्त JPG, JPEG, PNG, WEBP फाइल्स स्वीकारल्या जातात.';
                } elseif ($file_size > MAX_FILE_SIZE) {
                    $errors[] = 'फूटर प्रतिमेची साइज 2MB पेक्षा कमी असावी.';
                } else {
                    $footer_image = time() . '_footer_' . uniqid() . '.' . $file_ext;
                    $upload_path = FOOTER_ADS_PATH . $footer_image;

                    if (!move_uploaded_file($file_tmp, $upload_path)) {
                        $errors[] = 'फूटर प्रतिमा अपलोड करताना त्रुटी.';
                        $footer_image = '';
                    } else {
                        chmod($upload_path, 0644);
                    }
                }
            } else {
                $errors[] = 'फूटर जाहिरात प्रतिमा आवश्यक आहे.';
            }
        }

        // If no errors, insert into database
        if (empty($errors)) {
            $payment_status = 1; // 1 for paid
            
            $sql = "INSERT INTO ads_management 
                    (client_name, gst_number, client_email, mobile_number, business_type, full_address, state, district, ad_title, image_name, social_media_image, footer_image, ad_link, ad_type, duration, payment_method, transaction_id, price, start_date, end_date, created_by, payment_status, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
            
            $stmt = $conn->prepare($sql);
            
            $stmt->bind_param(
                'sssssssssssssiisssdssi',
                $client_name,
                $gst_number,
                $client_email,
                $mobile_number,
                $business_type,
                $full_address,
                $state,
                $district,
                $ad_title,
                $image_name,
                $social_media_image,
                $footer_image,
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
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong>यशस्वी!</strong> जाहिरात यशस्वीरित्या जोडली गेली.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                      </div>';
            } else {
                $errors[] = 'डेटाबेसमध्ये त्रुटी: ' . $conn->error;
                // Delete uploaded files if database insert fails
                if (!empty($image_name) && file_exists($upload_dir . $image_name)) {
                    unlink($upload_dir . $image_name);
                }
                if (!empty($social_media_image) && file_exists(SOCIAL_MEDIA_ADS_PATH . $social_media_image)) {
                    unlink(SOCIAL_MEDIA_ADS_PATH . $social_media_image);
                }
                if (!empty($footer_image) && file_exists(FOOTER_ADS_PATH . $footer_image)) {
                    unlink(FOOTER_ADS_PATH . $footer_image);
                }
            }
        }
    }
}
?>

<!-- Rest of your HTML and JavaScript remains exactly the same -->
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-orange text-white">
            <h5 class="mb-0"><i class="bi bi-plus-circle"></i> नवीन जाहिरात जोडा</h5>
        </div>
        <div class="card-body">
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

            <?php if ($payment_redirect): ?>
                <div class="text-center py-4">
                    <div class="spinner-border text-orange mb-3" style="color: #FF6600;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5>पेमेंट गेटवे वर पुनर्निर्देशित होत आहे...</h5>
                    <p>कृपया प्रतीक्षा करा</p>
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
                <form method="POST" enctype="multipart/form-data" id="adForm">
                    <!-- Your existing form HTML remains exactly the same -->
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
                            <label class="form-label">GST क्रमांक <small class="text-muted">(ऐच्छिक / Optional)</small></label>
                            <input type="text" name="gst_number" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['gst_number'] ?? ''); ?>" 
                                   placeholder="27AAPFU0939F1Z5" 
                                   pattern="^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}[Z]{1}[0-9A-Z]{1}$"
                                   title="कृपया वैध GST क्रमांक प्रविष्ट करा (उदा. 27AAPFU0939F1Z5)">
                            <small class="text-muted">15 अंकी GST क्रमांक (उदा. 27AAPFU0939F1Z5)</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">मोबाईल नंबर *</label>
                            <input type="tel" name="mobile_number" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['mobile_number'] ?? ''); ?>" 
                                   pattern="[0-9]{10}" maxlength="10" 
                                   placeholder="9876543210" required>
                            <small class="text-muted">10 अंकी मोबाईल नंबर टाका</small>
                        </div>
                    </div>
                    
                    <div class="row">
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
                            <select name="ad_type" id="ad_type" class="form-select" required onchange="updateDurationOptions(); toggleImageFields();">
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
                            <label class="form-label">किंमत (₹) * <small class="text-muted">(GST सह / GST included)</small></label>
                            <input type="number" name="price" id="price" class="form-control bg-light" 
                                   value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" 
                                   readonly placeholder="प्रकार व कालावधी निवडा" style="background-color:#f8f9fa; font-weight:600; color:#28a745;">
                            <small class="text-muted">प्रकार व कालावधीनुसार किंमत (करासह)</small>
                        </div>
                        
                        <div class="col-md-6 mb-3" id="main_image_container">
                            <label class="form-label">मुख्य वेबसाईटवर प्रमुख बॅनर * <small class="text-muted">(Size : 1500 × 600)</small></label>
                            <input type="file" name="ad_image" class="form-control" 
                                   accept=".jpg,.jpeg,.png,.webp" required>
                            <small class="text-muted">जास्तीत जास्त 2MB, फक्त jpg/png/webp</small>
                        </div>
                    </div>
                    
                    <!-- Dynamic Image Fields based on Ad Type -->
                    <div id="big_ad_fields" style="display: none;">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">सोशल मीडियावर प्रसारित होणारी बातमी * <small class="text-muted">(Size : 1080 × 1080)</small></label>
                                <input type="file" name="social_media_image" class="form-control" 
                                       accept=".jpg,.jpeg,.png,.webp">
                                <small class="text-muted">सोशल मीडिया पोस्टसाठी चौरस प्रतिमा, जास्तीत जास्त 2MB, फक्त jpg/png/webp</small>
                            </div>
                        </div>
                    </div>
                    
                    <div id="small_ad_fields" style="display: none;">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">फूटर जाहिरात * <small class="text-muted">(Size : 360 × 80)</small></label>
                                <input type="file" name="footer_image" class="form-control" 
                                       accept=".jpg,.jpeg,.png,.webp">
                                <small class="text-muted">फूटरमध्ये दाखवण्यासाठी आयताकृती बॅनर, जास्तीत जास्त 2MB, फक्त jpg/png/webp</small>
                            </div>
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
                                <option value="Amrut Scheme" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'Amrut Scheme') ? 'selected' : ''; ?>>अमृत योजना / Amrut Scheme's</option>
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
    .form-label small {
        font-weight: normal;
        color: #6c757d;
        font-size: 0.875rem;
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
                option.text = days + ' दिवस (₹' + durationPrices[1][days] + ' GST सह)';
            } else {
                option.text = days + ' दिवस (₹' + durationPrices[2][days] + ' GST सह)';
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
    
    if (paymentMethod === 'Payment Gateway' || paymentMethod === 'Amrut Scheme') {
        transactionContainer.style.display = 'none';
        transactionInput.removeAttribute('required');
    } else {
        transactionContainer.style.display = 'block';
        transactionInput.setAttribute('required', 'required');
    }
}

// Toggle image fields based on ad type
function toggleImageFields() {
    const adType = document.getElementById('ad_type').value;
    const bigAdFields = document.getElementById('big_ad_fields');
    const smallAdFields = document.getElementById('small_ad_fields');
    const socialInput = document.querySelector('input[name="social_media_image"]');
    const footerInput = document.querySelector('input[name="footer_image"]');
    
    // Hide both first
    bigAdFields.style.display = 'none';
    smallAdFields.style.display = 'none';
    
    // Remove required attributes
    if (socialInput) socialInput.removeAttribute('required');
    if (footerInput) footerInput.removeAttribute('required');
    
    if (adType == 1) {
        bigAdFields.style.display = 'block';
        if (socialInput) socialInput.setAttribute('required', 'required');
    } else if (adType == 2) {
        smallAdFields.style.display = 'block';
        if (footerInput) footerInput.setAttribute('required', 'required');
    }
}

// Load Indian states on page load
document.addEventListener('DOMContentLoaded', function() {
    loadIndianStates();
    updateDurationOptions();
    updateEndDate();
    toggleTransactionId();
    toggleImageFields();
    
    // If state was previously selected, load its districts
    const selectedState = document.getElementById('state').value;
    if (selectedState && selectedState !== '') {
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
        
        // Add a fallback message.....
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