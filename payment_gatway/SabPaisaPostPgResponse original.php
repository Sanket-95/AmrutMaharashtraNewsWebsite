<?php
session_start();
include('Authentication.php');
$query = $_REQUEST['encResponse'];

$authKey = 'ISTrmmDC2bTvkxzlDRrVguVwetGS8xC/UFPsp6w+Itg=';
$authIV = 'M+aUFgRMPq7ci+Cmoytp3KJ2GPBOwO72Z2Cjbr55zY7++pT9mLES2M5cIblnBtaX';

$decText = null;
$AES256HMACSHA384HEX = new AES256HMACSHA384HEX();
$decText = $AES256HMACSHA384HEX -> decrypt($authKey, $authIV, $query); 
echo $decText;

$token = strtok($decText,"&");
//echo $token;
$i=0;


while ($token !== false)
{
  $i=$i+1;
  $token1=strchr($token, "=");
  $token=strtok("&");
  $fstr=ltrim($token1,"=");

 //echo "i-". $i . '='. $fstr;
 //echo '<br>';

 //echo $fstr;
 if($i==1)
      $payerName=$fstr;
  if($i==2)
      $payerEmail=$fstr;
  if($i==3)
      $payerMobile=$fstr;
  if($i==4)
      $clientTxnId=$fstr;
  if($i==5)
      $payerAddress=$fstr;
  if($i==6)
      $amount=$fstr;
  if($i==7)
      $clientCode=$fstr;
  if($i==8)
      $paidAmount=$fstr;
  if($i==9)
      $paymentMode=$fstr;
  if($i==10)
      $bankName=$fstr;
  if($i==11)
      $amountType=$fstr;
  if($i==12)
      $status=$fstr;  
  if($i==13)
	    $statusCode=$fstr; 
  if($i==14)
	    $challanNumber=$fstr;
  if($i==15)
	    $sabpaisaTxnId=$fstr;
  if($i==16)
	    $sabpaisaMessage=$fstr;
  if($i==17)
	    $bankMessage=$fstr;
  if($i==18)
	    $bankErrorCode=$fstr;
  if($i==19)
	    $sabpaisaErrorCode=$fstr;
  if($i==20)
	    $bankTxnId=$fstr;				
  if($i==21)
      $transDate=$fstr;

	if($token == true)
	{
	   // $up = "UPDATE  buy_now SET txid='$pgTxnId', tx_dt='$transDate', status='1' WHERE student_id='$userid'";
	      //$up = "UPDATE  buy_now SET txid='$pgTxnId', tx_dt='$transDate', status=1 WHERE student_id=$ufd20";
	     // echo $up;
	  //  mysqli_query($conn,$up);
	    
	}

}
?>

<?php
//include('header.php');
?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-bar">
            <div class="page-title-breadcrumb">
                <div class=" pull-left">
                    <div class="page-title">Payment Success Page</div>
                </div>
               
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="card card-box">
                    <div class="card-body " id="bar-parent2">
                        <div class="row">
                          <h1>Thank You, Your payment for Rs. <?php echo $amount;?> is <?= $status; ?>. You can have your reciept by clicking on print button given below. </h1>
                            <div class="col-md-6 col-sm-6">
                                
                                    <a href="pdf/fpdf/add_receipt.php?user_id=<?php echo $userid?>&pay_type=Pros_Fee" class="btn btn-success" target="_blank">Print Receipt</a>
                                    <a href="download_prospectus.php?user_id=<?php echo $userid?>" class="btn btn-primary">Download Prospectus</a>
                                    <br>
                                     <br>
                                    <p> <span class="badge badge-sucess">Note:</span> You can print recipt any time if required.</p>
                                </div>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    // include('footer.php');
     ?>