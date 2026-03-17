<?php
// Start session with proper configuration
ini_set('session.cookie_path', '/');
ini_set('session.cookie_domain', 'amrutmaharashtra.org');
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);
session_start();

// Enable error logging
error_log("=== SabPaisa Callback Started ===");
error_log("Session ID: " . session_id());
error_log("Session Data: " . print_r($_SESSION, true));
error_log("POST Data: " . print_r($_POST, true));
error_log("GET Data: " . print_r($_GET, true));
error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);

include('Authentication.php');

// Get the encrypted response - check both POST and GET
$query = '';
if (isset($_POST['encResponse'])) {
    $query = $_POST['encResponse'];
} elseif (isset($_GET['encResponse'])) {
    $query = $_GET['encResponse'];
}

error_log("Encrypted Response: " . $query);

$authKey = 'VkylGulAs8ysjQcwDU7vHCbSDz+05lxxh43s13/+P1A=';
$authIV = '5rTyHyY/FDpKUCpiFe+d5K2XkDkXCb99v+5GDWwnoK2KFPIVq629dikwYbluXXze';

$decText = null;
$AES256HMACSHA384HEX = new AES256HMACSHA384HEX();

try {
    $decText = $AES256HMACSHA384HEX->decrypt($authKey, $authIV, $query);
    error_log("Decrypted Text: " . $decText);
} catch (Exception $e) {
    error_log("Decryption Error: " . $e->getMessage());
}

// Parse the decrypted response
parse_str($decText, $responseData);
error_log("Parsed Response Data: " . print_r($responseData, true));

// Extract all parameters
$payerName = $responseData['payerName'] ?? '';
$payerEmail = $responseData['payerEmail'] ?? '';
$payerMobile = $responseData['payerMobile'] ?? '';
$clientTxnId = $responseData['clientTxnId'] ?? '';
$payerAddress = $responseData['payerAddress'] ?? '';
$amount = $responseData['amount'] ?? '';
$clientCode = $responseData['clientCode'] ?? '';
$paidAmount = $responseData['paidAmount'] ?? '';
$paymentMode = $responseData['paymentMode'] ?? '';
$bankName = $responseData['bankName'] ?? '';
$amountType = $responseData['amountType'] ?? '';
$status = $responseData['status'] ?? '';
$statusCode = $responseData['statusCode'] ?? '';
$challanNumber = $responseData['challanNumber'] ?? '';
$sabpaisaTxnId = $responseData['sabpaisaTxnId'] ?? '';
$sabpaisaMessage = $responseData['sabpaisaMessage'] ?? '';
$bankMessage = $responseData['bankMessage'] ?? '';
$bankErrorCode = $responseData['bankErrorCode'] ?? '';
$sabpaisaErrorCode = $responseData['sabpaisaErrorCode'] ?? '';
$bankTxnId = $responseData['bankTxnId'] ?? '';
$transDate = $responseData['transDate'] ?? '';

// Check if payment was successful
$payment_success = ($status == 'SUCCESS' || $statusCode == '0300');

error_log("Payment Status: " . ($payment_success ? 'SUCCESS' : 'FAILED'));
error_log("SabPaisa Transaction ID: " . $sabpaisaTxnId);

if ($payment_success) {
    // Try to get pending data from session
    $ad_data = $_SESSION['pending_ad_data'] ?? null;
    
    // If session data is lost, try to reconstruct from transaction ID
    if (empty($ad_data) && !empty($sabpaisaTxnId)) {
        error_log("Session data lost, trying to recover from database...");
        
        // Check if this transaction was already processed
        include dirname(__DIR__) . '/components/db_config.php';
        
        $check_sql = "SELECT id FROM ads_management WHERE transaction_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param('s', $sabpaisaTxnId);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            error_log("Transaction already processed: " . $sabpaisaTxnId);
            $already_processed = true;
        }
        $check_stmt->close();
    }
    
    if (!empty($ad_data) && !isset($already_processed)) {
        // Include database connection
        $db_config_path = dirname(__DIR__) . '/components/db_config.php';
        if (file_exists($db_config_path)) {
            include $db_config_path;
            error_log("Database connection included successfully");
        } else {
            error_log("ERROR: Database config file not found at: " . $db_config_path);
            die("Database configuration error");
        }
        
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
        $duration = (int)($ad_data['duration'] ?? 30);
        $amount_val = (float)($ad_data['amount'] ?? 0);
        $start_date = $ad_data['start_date'] ?? date('Y-m-d');
        $created_by = $conn->real_escape_string($ad_data['created_by'] ?? 'Admin');
        
        // Calculate end date
        $end_date = date('Y-m-d', strtotime($start_date . ' + ' . $duration . ' days'));
        
        // Handle image uploads (similar to your existing code)
        // [Keep your existing image upload code here]
        
        // Insert into database
        $sql = "INSERT INTO ads_management 
                (client_name, gst_number, client_email, mobile_number, business_type, full_address, 
                 state, district, ad_title, ad_link, ad_type, duration, payment_method, 
                 transaction_id, price, start_date, end_date, created_by, payment_status, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Payment Gateway', ?, ?, ?, ?, ?, 1, 1)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'ssssssssssiisssss',
            $client_name,
            $gst_number,
            $client_email,
            $mobile_number,
            $business_type,
            $full_address,
            $state,
            $district,
            $ad_title,
            $ad_link,
            $ad_type,
            $duration,
            $sabpaisaTxnId,
            $amount_val,
            $start_date,
            $end_date,
            $created_by
        );
        
        if ($stmt->execute()) {
            $insert_id = $conn->insert_id;
            error_log("SUCCESS: Ad inserted with ID: " . $insert_id);
            
            // Clear session data
            unset($_SESSION['pending_ad_data']);
            unset($_SESSION['pending_ad_image']);
            unset($_SESSION['pending_social_image']);
            unset($_SESSION['pending_footer_image']);
            
        } else {
            error_log("ERROR: Failed to insert - " . $stmt->error);
        }
        
        $stmt->close();
        $conn->close();
    } elseif (isset($already_processed)) {
        error_log("Transaction already processed, skipping insert");
    } else {
        error_log("ERROR: No pending ad data found");
    }
}

// Redirect back to the form with status
$redirect_url = 'https://amrutmaharashtra.org/advertisement_post.php';
$redirect_url .= '?payment_status=' . ($payment_success ? 'success' : 'failed');
$redirect_url .= '&txn_id=' . urlencode($sabpaisaTxnId);
$redirect_url .= '&amount=' . urlencode($amount);
$redirect_url .= '&payment_mode=' . urlencode($paymentMode);

header('Location: ' . $redirect_url);
exit();
?>