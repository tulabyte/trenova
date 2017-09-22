<?php //error_reporting(E_ALL); ini_set("display_errors", 1); ?>
<?php require_once('-inc-config.php') ?>
<?php require_once('dbconn.php') ?>
<?php require_once("-inc-functions.php"); ?>
<?php
// get payment details from db
$payment_sql = sprintf("SELECT pay_id, pay_amount, pay_order_id, pay_user_id, pay_online_ref, pay_time_initiated, user_fullname, user_email FROM user_payment LEFT JOIN user ON pay_user_id = user_id WHERE pay_id = %s ", GetSQLValueString($_GET['id'],"int"));
$payment_rs = mysql_query($payment_sql, $dbconn) or die(mysql_error());
$payment = mysql_fetch_assoc($payment_rs);

if(!mysql_num_rows($payment_rs) || $payment['pay_online_ref'] != $_GET['ref']) {
    header("Location: error.php");
}

// parameters
$gtpay_mert_id = '8682';
$gtpay_tranx_id = $payment['pay_online_ref'];
$gtpay_tranx_amt = $payment['pay_amount']*100;
$gtpay_tranx_curr = '566';
$gtpay_user_id = $payment['pay_user_id'];
$txn_date = date('Y-m-d h:i:s');
$gtpay_cust_name = $payment['user_fullname'];
$gtpay_tranx_memo = $gtpay_cust_name."(".$payment['pay_user_id']."):Course.Purchase.on.Learnova.Training.Mobile.($txn_date)";
$gtpay_tranx_noti_url = "http://tulabyte.net/trenova-demo/pay-online/response.php?gtpay_tranx_id=$gtpay_tranx_id";
//$gateway = 'https://ibank.gtbank.com/GTPay/Tranx.aspx';
$gateway = 'http://gtweb2.gtbank.com/orangelocker/gtpaym/tranx.aspx';
$gtpay_token = bin2hex(openssl_random_pseudo_bytes(25));

// generate gt pay transaction hash
$gtpay_fitc_hash = 'D3D1D05AFE42AD50818167EAC73C109168A0F108F32645C8B59E897FA930DA44F9230910DAC9E20641823799A107A02068F7BC0F4CC41D2952E249552255710F';
$hash_string = $gtpay_mert_id . $gtpay_tranx_id . $gtpay_tranx_amt . $gtpay_tranx_curr . $gtpay_user_id . $gtpay_tranx_noti_url . $gtpay_fitc_hash ;
// echo $hash_string;
$gtpay_tranx_hash = hash('sha512', $hash_string, false);

//save gt transaction
$insert_sql = sprintf("INSERT INTO gtpay_transaction (gtpay_tranx_id, gtpay_tranx_curr, gtpay_tranx_amt, gtpay_date, gtpay_user_id, gtpay_payment_id, gtpay_tranx_hash, gtpay_token) VALUES (%s, %s, %s, %s, %s, %s, %s, %s) ",
    GetSQLValueString($gtpay_tranx_id, "text"),
    GetSQLValueString($gtpay_tranx_curr, "int"),
    GetSQLValueString($gtpay_tranx_amt, "int"),
    GetSQLValueString($txn_date, "date"),
    GetSQLValueString($gtpay_user_id, "int"),
    GetSQLValueString($payment['pay_id'], "int"),
    GetSQLValueString($gtpay_tranx_hash, "text"),
    GetSQLValueString($gtpay_token, "text")
);
$insert_rs = mysql_query($insert_sql, $dbconn) or die(mysql_error());

//go down to generate form and submit
?>
<!DOCTYPE html>
<html>
<head>
  <title>Confirm Payment</title>
</head>
<body style="background: #333; color: white;">
<p>Redirecting to GTPay.....</p>
<form action="<?php echo $gateway ?>" method="post" id="payForm"  >
  <input type="hidden" name="gtpay_mert_id" value="<?php echo $gtpay_mert_id ?>">
  <input type="hidden" name="gtpay_tranx_id" value="<?php echo $gtpay_tranx_id ?>">
  <input type="hidden" name="gtpay_tranx_amt" value="<?php echo $gtpay_tranx_amt ?>">
  <input type="hidden" name="gtpay_tranx_curr" value="<?php echo $gtpay_tranx_curr ?>">
  <input type="hidden" name="gtpay_cust_id" value="<?php echo $gtpay_user_id ?>">
  <input type="hidden" name="gtpay_cust_name" value="<?php echo $gtpay_cust_name ?>" />
  <input type="hidden" name="gtpay_tranx_memo" value="<?php echo $gtpay_tranx_memo ?>">
  <input type="hidden" name="gtpay_no_show_gtbank" value="yes" />
  <input type="hidden" name="gtpay_echo_data" value="<?php echo $gtpay_token ?>">
  <input type="hidden" name="gtpay_gway_name" value="" />
  <input type="hidden" id="gtpay_hash" name="gtpay_hash" value="<?php echo $gtpay_tranx_hash ?>">
  <input type="hidden" name="gtpay_tranx_noti_url" value="<?php echo $gtpay_tranx_noti_url ?>">  
  <!-- <input type="submit"> -->
</form>
<script src="https://code.jquery.com/jquery-1.11.0.min.js"></script>
<script type="text/javascript">
  $(document).ready(function(){
    $("#payForm").submit();
  });
</script>
</body>
</html>