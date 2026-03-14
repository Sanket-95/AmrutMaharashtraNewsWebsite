<?php
session_start();

// Include the AES256HMACSHA384HEX class for encryption and decryption
require_once 'authentication.php';  // Make sure the correct path to the authentication.php is used

$decText = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $clientTxnId = $_POST["clientTxnId"];
    $encData = null;
    $clientCode = 'ACAD914';  // Your client code
    $authKey = 'VkylGulAs8ysjQcwDU7vHCbSDz+05lxxh43s13/+P1A=';  // Your AES key in base64 format
    $authIV = '5rTyHyY/FDpKUCpiFe+d5K2XkDkXCb99v+5GDWwnoK2KFPIVq629dikwYbluXXze';  // Your HMAC key in base64 format
    
    // Prepare the data to be encrypted
    $encData = "clientCode=" . $clientCode . "&clientTxnId=" . $clientTxnId;
    
    // Use the AES256HMACSHA384HEX class to encrypt the data
    $encryptedData = AES256HMACSHA384HEX::encrypt($authKey, $authIV, $encData);

    // Prepare the data to send in the POST request
    $url = 'https://stage-txnenquiry.sabpaisa.in/SPTxtnEnquiry/getTxnStatusByClientxnId';  // Use the appropriate URL for prod or stage
    $data = array("clientCode" => $clientCode, "statusTransEncData" => $encryptedData);
    $postdata = json_encode($data);
    
    // cURL request to get the response
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $result = curl_exec($ch);
    curl_close($ch);
    $response = json_decode($result, true);
    
    // Process the response
    if ($response) {
        if (isset($response['statusResponseData'])) {
            $enc_response = $response['statusResponseData'];
            $decText = null;
            
            // Use the AES256HMACSHA384HEX class to decrypt the response data
            try {
                $decText = AES256HMACSHA384HEX::decrypt($authKey, $authIV, $enc_response);
            } catch (Exception $e) {
                $decText = "Error: " . $e->getMessage();
            }
        } else {
            $decText = "statusResponseData not found in the response.";
        }
    } else {
        $decText = "Failed to decode the response.";
    }
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SabPaisa Transaction Enquiry</title>
</head>
<body>
 
<h2>SabPaisa Transaction Enquiry</h2>
 
<form action="transenq.php" method="post">
    <label for="clientTxnId">Client Transaction ID:</label>
    <input type="text" id="clientTxnId" name="clientTxnId" required>
    <button type="submit">Submit</button>
</form>
 
<?php
if (!empty($decText)) {
    echo '<div>';
    echo '<strong>Response:</strong><br>';
    echo $decText;
    echo '</div>';
}
?>
 
</body>
</html>
