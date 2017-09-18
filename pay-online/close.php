<?php //error_reporting(E_ALL); ini_set("display_errors", 1); ?>
<?php require_once('-inc-config.php') ?>
<?php require_once('dbconn.php') ?>
<?php require_once("-inc-functions.php"); ?>
<?php
// get payment details from db
$payment_sql = sprintf("SELECT pay_id, pay_amount, pay_order_id, pay_user_id, pay_online_ref, pay_time_initiated, user_fullname user_email FROM user_payment LEFT JOIN user ON pay_user_id = user_id WHERE pay_id = %s ", GetSQLValueString($_GET['id'],"int"));
$payment_rs = mysql_query($payment_sql, $dbconn) or die(mysql_error());
$payment = mysql_fetch_assoc($payment_rs);
?>
<!doctype html>
<html>
    <head>
        <title><?php echo $config['title'] ?></title>
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
                <h3>Learnova</h3>
            </div>
            <!-- /.col-md-4 -->
        </div>
        <!-- /.row -->

        <!-- Content Row -->
        <div class="row">
            
            

            <div class="panel panel-default col-sm-12 col-xs-12">
              <div class="panel-heading">
                <h3 class="panel-title">Returning to app...</h3>
              </div>
              <div class="panel-body">
                <p>This window should close in a few seconds. If it doesn't, please click on the close icon in the top-right corner of this window.</p>
              </div>
            </div>
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
            $(document).ready(function(e) {
                // console.log('jquery');
            });
        </script>

    </body>
</html>