<?php
// Start session with proper configuration
ini_set('session.cookie_path', '/');
ini_set('session.cookie_domain', '');
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);
session_start();

// Enable error logging
error_log("=== SabPaisa Callback Started ===");
error_log("Session ID: " . session_id());
error_log("Session Data: " . print_r($_SESSION, true));
error_log("POST Data: " . print_r($_POST, true));
error_log("GET Data: " . print_r($_GET, true));

include('Authentication.php');

// Get the encrypted response
$query = $_REQUEST['encResponse'] ?? '';
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
    // Get pending data from session
    $ad_data = $_SESSION['pending_ad_data'] ?? null;
    $ad_image = $_SESSION['pending_ad_image'] ?? null;
    $social_image = $_SESSION['pending_social_image'] ?? null;
    $footer_image = $_SESSION['pending_footer_image'] ?? null;
    
    error_log("Pending Ad Data: " . print_r($ad_data, true));
    
    if (!empty($ad_data)) {
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
        $amount = (float)($ad_data['amount'] ?? 0);
        $start_date = $ad_data['start_date'] ?? date('Y-m-d');
        $created_by = $conn->real_escape_string($ad_data['created_by'] ?? 'Admin');
        $payment_method = 'Payment Gateway';
        $transaction_id = $conn->real_escape_string($sabpaisaTxnId);
        $price = $amount;
        
        // Calculate end date based on duration
        $end_date = date('Y-m-d', strtotime($start_date . ' + ' . $duration . ' days'));
        
        // Handle image uploads
        $image_name = '';
        $social_media_image = '';
        $footer_image_name = '';
        
        // Define upload directories
        $primary_upload_dir = dirname(__DIR__) . '/components/primary_advertised/';
        $secondary_upload_dir = dirname(__DIR__) . '/components/secondary_advertised/';
        $social_upload_dir = dirname(__DIR__) . '/components/primary_advertised_social_media/';
        $footer_upload_dir = dirname(__DIR__) . '/components/secondary_advertised_footer/';
        
        // Ensure directories exist
        foreach ([$primary_upload_dir, $secondary_upload_dir, $social_upload_dir, $footer_upload_dir] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
        
        // Upload main image
        if ($ad_image && isset($ad_image['tmp_name']) && file_exists($ad_image['tmp_name'])) {
            $file_ext = $ad_image['ext'] ?? 'jpg';
            $image_name = time() . '_' . uniqid() . '.' . $file_ext;
            $upload_dir = ($ad_type == 1) ? $primary_upload_dir : $secondary_upload_dir;
            $upload_path = $upload_dir . $image_name;
            
            if (copy($ad_image['tmp_name'], $upload_path)) {
                error_log("Main image uploaded successfully: " . $image_name);
            } else {
                error_log("Failed to upload main image");
                $image_name = '';
            }
        }
        
        // Upload social media image for big ads
        if ($ad_type == 1 && $social_image && isset($social_image['tmp_name']) && file_exists($social_image['tmp_name'])) {
            $file_ext = $social_image['ext'] ?? 'jpg';
            $social_media_image = time() . '_social_' . uniqid() . '.' . $file_ext;
            $upload_path = $social_upload_dir . $social_media_image;
            
            if (copy($social_image['tmp_name'], $upload_path)) {
                error_log("Social image uploaded successfully: " . $social_media_image);
            } else {
                error_log("Failed to upload social image");
                $social_media_image = '';
            }
        }
        
        // Upload footer image for small ads
        if ($ad_type == 2 && $footer_image && isset($footer_image['tmp_name']) && file_exists($footer_image['tmp_name'])) {
            $file_ext = $footer_image['ext'] ?? 'jpg';
            $footer_image_name = time() . '_footer_' . uniqid() . '.' . $file_ext;
            $upload_path = $footer_upload_dir . $footer_image_name;
            
            if (copy($footer_image['tmp_name'], $upload_path)) {
                error_log("Footer image uploaded successfully: " . $footer_image_name);
            } else {
                error_log("Failed to upload footer image");
                $footer_image_name = '';
            }
        }
        
        // Insert into database with correct values
        $sql = "INSERT INTO ads_management 
                (client_name, gst_number, client_email, mobile_number, business_type, full_address, 
                 state, district, ad_title, image_name, social_media_image, footer_image, ad_link, 
                 ad_type, duration, payment_method, transaction_id, price, start_date, end_date, 
                 created_by, payment_status, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1)";
        
        $stmt = $conn->prepare($sql);
        
        $stmt->bind_param(
            'sssssssssssssiisssdss',
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
            $payment_method,
            $transaction_id,
            $price,
            $start_date,
            $end_date,
            $created_by
        );
        
        error_log("Executing SQL with params: " . print_r([
            'client_name' => $client_name,
            'gst_number' => $gst_number,
            'client_email' => $client_email,
            'mobile_number' => $mobile_number,
            'business_type' => $business_type,
            'ad_title' => $ad_title,
            'ad_type' => $ad_type,
            'duration' => $duration,
            'payment_method' => $payment_method,
            'transaction_id' => $transaction_id,
            'price' => $price,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'created_by' => $created_by
        ], true));
        
        if ($stmt->execute()) {
            $insert_id = $conn->insert_id;
            error_log("SUCCESS: Ad inserted successfully with ID: " . $insert_id . " for transaction: " . $sabpaisaTxnId);
            
            // Clear session data
            unset($_SESSION['pending_ad_data']);
            unset($_SESSION['pending_ad_image']);
            unset($_SESSION['pending_social_image']);
            unset($_SESSION['pending_footer_image']);
            
            // Store transaction ID for reference
            $_SESSION['last_successful_txn'] = $sabpaisaTxnId;
            
        } else {
            error_log("ERROR: Failed to insert ad - " . $stmt->error);
        }
        
        $stmt->close();
        $conn->close();
    } else {
        error_log("ERROR: No pending ad data found in session");
    }
}

// Create HTML response page with redirect
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
    </style>
</head>
<body>
    <div class="container">
        <?php if ($payment_success): ?>
            <h1 class="success">✓ पेमेंट यशस्वी!</h1>
            <p>तुमचे पेमेंट यशस्वीरित्या पूर्ण झाले आहे. तुमची जाहिरात लवकरच प्रसिदित केली जाईल.</p>
            
            <div class="details">
                <h3>व्यवहार तपशील:</h3>
                <p><strong>व्यवहार ID:</strong> <?php echo htmlspecialchars($sabpaisaTxnId); ?></p>
                <p><strong>रक्कम:</strong> ₹<?php echo htmlspecialchars($amount); ?></p>
                <p><strong>पेमेंट पद्धत:</strong> <?php echo htmlspecialchars($paymentMode ?: 'Payment Gateway'); ?></p>
                <p><strong>दिनांक:</strong> <?php echo htmlspecialchars($transDate); ?></p>
                <p><strong>स्थिती:</strong> यशस्वी</p>
            </div>
            
            <form action="../advertisement_post.php" method="get" id="redirectForm">
                <input type="hidden" name="payment_status" value="success">
                <input type="hidden" name="txn_id" value="<?php echo htmlspecialchars($sabpaisaTxnId); ?>">
                <input type="hidden" name="amount" value="<?php echo htmlspecialchars($amount); ?>">
                <input type="hidden" name="payment_mode" value="<?php echo htmlspecialchars($paymentMode); ?>">
                <button type="submit" class="btn btn-primary">जाहिरात पेजवर परत जा</button>
            </form>
            
        <?php else: ?>
            <h1 class="failed">✗ पेमेंट अयशस्वी</h1>
            <p>तुमचे पेमेंट प्रक्रिया करताना त्रुटी आली. कृपया पुन्हा प्रयत्न करा.</p>
            
            <div class="details">
                <h3>त्रुटी तपशील:</h3>
                <p><strong>संदेश:</strong> <?php echo htmlspecialchars($sabpaisaMessage ?: $bankMessage ?: 'पेमेंट अयशस्वी'); ?></p>
            </div>
            
            <form action="../advertisement_post.php" method="get">
                <input type="hidden" name="payment_status" value="failed">
                <input type="hidden" name="message" value="<?php echo htmlspecialchars($sabpaisaMessage ?: 'पेमेंट अयशस्वी'); ?>">
                <button type="submit" class="btn btn-primary">पुन्हा प्रयत्न करा</button>
            </form>
        <?php endif; ?>
        
        <p><small>स्वयंचलितपणे पुनर्निर्देशित होण्यासाठी <span id="countdown">5</span> सेकंद...</small></p>
    </div>
    
    <script>
        // Auto redirect after 5 seconds
        let seconds = 5;
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