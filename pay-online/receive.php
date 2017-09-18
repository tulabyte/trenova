<?php die('here'); ?>
<?php error_reporting(E_ALL); ini_set("display_errors", 1);  ?>
<?php echo "What is happening here?"; ?>
<?php require_once('-inc-config.php') ?>
<?php require_once('dbconn.php') ?>
<?php require_once("-inc-functions.php"); ?>
<?php require_once('swiftMailer/myswiftmailer.php'); ?>
<?php
print_r($_POST);
if (isset($_GET['gtpay_tranx_id'])) {
    print_r($_POST); die;
    //ensure that token is correct
    //get transaction info from db
    $transactionSQL = sprintf("SELECT * FROM gtpay_transactions WHERE gtpay_tranx_id = %s", 
      GetSQLValueString($_GET['gtpay_tranx_id'], "text"));
    //die($transactionSQL);
    $transactionRS = mysql_query($transactionSQL, $dbconn) or die('Tranx Query:'. mysql_error());
    // found transaction?
    if(mysql_num_rows($transactionRS) < 1) {
        die('Bad Ref - ACCESS DENIED!!!');
        exit;
    }
    $transaction = mysql_fetch_assoc($transactionRS);
    // check token
    if($_POST['gtpay_echo_data'] != $transaction['gtpay_token']) {
        die('Bad Token - ACCESS DENIED!!!');
        exit;
    }

    //dump POST parameters into variables
    extract($_POST);
    //get rid of blank spaces in status code
    $gtpay_tranx_status_code = trim($gtpay_tranx_status_code);

    $dateOfTransaction = date("Y-m-d h:i:s");

    //was transaction cancelled?
      if($gtpay_tranx_status_code == 'G300') {//cancelled by user
        $gtpay_tranx_id = $_GET['gtpay_tranx_id'];
      }

    //update transaction status
  $updateSQL = sprintf("UPDATE gtpay_transactions SET gtpay_tranx_status_code = %s, gtpay_tranx_status_msg = %s WHERE gtpay_tranx_id = %s",
        GetSQLValueString($gtpay_tranx_status_code, "text"),
        GetSQLValueString($gtpay_tranx_status_msg, "text"),
        GetSQLValueString($gtpay_tranx_id, "text")
    );
  $Result = mysql_query($updateSQL, $dbconn) or die('Update GTPay Tranx Query:'. mysql_error());

  //update payment status
  switch($gtpay_tranx_status_code) { //determine which status to use
    case '00': $pay_status = 'SUCCESSFUL'; break;
    case 'G300':
    default: $pay_status = 'FAILED';
  }
  $updateSQL = sprintf("UPDATE payment SET payment_status = '$tr_status' WHERE pay_id = %s", GetSQLValueString($transaction['gtpay_payment_id'], "int"));
  $Result3 = mysql_query($updateSQL, $dbconn) or die('Update Payment Query:'. mysql_error());

    // get payment details from db
    $payment_sql = sprintf("SELECT pay_id, pay_amount, pay_order_id, pay_user_id, pay_online_ref, pay_time_initiated, user_fullname, user_email FROM payment LEFT JOIN user ON pay_user_id = user_id WHERE pay_id = %s ", GetSQLValueString($transaction['gtpay_payment_id'],"int"));
    $payment_rs = mysql_query($payment_sql, $dbconn) or die(mysql_error());
    $payment = mysql_fetch_assoc($payment_rs);
    extract($payment);

    //send notifications
    if($gtpay_tranx_status_code == '00') {
        //die('payment success');
        //successful payment
        //notify payee
        $subject = "Your Learnova Training payment for Order #$pay_order_id was SUCCESSFUL!";
        $body = "
<p>Dear $user_fullname,</p>
<p>Your payment for <strong>Order #$pay_order_id</strong> on <strong>Learnova Training Mobile App</strong> was SUCCESSFUL!</p>
<p>GTPay Transaction Reference number is <strong>$gtpay_tranx_id</strong>.</p>
<p>Thank you for registering. We will contact you with more details shortly</p>
<p>Regards,</p>
<p>Learnova Training</p>";
        MySwiftMailer($config['sender'], 'Learnova Training Mobile', [$user_email], $subject, $body);

        //notify admin
        $subject = "$user_fullname's payment for Order #$pay_order_id was SUCCESSFUL!";
        $body = "
<p>Hello,</p>
<p>$user_fullname's payment for <strong>Order #$pay_order_id</strong> on <strong>Learnova Training Mobile</strong> was SUCCESSFUL!</p>
<p>GTPay Transaction Reference number is <strong>$gtpay_tranx_id</strong>.</p>
<p>You can access full registration details by logging into Learnova Training Backend (http://Learnova-ng.com/Learnova-training)</p>
<p>Regards,</p>
<p>Learnova Training</p>";
        MySwiftMailer($config['sender'], 'Learnova Training Mobile', [$config['notify']], $subject, $body);

    } else {
        //die('payment failure');
        //failed payment
        //notify payee
        $subject = "Your Learnova Training payment for Order #$pay_order_id FAILED!";
        $body = "
<p>Dear $user_fullname,</p>
<p>Unfortunately, your payment for <strong>Order #$pay_order_id</strong> on <strong>Learnova Training Mobile App</strong> FAILED!</p>
<p>GTPay Transaction Reference number is <strong>$gtpay_tranx_id</strong>.</p>
<p>Reason for Failure: $gtpay_tranx_status_msg.</p>
<p>Regards,</p>
<p>Learnova Training Team</p>";
        MySwiftMailer($config['sender'], 'Learnova Training Mobile', [$user_email], $subject, $body);

        //notify admin
        $subject = "$user_fullname's payment for Order #$pay_order_id FAILED!";
        $body = "
<p>Hello,</p>
<p>$user_fullname's payment for <strong>Order #$pay_order_id</strong> on <strong>Learnova Training Mobile</strong> FAILED!</p>
<p>GTPay Transaction Reference number is <strong>$gtpay_tranx_id</strong>.</p>
<p>GTPay Error Code: $gtpay_tranx_status_code.</p>
<p>Reason for Failure: $gtpay_tranx_status_msg.</p>
<p>Regards,</p>
<p>Learnova Website</p>";
        MySwiftMailer($config['sender'], 'Learnova Training Mobile', [$config['notify']], $subject, $body);
    }

}
?>
<!doctype html>
<html>
    <head>
        <title><?php echo $config['title'] ?></title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
        <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="css/small-business.css">
        <link rel="stylesheet" href="bootflat/css/bootflat.css">

    </head>

    <body>

    <!-- Page Content -->
    <div class="container">

        <!-- Heading Row -->
        <div class="row">
            <!-- /.col-md-8 -->
            <div class="col-sm-12 col-xs-12" align="center">
                <img src="img/fta-logo.png" alt="<?php echo $config['fullname']; ?>" width="150px">
                <h3>Learnova Training</h3>
            </div>
            <!-- /.col-md-4 -->
        </div>
        <!-- /.row -->

        <!-- Content Row -->
        <div class="row">

            <?php if($gtpay_tranx_status_code == '00') { ?>

            <div class="panel panel-success col-sm-12 col-xs-12">
              <div class="panel-heading">
                <h3 class="panel-title">PAYMENT SUCCESSFUL!</h3>
              </div>
              <div class="panel-body">
                <table class="table">
                    <tbody>
                        <tr>
                            <td>Ref</td>
                            <td><strong><?php echo $gtpay_tranx_id ?></strong></td>
                        </tr>
                        <tr>
                            <td>Amount Paid</td>
                            <td><strong>₦ <?php echo $pay_amount ?></strong></td>
                        </tr>
                    </tbody>
                </table>
                <p>You will be taken back to the app in a moment. Please <a href="close.php">CLICK HERE</a> if nothing happens</p>
              </div>
            </div>
            <?php } else { ?>
            <div class="panel panel-danger col-sm-12 col-xs-12">
              <div class="panel-heading">
                <h3 class="panel-title">PAYMENT FAILED</h3>
              </div>
              <div class="panel-body">
                <table class="table">
                    <tbody>
                        <tr>
                            <td>Ref</td>
                            <td><strong><?php echo $gtpay_tranx_id ?></strong></td>
                        </tr>
                        <tr>
                            <td>Reason for Failure</td>
                            <td><strong><?php echo $gtpay_tranx_status_msg ?></strong></td>
                        </tr>
                    </tbody>
                </table>
                <p>You will be taken back to the app in a moment. Please <a href="close.php">CLICK HERE</a> if nothing happens</p>
              </div>
            </div>
            <?php } ?>
        </div>
        <!-- /.row -->

        <!-- Footer -->
        <footer>
            <?php include('-inc-footer.php') ?>
        </footer>

    </div>
    <!-- /.container -->

        <!-- Bootstrap -->
        <script src="https://code.jquery.com/jquery-1.11.0.min.js"></script>
        <script src="https://netdna.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>

        <!-- Bootflat's JS files.-->
        <script src="bootflat/js/icheck.min.js"></script>
        <script src="bootflat/js/jquery.fs.selecter.min.js"></script>
        <script src="bootflat/js/jquery.fs.stepper.min.js"></script>

        <script type="text/javascript">
            setTimeout(function(){
                  window.location = 'close.php';
                }, 5000);
            $(document).ready(function(e) {
                // console.log('jquery');
            });
        </script>

    </body>
</html>