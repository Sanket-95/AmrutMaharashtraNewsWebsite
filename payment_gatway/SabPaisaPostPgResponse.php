<?php
// Start session with proper configuration
ini_set('session.cookie_path', '/');
ini_set('session.cookie_domain', '');
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);
session_start();

// Enable error logging
error_log("SabPaisa Callback Started - Session ID: " . session_id());
error_log("Session Data: " . print_r($_SESSION, true));

include('Authentication.php');

// Get the encrypted response
$query = $_REQUEST['encResponse'] ?? '';

$authKey = 'VkylGulAs8ysjQcwDU7vHCbSDz+05lxxh43s13/+P1A=';
$authIV = '5rTyHyY/FDpKUCpiFe+d5K2XkDkXCb99v+5GDWwnoK2KFPIVq629dikwYbluXXze';

$decText = null;
$AES256HMACSHA384HEX = new AES256HMACSHA384HEX();
$decText = $AES256HMACSHA384HEX->decrypt($authKey, $authIV, $query);

// Parse the decrypted response
parse_str($decText, $responseData);

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

// Log the payment status
error_log("Payment Status: " . ($payment_success ? 'SUCCESS' : 'FAILED') . " - Transaction ID: " . $sabpaisaTxnId);

// Check if we have pending data in session
$pending_data_exists = isset($_SESSION['pending_ad_data']);
error_log("Pending data in session: " . ($pending_data_exists ? 'YES' : 'NO'));

if ($payment_success) {
    // Try to get data from POST first (if passed as parameters)
    $ad_data = $_SESSION['pending_ad_data'] ?? $_POST ?? [];
    $ad_image = $_SESSION['pending_ad_image'] ?? null;
    
    error_log("Ad Data: " . print_r($ad_data, true));
    
    if (!empty($ad_data)) {
        // Include database connection
        $db_config_path = dirname(__DIR__) . '/components/db_config.php';
        if (file_exists($db_config_path)) {
            include $db_config_path;
            error_log("Database connection included successfully");
        } else {
            error_log("Database config file not found at: " . $db_config_path);
        }
        
        // Process the ad data
        $client_name = $conn->real_escape_string($ad_data['client_name'] ?? '');
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
        $start_date = $ad_data['start_date'] ?? date('Y-m-d');
        $created_by = $conn->real_escape_string($_SESSION['name'] ?? 'Admin');
        
        // Calculate price based on ad type and duration
        if ($ad_type == 1) {
            $price = ($duration == 10) ? 1500 : (($duration == 20) ? 2500 : 3000);
        } else {
            $price = ($duration == 10) ? 1000 : (($duration == 20) ? 1500 : 2000);
        }
        
        // Calculate end date based on duration
        $end_date = date('Y-m-d', strtotime($start_date . ' + ' . $duration . ' days'));
        
        // Handle image upload if it exists in session
        $image_name = '';
        if ($ad_image && isset($ad_image['tmp_name']) && $ad_image['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $ad_image['tmp_name'];
            $file_ext = strtolower(pathinfo($ad_image['name'], PATHINFO_EXTENSION));
            $image_name = time() . '_' . uniqid() . '.' . $file_ext;
            $upload_dir = ($ad_type == 1) ? dirname(__DIR__) . '/components/primary_advertised/' : dirname(__DIR__) . '/components/secondary_advertised/';
            $upload_path = $upload_dir . $image_name;
            
            // Ensure directory exists
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                error_log("Image uploaded successfully: " . $image_name);
            } else {
                error_log("Failed to upload image");
            }
        }
        
        // Insert into database
        $sql = "INSERT INTO ads_management 
                (client_name, client_email, mobile_number, business_type, full_address, state, district, 
                 ad_title, image_name, ad_link, ad_type, duration, payment_method, transaction_id, 
                 price, start_date, end_date, created_by, payment_status, is_active) 
                VALUES (
                    '$client_name', '$client_email', '$mobile_number', '$business_type', 
                    '$full_address', '$state', '$district', '$ad_title', '$image_name', 
                    '$ad_link', $ad_type, $duration, 'Payment Gateway', '$sabpaisaTxnId', 
                    $price, '$start_date', '$end_date', '$created_by', 1, 1
                )";
        
        error_log("SQL Query: " . $sql);
        
        if ($conn->query($sql)) {
            error_log("SUCCESS: Ad inserted successfully for transaction: " . $sabpaisaTxnId);
            
            // Clear session data
            unset($_SESSION['pending_ad_data']);
            unset($_SESSION['pending_ad_image']);
            
            // Also try to save clientTxnId for reference
            $_SESSION['last_successful_txn'] = $sabpaisaTxnId;
            
        } else {
            error_log("ERROR: Failed to insert ad - " . $conn->error);
        }
        
        $conn->close();
    } else {
        error_log("WARNING: No pending ad data found in session or POST");
        
        // Try to get data from the decrypted response
        error_log("Checking if data is in the response...");
        
        // You might have passed additional data in udf fields
        // If you're using udf1, udf2, etc., extract them here
    }
}

// Create HTML response page with JavaScript to redirect and show status
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment <?php echo $payment_success ? 'Success' : 'Failed'; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
            text-align: center;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success { color: #28a745; }
        .failed { color: #dc3545; }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }
        .btn-primary {
            background: #FF6600;
            color: white;
        }
        .btn-primary:hover {
            background: #e65c00;
        }
        .details {
            text-align: left;
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .details p {
            margin: 5px 0;
        }
        .debug-info {
            text-align: left;
            margin-top: 20px;
            padding: 10px;
            background: #e9ecef;
            border-radius: 5px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($payment_success): ?>
            <h1 class="success">✓ Payment Successful!</h1>
            <p>Thank you for your payment. Your advertisement has been submitted successfully.</p>
            
            <div class="details">
                <h3>Transaction Details:</h3>
                <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($sabpaisaTxnId); ?></p>
                <p><strong>Amount:</strong> ₹<?php echo htmlspecialchars($amount); ?></p>
                <p><strong>Payment Mode:</strong> <?php echo htmlspecialchars($paymentMode); ?></p>
                <p><strong>Date:</strong> <?php echo htmlspecialchars($transDate); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($status); ?></p>
            </div>
            
            <form action="../advertisement_post.php" method="get" id="redirectForm">
                <input type="hidden" name="payment_status" value="success">
                <input type="hidden" name="txn_id" value="<?php echo htmlspecialchars($sabpaisaTxnId); ?>">
                <button type="submit" class="btn btn-primary">Return to Advertisement Page</button>
            </form>
            
        <?php else: ?>
            <h1 class="failed">✗ Payment Failed</h1>
            <p>We couldn't process your payment. Please try again.</p>
            
            <div class="details">
                <h3>Error Details:</h3>
                <p><strong>Message:</strong> <?php echo htmlspecialchars($sabpaisaMessage ?: $bankMessage ?: 'Payment failed'); ?></p>
                <?php if ($sabpaisaErrorCode): ?>
                    <p><strong>Error Code:</strong> <?php echo htmlspecialchars($sabpaisaErrorCode); ?></p>
                <?php endif; ?>
            </div>
            
            <form action="../advertisement_post.php" method="get">
                <input type="hidden" name="payment_status" value="failed">
                <input type="hidden" name="message" value="<?php echo htmlspecialchars($sabpaisaMessage ?: 'Payment failed'); ?>">
                <button type="submit" class="btn btn-primary">Try Again</button>
            </form>
        <?php endif; ?>
        
        <p><small>Click the button above to return, or you will be automatically redirected in <span id="countdown">10</span> seconds...</small></p>
        
        <!-- Debug info (remove in production) -->
        <?php if (isset($_GET['debug']) || true): ?>
        <div class="debug-info">
            <h4>Debug Information:</h4>
            <p><strong>Session ID:</strong> <?php echo session_id(); ?></p>
            <p><strong>Payment Success:</strong> <?php echo $payment_success ? 'Yes' : 'No'; ?></p>
            <p><strong>Transaction ID:</strong> <?php echo $sabpaisaTxnId; ?></p>
            <p><strong>Pending Data in Session:</strong> <?php echo isset($_SESSION['pending_ad_data']) ? 'Yes' : 'No'; ?></p>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Auto redirect after 10 seconds
        let seconds = 10;
        const countdownEl = document.getElementById('countdown');
        
        const interval = setInterval(function() {
            seconds--;
            if (countdownEl) countdownEl.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(interval);
                document.getElementById('redirectForm').submit();
            }
        }, 1000);
    </script>
</body>
</html>
<?php
exit();
?>