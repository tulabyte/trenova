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

            /*$table_name = "payment_verification_task";
            $column_names = ['task_date_created','task_details','task_payment_id'];
            $values = [$task_date_created, $task_details, $pay_id];
            $result = $db->insertToTable($values, $column_names, $table_name);*/

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
    $pay_user_id = $db->purify($r->payment->pay_user_id);

    // get logged in user session details
    $session = $db->getSession();
    // $pay_user_id = $session['trenova_user']['user_id'];

    // generate other necessary values
    $pay_time_initiated = date("Y-m-d h:i:s");

    // try a dummy select - makes no sense for now
    $dummy = $db->getOneRecord("SELECT 1 FROM user");

    if($dummy) {
        // run query to insert new payment
        $table_name = "user_payment";
        $column_names = ['pay_amount', 'pay_method', 'pay_order_id', 'pay_user_id', 'pay_time_initiated'];
        if($pay_method == 'ONLINE') {
            $column_names[] = 'pay_online_ref';
        }
        $values = [$pay_amount, $pay_method, $pay_order_id, $pay_user_id, $pay_time_initiated];
        // generate transaction number if bank payment, generate online reference number
        if($pay_method == 'ONLINE') {
            $online_ref = 'LNV' . time() . $db->randomNumericPassword();
            $values[] = $online_ref;
        }
        $pay_id = $db->insertToTable($values, $column_names, $table_name);

        if($pay_method == 'BANK') {
            $user = $db->getOneRecord("SELECT * FROM user WHERE user_id = '$pay_user_id'");
            // Send Email to notify user
            $swiftmailer = new mySwiftMailer();
            $subject = "Bank Payment Details for $pay_order_id";
            $body = "<p>Dear ".$user['user_fullname'].",</p>
    <p>You selected BANK payment method for Order #$pay_order_id. Please find below the bank details:</p>
    <p>
    Amount to Pay: $pay_amount<br>
    Bank Name: Guaranty Trust Bank (GTB)<br>
    Account Name: Trenova<br>
    Account Number: 0123456789
    </p>

    <p>After making this payment, please login to the Learnnova App, go to My Profile > My Payments. You will find this pending payment, tap on it and then click REGISTER PAYMENT. Supply your teller/transaction details and submit. We will then verify the paymemnt and confirm your subscription(s).</p>
    <p>NOTE: please DO NOT REPLY to this email.</p>
    <p><br><strong>Learnnova App</strong></p>";
            $swiftmailer->sendmail(FROM_EMAIL, LONGNAME, [$user['user_email']], $subject, $body);
        }

        if($pay_id) {
            //order creation complete
            $response['pay_id'] = $pay_id;
            $response['ref'] = isset($online_ref)? $online_ref : '';
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
    <p>You submitted the following bank payment details for Order ".$payment['pay_order_id']." via the Learnnova Mobile App:</p>
    <p>
    Date of Payment: ".$payment['pay_bank_date']."<br>
    Teller/Transaction Number: " . $payment['pay_bank_ref'] . "<br>
    Amount Paid: N ". $payment['pay_amount'] ."
    </p>
    <p>We are sorry to inform you that we could NOT find any records to verify your payment, therefore we had to DENY it.</p>
    <p>Please go to your Orders and try to make payment for it again, using the correct payment details this time around. If you have further issues, please send an email to training@trenova.com.</p>
    <p>Thank you for using Learnnova.</p>
    <p>NOTE: please DO NOT REPLY to this email.</p>
    <p><br><strong>Learnnova App</strong></p>";
        $swiftmailer->sendmail(FROM_EMAIL, LONGNAME, [$payment['user_email']], $subject, $body);

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
    $table_to_update = "user_payment";
    $columns_to_update = ['pay_status'=>'SUCCESSFUL', 'pay_time_completed'=>date("Y-m-d h:i:s") ];
    $where_clause = ['pay_id'=>$pay_id];
    $result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

    if($result) {
        
        // get payment details
        $payment = $db->getOneRecord("SELECT * FROM user_payment LEFT JOIN user ON pay_user_id = user_id WHERE pay_id = '$pay_id' ");

        // update order status to COMPLETED
        $table = "user_order";
        $columns = ['order_status'=>'COMPLETED'];
        $where = ['order_id'=>$payment['pay_order_id']];
        $update_result = $db->updateInTable($table, $columns, $where);

        // notify user of payment success
        $swiftmailer = new mySwiftMailer();
        $subject = "Your payment has been CONFIRMED";
        $body = "<p>Hello,</p>
    <p>You submitted the following bank payment details for Order ".$payment['pay_order_id']." via the Learnnova Mobile App:</p>
    <p>
    Date of Payment: ".$payment['pay_bank_date']."<br>
    Teller/Transaction Number: " . $payment['pay_bank_ref'] . "<br>
    Amount Paid: N ". $payment['pay_amount'] ."
    </p>
    <p>We are pleased to inform you that your payment has been CONFIRMED.</p>
    <p>Your subscription is being activated at the moment. You will be notified once it is ready.</p>
    <p>Thank you for using Learnnova.</p>
    <p>NOTE: please DO NOT REPLY to this email.</p>
    <p><br><strong>Learnnova App</strong></p>";
        $swiftmailer->sendmail(FROM_EMAIL, LONGNAME, [$payment['user_email']], $subject, $body);

        /*// send push notification
        $push = new pushHandler();
        if($payment['user_device_token']) {
            $pushresult = $push->createPushNotification([$payment['user_device_token']], "Your Payment #$pay_id has been confirmed!");    
        }*/

        // get order details
        $order = $db->getOneRecord("SELECT order_type FROM user_order WHERE order_id='".$payment['pay_order_id']."'");

        $course_list = "<strong></strong>";

        if($order['order_type'] == 'COURSE') {
            // get order items
            $user_order_items = $db->getRecordset("SELECT * FROM user_order_item LEFT JOIN course ON item_course_id = course_id WHERE item_order_id = '". $payment['pay_order_id'] ."' ");

            // loop through order items
            foreach ($user_order_items as $item) {
                // create subscription for order if it doesn't already exist

                $sub = $db->getOneRecord("SELECT sub_id, sub_months FROM subscription WHERE sub_course_id='".$item['item_course_id']."' AND sub_user_id='".$payment['pay_user_id']."' AND sub_status='ACTIVE'");

                if ($sub) {
                    // user has active sub, add months
                    $table = "subscription";
                    $columns = ['sub_months'=> ($sub['sub_months'] + $item['item_qty']*4) ];
                    $where = ['sub_id'=>$sub['sub_id']];
                    $itemresult = $db->updateInTable($table, $columns, $where);

                    // add course to course list printout
                    $course_list .= "<br>" . $item['course_title'] . " - (extended by) ". $item['item_qty']*4 ." months";
                } else {
                    // no active sub, create new one
                    $table_name = "subscription";
                    $column_names = ['sub_user_id', 'sub_course_id', 'sub_date_started', 'sub_months', 'sub_status', 'sub_order_id'];
                    $values = [$payment['pay_user_id'], $item['item_course_id'], date("Y-m-d h:i:s"), $item['item_qty']*4, 'ACTIVE', $payment['pay_order_id']];
                    $itemresult = $db->insertToTable($values, $column_names, $table_name);

                    // add course to course list printout
                    $course_list .= "<br>" . $item['course_title'] . " - ". $item['item_qty']*4 ." months";
                }
            }
        } else {
            // order is a bundle, get order item, which contains bundle id
            $item = $db->getOneRecord("SELECT item_course_id FROM user_order_item WHERE item_order_id = '".$payment['pay_order_id']."'") ;
            $bdl_id = $item['item_course_id'];

            // get bundle details
            $bundle = $db->getOneRecord("SELECT bdl_type, bdl_subject_id, bdl_school_id, bdl_term, bdl_class_id FROM course_bundle WHERE bdl_id = '$bdl_id'");

            // 4 months by default
            $months = 4;

            // get the bundle items
            switch ($bundle['bdl_type']) {
                case 'CUSTOM':
                    $bundle_items = $db->getRecordset("SELECT course_title, course_id FROM course_bundle_item LEFT JOIN course ON cbi_course_id = course_id WHERE cbi_bundle_id = '$bdl_id'");
                    break;
                
                case 'TERM':
                    $bundle_items = $db->getRecordset("SELECT course_title, course_id FROM course LEFT JOIN class ON course_class_id = class_id WHERE class_school_id = '".$bundle['bdl_school_id']."' AND course_subject_id = '".$bundle['bdl_subject_id']."' AND course_term = '".$bundle['bdl_term']."' ");
                    break;

                case 'CLASS':
                    $bundle_items = $db->getRecordset("SELECT course_title, course_id FROM course WHERE course_class_id = '".$bundle['bdl_class_id']."' AND course_subject_id = '".$bundle['bdl_subject_id']."' ORDER BY course_term ");
                    break;

                case 'YEAR':
                    $bundle_items = $db->getRecordset("SELECT course_title, course_id FROM course LEFT JOIN class ON course_class_id = class_id WHERE class_school_id = '".$bundle['bdl_school_id']."' AND course_subject_id = '".$bundle['bdl_subject_id']."' ORDER BY course_class_id, course_term ");
                    $months = 12; //one year subscription
                    break;
            }

            if($bundle_items) {
                // loop through bundle items
                foreach ($bundle_items as $item) {
                    // create subscription for order if it doesn't exist
                    $sub = $db->getOneRecord("SELECT sub_id, sub_months FROM subscription WHERE sub_course_id='".$item['course_id']."' AND sub_user_id='".$payment['pay_user_id']."' AND sub_status='ACTIVE'");

                    if($sub) {
                        // user has active sub, add months
                        $table = "subscription";
                        $columns = ['sub_months'=> ($sub['sub_months'] + $months) ];
                        $where = ['sub_id'=>$sub['sub_id']];
                        $itemresult = $db->updateInTable($table, $columns, $where);

                        // add course to course list printout
                        $course_list .= "<br>" . $item['course_title'] . " - (extended by) ". $months ." months";
                    } else {
                        $table_name = "subscription";
                        $column_names = ['sub_user_id', 'sub_course_id', 'sub_date_started', 'sub_months', 'sub_status', 'sub_order_id'];
                        $values = [$payment['pay_user_id'], $item['course_id'], date("Y-m-d h:i:s"), $months, 'ACTIVE', $payment['pay_order_id']];
                        $itemresult = $db->insertToTable($values, $column_names, $table_name);

                        // add course to course list
                        $course_list .= "<br>" . $item['course_title'] . " - ". $months ." months";
                    }
                }

                // notify user of subscriptions
                $swiftmailer = new mySwiftMailer();
                $subject = "New Subscription(s) Activated";
                $body = "<p>Hello,</p>
            <p>The following subscription(s) have been activated for you:</p>
            <p>
            $course_list
            </p>
            <p>To access your courses, please login to the Learnnova Mobile App and go to My Subscriptions in the menu.</p>
            <p>Thank you for using Learnnova.</p>
            <p>NOTE: please DO NOT REPLY to this email.</p>
            <p><br><strong>Learnnova App</strong></p>";
                $swiftmailer->sendmail(FROM_EMAIL, LONGNAME, [$payment['user_email']], $subject, $body);
                    
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
                $response["message"] = "Payment confirmed BUT the bundle has no items to activate!";
                echoResponse(201, $response); 
            }   

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
    
    $payments = $db->getRecordset("SELECT * FROM user_payment LEFT JOIN user ON pay_user_id =user_id WHERE pay_method = 'BANK' AND pay_status = 'PROCESSING' ORDER BY pay_time_completed DESC");

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

$app->get('/getUserPaymentList', function() use ($app) {

    $response = array();
    $db = new DbHandler();

    $user_id = $db->purify($app->request->get('id'));
    
    $payments = $db->getRecordset("SELECT * FROM user_payment WHERE pay_user_id = '$user_id' ORDER BY pay_time_initiated DESC ");

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

//get payment
$app->get('/checkPaymentStatus', function() use ($app) {

    $response = array();

    $db = new DbHandler();
    $pay_id = $db->purify($app->request->get('id'));

    $payment = $db->getOneRecord("SELECT * FROM user_payment LEFT JOIN user ON pay_user_id = user_id WHERE pay_id='$pay_id'");
    if($payment) {
        if($payment['pay_status'] == 'SUCCESSFUL') {
            $response['status'] = 'successful';
            // update order status to COMPLETED
            $table = "user_order";
            $columns = ['order_status'=>'COMPLETED'];
            $where = ['order_id'=>$payment['pay_order_id']];
            $update_result = $db->updateInTable($table, $columns, $where);

            // get order details
            $order = $db->getOneRecord("SELECT order_type FROM user_order WHERE order_id='".$payment['pay_order_id']."'");

            $course_list = "<strong></strong>";
            $course_count = 0;

            // create the related subscriptions
            $course_list = "<strong></strong>";

            if($order['order_type'] == 'COURSE') {
                // get order items
                $user_order_items = $db->getRecordset("SELECT * FROM user_order_item LEFT JOIN course ON item_course_id = course_id WHERE item_order_id = '". $payment['pay_order_id'] ."' ");

                // loop through order items
                foreach ($user_order_items as $item) {
                    // create subscription for order if it doesn't already exist
                    $sub = $db->getOneRecord("SELECT sub_id, sub_months FROM subscription WHERE sub_course_id='".$item['item_course_id']."' AND sub_user_id='".$payment['pay_user_id']."' AND sub_status='ACTIVE'");

                    if ($sub) {
                        // user has active sub, add months
                        $table = "subscription";
                        $columns = ['sub_months'=> ($sub['sub_months'] + $item['item_qty']*4) ];
                        $where = ['sub_id'=>$sub['sub_id']];
                        $itemresult = $db->updateInTable($table, $columns, $where);

                        // add course to course list printout
                        $course_list .= "<br>" . $item['course_title'] . " - (extended by) ". $item['item_qty']*4 ." months";
                    } else {
                        // no active sub, create new one
                        $table_name = "subscription";
                        $column_names = ['sub_user_id', 'sub_course_id', 'sub_date_started', 'sub_months', 'sub_status', 'sub_order_id'];
                        $values = [$payment['pay_user_id'], $item['item_course_id'], date("Y-m-d h:i:s"), $item['item_qty']*4, 'ACTIVE', $payment['pay_order_id']];
                        $itemresult = $db->insertToTable($values, $column_names, $table_name);

                        // add course to course list printout
                        $course_list .= "<br>" . $item['course_title'] . " - ". $item['item_qty']*4 ." months";
                    }
                    $course_count++;
                }
                $response["message"] = "Payment SUCCESSFUL! Your Subscriptions have been activated. Please go to My Subscriptions to access your courses.";
            } else {
                // order is a bundle, get order item, which contains bundle id
                $item = $db->getOneRecord("SELECT item_course_id FROM user_order_item WHERE item_order_id = '".$payment['pay_order_id']."'") ;
                $bdl_id = $item['item_course_id'];

                // get bundle details
                $bundle = $db->getOneRecord("SELECT bdl_type, bdl_subject_id, bdl_school_id, bdl_term, bdl_class_id FROM course_bundle WHERE bdl_id = '$bdl_id'");

                // 4 months by default
                $months = 4;

                // get the bundle items
                switch ($bundle['bdl_type']) {
                    case 'CUSTOM':
                        $bundle_items = $db->getRecordset("SELECT course_title, course_id FROM course_bundle_item LEFT JOIN course ON cbi_course_id = course_id WHERE cbi_bundle_id = '$bdl_id'");
                        break;

                    case 'TERM':
                        $bundle_items = $db->getRecordset("SELECT course_title, course_id FROM course LEFT JOIN class ON course_class_id = class_id WHERE class_school_id = '".$bundle['bdl_school_id']."' AND course_subject_id = '".$bundle['bdl_subject_id']."' AND course_term = '".$bundle['bdl_term']."' ");
                        break;

                    case 'CLASS':
                        $bundle_items = $db->getRecordset("SELECT course_title, course_id FROM course WHERE course_class_id = '".$bundle['bdl_class_id']."' AND course_subject_id = '".$bundle['bdl_subject_id']."' ORDER BY course_term ");
                        break;

                    case 'YEAR':
                        $bundle_items = $db->getRecordset("SELECT course_title, course_id FROM course LEFT JOIN class ON course_class_id = class_id WHERE class_school_id = '".$bundle['bdl_school_id']."' AND course_subject_id = '".$bundle['bdl_subject_id']."' ORDER BY course_class_id, course_term ");
                        $months = 12; //one year subscription
                        break;
                }

                if($bundle_items) {
                    // loop through bundle items
                    foreach ($bundle_items as $item) {
                        // create subscription for order if it doesn't exist
                        $sub = $db->getOneRecord("SELECT sub_id, sub_months FROM subscription WHERE sub_course_id='".$item['course_id']."' AND sub_user_id='".$payment['pay_user_id']."' AND sub_status='ACTIVE'");

                        if($sub) {
                            // user has active sub, add months
                            $table = "subscription";
                            $columns = ['sub_months'=> ($sub['sub_months'] + $months) ];
                            $where = ['sub_id'=>$sub['sub_id']];
                            $itemresult = $db->updateInTable($table, $columns, $where);

                            // add course to course list printout
                            $course_list .= "<br>" . $item['course_title'] . " - (extended by) ". $months ." months";
                        } else {
                            $table_name = "subscription";
                            $column_names = ['sub_user_id', 'sub_course_id', 'sub_date_started', 'sub_months', 'sub_status', 'sub_order_id'];
                            $values = [$payment['pay_user_id'], $item['course_id'], date("Y-m-d h:i:s"), $months, 'ACTIVE', $payment['pay_order_id']];
                            $itemresult = $db->insertToTable($values, $column_names, $table_name);

                            // add course to course list
                            $course_list .= "<br>" . $item['course_title'] . " - ". $months ." months";
                        }
                        $course_count++;
                    }
                    $response["message"] = "Payment SUCCESSFUL! Your Bundle Subscriptions have been activated. Please go to My Subscriptions to access your courses.";

                } else {
                    $response["message"] = "Payment confirmed BUT the bundle has no items to activate!";
                }

            }

            if($course_count > 0) {
                // notify user of subscriptions
                $swiftmailer = new mySwiftMailer();
                $subject = "New Subscription(s) Activated";
                $body = "<p>Hello,</p>
            <p>The following subscription(s) have been activated for you:</p>
            <p>
            $course_list
            </p>
            <p>To access your courses, please login to the Learnnova Mobile App and go to My Subscriptions in the menu.</p>
            <p>Thank you for using Learnnova.</p>
            <p>NOTE: please DO NOT REPLY to this email.</p>
            <p><br><strong>Learnnova App</strong></p>";
                $swiftmailer->sendmail(FROM_EMAIL, LONGNAME, [$payment['user_email']], $subject, $body);
            }

            if(!$itemresult) {
                $response["message"] = "Payment successful BUT we ran into an issue while trying to activate subscriptions!";
            }

            echoResponse(200, $response);
        } else {
            // return the error
            $response['status'] = "error";
            $response["message"] = "Payment NOT successful! Please try again later.";
            echoResponse(201, $response);
        }
    } else {
        $response['status'] = "error";
        $response["message"] = "Payment NOT found! Please try again later.";
        echoResponse(201, $response);
    }
});