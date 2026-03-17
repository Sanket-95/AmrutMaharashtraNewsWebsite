<?php 
session_start();
include 'Authentication.php';

$encData=null;

// $clientCode='DJ020';   // Please use the credentials shared by your Account Manager  If not, please contact your Account Manage
// $username='DJL754@sp';     // Please use the credentials shared by your Account Manager  If not, please contact your Account Manage
// $password='4q3qhgmJNM4m';     // Please use the credentials shared by your Account Manager  If not, please contact your Account Manage
// $authKey='ISTrmmDC2bTvkxzlDRrVguVwetGS8xC/UFPsp6w+Itg=';      // Please use the credentials shared by your Account Manager  If not, please contact your Account Manage
// $authIV='M+aUFgRMPq7ci+Cmoytp3KJ2GPBOwO72Z2Cjbr55zY7++pT9mLES2M5cIblnBtaX';       // Please use the credentials shared by your Account Manager  If not, please contact your Account Manage

// Production
$clientCode='ACAD914';   // Please use the credentials shared by your Account Manager  If not, please contact your Account Manage
$username='amrut.gom-4@gmail.com';     // Please use the credentials shared by your Account Manager  If not, please contact your Account Manage
$password='ACAD914_SP25756';     // Please use the credentials shared by your Account Manager  If not, please contact your Account Manage
$authKey='VkylGulAs8ysjQcwDU7vHCbSDz+05lxxh43s13/+P1A=';      // Please use the credentials shared by your Account Manager  If not, please contact your Account Manage
$authIV='5rTyHyY/FDpKUCpiFe+d5K2XkDkXCb99v+5GDWwnoK2KFPIVq629dikwYbluXXze';       // Please use the credentials shared by your Account Manager  If not, please contact your Account Manage

// $payerName='name';
// $payerEmail='Test@email.in';
// $payerMobile='1234567890';
// $payerAddress='Patna, Bihar';

// Production
$payerName='name';
$payerEmail='Test@email.in';
$payerMobile='1234567890';
$payerAddress='Patna, Bihar';

$clientTxnId=rand(1000,9999);
$amount=300;
$amountType='INR';
$mcc=5137;
$channelId='W'; 
$callbackUrl='http://127.0.0.1/payment_gatway/SabPaisaPostPgResponse.php';
// Extra Parameter you can use 20 extra parameters(udf1 to udf20)
//$Class='VIII';
//$Roll='1008';

$encData="?clientCode=".$clientCode."&transUserName=".$username."&transUserPassword=".$password."&payerName=".$payerName.
"&payerMobile=".$payerMobile."&payerEmail=".$payerEmail."&payerAddress=".$payerAddress."&clientTxnId=".$clientTxnId.
"&amount=".$amount."&amountType=".$amountType."&mcc=".$mcc."&channelId=".$channelId."&callbackUrl=".$callbackUrl;
//."&udf1=".$Class."&udf2=".$Roll;
				
$AES256HMACSHA384HEX = new AES256HMACSHA384HEX(); 
$data = $AES256HMACSHA384HEX->encrypt($authKey, $authIV, $encData);
    
?>

<!-- <form action="https://stage-securepay.sabpaisa.in/SabPaisa/sabPaisaInit?v=1"method="post"> -->
<form action="https://securepay.sabpaisa.in/SabPaisa/sabPaisaInit?v=1"method="post">
<input type="hidden" name="encData" value="<?php echo $data?>" id="frm1">
<input type="hidden" name="clientCode" value ="<?php echo $clientCode?>" id="frm2">
<input type="submit" id="submitButton" name="submit">
</form>                     
