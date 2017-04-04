<?php

// register payment
$app->post('/registerPayment', function() use ($app) {
    
    $response = array();

    $r = json_decode($app->request->getBody());
    verifyRequiredParams(['pay_id', 'pay_bank_date', 'pay_bank_ref'],$r->payment);
    
    $db = new DbHandler();
    $pay_id = $db->purify($r->payment->pay_id); 
    $pay_bank_ref = $db->purify($r->payment->pay_bank_ref); 
    $pay_bank_date = $db->purify($r->payment->pay_bank_date);
    $pay_bank_date = date("Y-m-d", strtotime($pay_bank_date));
    $pay_time_completed = date("Y-m-d h:i:s");
    $pay_order_id = $db->purify($r->payment->pay_order_id);

    $paymentExists = $db->getOneRecord("SELECT 1 FROM user_payment WHERE pay_id='$pay_id'");
    if($paymentExists){
        
        $table_to_update = "user_payment";
        $columns_to_update = ['pay_bank_date'=>$pay_bank_date,'pay_bank_ref'=>$pay_bank_ref,'pay_time_completed'=>$pay_time_completed, 'pay_status'=>'PROCESSING'];
        $where_clause = ['pay_id'=>$pay_id];

        $result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

        if ($result > 0) {
            // create an admin task
            $task_date_created = date("Y-m-d h:i:s");
            $task_details = "Pending Confirmation for Bank Payment for Order $pay_order_id";

            $table_name = "payment_verification_task";
            $column_names = ['task_date_created','task_details','task_payment_id'];
            $values = [$task_date_created, $task_details, $pay_id];
            $result = $db->insertToTable($values, $column_names, $table_name);

            // Send Email?

            $response["status"] = "success";
            $response["message"] = "Payment registered successfully! We will confirm your payment and notify you";
            echoResponse(200, $response);
        } else {
            $response["status"] = "error";
            $response["message"] = "ERROR: Payment Registration failed. Please try again later.";
            echoResponse(201, $response);
        }            
    }else{
        $response["status"] = "error";
        $response["message"] = "ERROR: Couldn't find this payment!";
        echoResponse(201, $response);
    }
});

//get payment
$app->get('/getPayment', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $pay_id = $db->purify($app->request->get('id'));
    
    $payment = $db->getOneRecord("SELECT * FROM user_payment WHERE pay_id='$pay_id'");
    if($payment) {
        //found payment, return success result
        $response['payment'] = $payment;
        $response['status'] = "success";
        $response["message"] = "Payment Details Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading payment!";
        echoResponse(201, $response);
    }
});

$app->post('/createPayment', function() use ($app) {
    
    $response = array();
    $db = new DbHandler();

    // extract body of request
    $r = json_decode($app->request->getBody());

    // verify required fields
    verifyRequiredParams(['pay_method', 'pay_amount', 'pay_order_id'],$r->payment);

    // extract values needed from body of request
    $pay_method = $db->purify($r->payment->pay_method);
    $pay_amount = $db->purify($r->payment->pay_amount);
    $pay_order_id = $db->purify($r->payment->pay_order_id);

    // get logged in user session details
    $session = $db->getSession(); 
    $pay_user_id = $session['trenova_user']['user_id'];

    // generate other necessary values
    $pay_time_initiated = date("Y-m-d h:i:s");

    // try a dummy select - makes no sense for now
    $dummy = $db->getOneRecord("SELECT 1 FROM user");

    if($dummy) {
        // run query to insert new order
        $table_name = "user_payment";
        $column_names = ['pay_amount', 'pay_method', 'pay_order_id', 'pay_user_id', 'pay_time_initiated'];
        $values = [$pay_amount, $pay_method, $pay_order_id, $pay_user_id, $pay_time_initiated];

        $pay_id = $db->insertToTable($values, $column_names, $table_name);
        
        if($pay_id) {
            //order creation complete
            $response['pay_id'] = $pay_id;
            $response['status'] = "success";
            $response["message"] = "Payment created successfully!";
            echoResponse(200, $response);
        } else {
            $response['status'] = "error";
            $response["message"] = "Error creating payment!";
            echoResponse(201, $response);
        }
    }

});

//get payment
$app->get('/getDashTrends', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $start_date = $db->purify($app->request->get('start_date'));
    $end_date = $db->purify($app->request->get('end_date'));

    // user trends
    $user_trends = $sub_trends = [];
    $currdate = $start_date;
    do {
        
        $user_counter = $db->getOneRecord("SELECT COUNT(*) AS user_count FROM user WHERE user_time_reg = '$currdate'");
        $user_trends[] = $user_counter['user_count'];

        $sub_counter = $db->getOneRecord("SELECT COUNT(*) AS sub_count FROM subscription WHERE sub_date_started = '$currdate'");
        $sub_trends[] = $sub_counter['sub_count'];

        // next date
        $currdate = date("Y-m-d", strtotime($currdate) + 86400);

    } while(strtotime($currdate) <= strtotime($end_date));
    
    
    if($sub_trends && $user_trends) {
        //found payment, return success result
        $response['user_trends'] = $user_trends;
        $response['sub_trends'] = $sub_trends;
        $response['status'] = "success";
        $response["message"] = "Dashboard Trends Loaded successfully!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading dashboard trends!";
        echoResponse(201, $response);
    }
});

//deny payment
$app->get('/denyPayment', function() use ($app) {
    $response = array();
    $db = new DbHandler();

    $pay_id = $db->purify($app->request->get('id'));

    // update payment
    $table_to_update = "user_payment";
    $columns_to_update = ['pay_status'=>'FAILED'];
    $where_clause = ['pay_id'=>$pay_id];
    $result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);
    
    if($result) {
        // get payment details
        $payment = $db->getOneRecord("SELECT * FROM user_payment LEFT JOIN user ON pay_user_id = user_id WHERE pay_id = '$pay_id' ");

        // notify user of payment failure
        $swiftmailer = new mySwiftMailer();
        $subject = "Your payment has been DENIED";
        $body = "<p>Hello,</p>
    <p>You submitted the following bank payment details for Order ".$payment['pay_order_id']." via the FITC Training Mobile App:</p>
    <p>
    Date of Payment: ".$payment['pay_bank_date']."<br>
    Teller/Transaction Number: " . $payment['pay_bank_ref'] . "<br>
    Amount Paid: N ". $payment['pay_amount'] ."
    </p>
    <p>We are sorry to inform you that we could NOT find any records to verify your payment, therefore we had to DENY it.</p>
    <p>Please go to your Orders and try to make payment for it again, using the correct payment details this time around. If you have further issues, please send an email to training@fitc-ng.com.</p>
    <p>Thank you for using FITC Training.</p>
    <p>NOTE: please DO NOT REPLY to this email.</p>
    <p><br><strong>FITC Training App</strong></p>";
        $swiftmailer->sendmail('info@fitc-ng.com', 'FITC Training', [$payment['user_email']], $subject, $body);

        //return success
        $response['status'] = "success";
        $response["message"] = "Payment denied successfully!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error while attempting to deny payment! Please try later";
        echoResponse(201, $response);
    }
});

//confirm payment
$app->get('/confirmPayment', function() use ($app) {
    $response = array();
    $db = new DbHandler();
    // payment id
    $pay_id = $db->purify($app->request->get('id'));

    // update payment status
    $table_to_update = "payment";
    $columns_to_update = ['pay_status'=>'SUCCESSFUL', 'pay_time_completed'=>date("Y-m-d h:i:s") ];
    $where_clause = ['pay_id'=>$pay_id];
    $result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

    if($result) {
        
        // get payment details
        $payment = $db->getOneRecord("SELECT * FROM user_payment LEFT JOIN user ON pay_user_id = user_id WHERE pay_id = '$pay_id' ");

        // notify user of payment success
        $swiftmailer = new mySwiftMailer();
        $subject = "Your payment has been CONFIRMED";
        $body = "<p>Hello,</p>
    <p>You submitted the following bank payment details for Order ".$payment['pay_order_id']." via the FITC Training Mobile App:</p>
    <p>
    Date of Payment: ".$payment['pay_bank_date']."<br>
    Teller/Transaction Number: " . $payment['pay_bank_ref'] . "<br>
    Amount Paid: N ". $payment['pay_amount'] ."
    </p>
    <p>We are pleased to inform you that your payment has been CONFIRMED.</p>
    <p>Your subscription is being activated at the moment. You will be notified once it is ready.</p>
    <p>Thank you for using FITC Training.</p>
    <p>NOTE: please DO NOT REPLY to this email.</p>
    <p><br><strong>FITC Training App</strong></p>";
        $swiftmailer->sendmail('info@fitc-ng.com', 'FITC Training', [$payment['user_email']], $subject, $body);

        // get order items
        $order_items = $db->getRecordset("SELECT * FROM order_item LEFT JOIN course ON item_course_id = course_id WHERE item_order_id = '". $payment['pay_order_id'] ."' ");

        $course_list = "<strong></strong>";

        // loop through order items
        foreach ($order_items as $item) {
            // create subscription for order
            $table_name = "subscription";
            $column_names = ['sub_user_id', 'sub_course_id', 'sub_date_started', 'sub_years', 'sub_status', 'sub_order_id'];
            $values = [$payment['pay_user_id'], $item['item_course_id'], date("Y-m-d h:i:s"), $item['item_qty'], 'ACTIVE', $payment['pay_order_id']];
            $itemresult = $db->insertToTable($values, $column_names, $table_name);

            // add course to course list
            $course_list .= "<br>" . $item['course_title'] . " - ". $item['item_qty'] ." year(s)";
        }

        // notify user of subscriptions
        $swiftmailer = new mySwiftMailer();
        $subject = "New Subscription(s) Activated";
        $body = "<p>Hello,</p>
    <p>The following subscription(s) have been activated for you:</p>
    <p>
    $course_list
    </p>
    <p>To access your courses, please login to the FITC Training Mobile App and go to My Courses in the menu.</p>
    <p>Thank you for using FITC Training.</p>
    <p>NOTE: please DO NOT REPLY to this email.</p>
    <p><br><strong>FITC Training App</strong></p>";
        $swiftmailer->sendmail('info@fitc-ng.com', 'FITC Training', [$payment['user_email']], $subject, $body);
            
        if($itemresult) {
            //return success
            $response['status'] = "success";
            $response["message"] = "Payment confirmed successfully! Subscriptions have been activated and user has been notified";
            echoResponse(200, $response);
        } else {
            $response['status'] = "error";
            $response["message"] = "Payment confirmed BUT we ran into an issue while trying to activate subscriptions!";
            echoResponse(201, $response);    
        }
    } else {
        $response['status'] = "error";
        $response["message"] = "Error while attempting to confirm payment! Please try later";
        echoResponse(201, $response);
    }
});

$app->get('/getPaymentList', function() use ($app) {

    $response = array();

    $db = new DbHandler();
    
    $payments = $db->getRecordset("SELECT * FROM user_payment LEFT JOIN user ON pay_user_id=user_id ");

    if($payments) {

        $response["message"] = "Payments loaded successfully!";
        $response['payments'] = $payments;
        $response['status'] = "success";
       
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No payment found!";
        echoResponse(201, $response);
    }

});

$app->get('/getBankWaitingList', function() use ($app) {

    $response = array();

    $db = new DbHandler();
    
    $payments = $db->getRecordset("SELECT * FROM user_payment LEFT JOIN user ON pay_user_id =user_id WHERE pay_method = 'BANK' AND pay_status = 'PROCESSING' ");

    if($payments) {

        $response["message"] = "Payments loaded successfully!";
        $response['payments'] = $payments;
        $response['status'] = "success";
       
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No bank payment (awaiting confirmation) found !";
        echoResponse(201, $response);
    }

});