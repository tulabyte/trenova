<?php

// get reseller
$app->get('/getResellerDetails', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $reseller_id = $db->purify($app->request->get('id'));
    
    $reseller = $db->getOneRecord("SELECT ad_email, ad_name, ad_reseller_code, ad_phone, ad_address, ad_photo, ad_time_reg, ad_last_login, ad_reseller_code, ad_creator_id, SUM(rc_commission) as commission FROM admin LEFT JOIN reseller_commission ON ad_id = rc_reseller_id WHERE ad_id='$reseller_id'");
    
    $reseller_referral = $db->getRecordset("SELECT user_id, user_fullname, user_time_reg FROM user WHERE user_reseller_id = '$reseller_id' ORDER BY user_time_reg DESC");

    $reseller_com = $db->getRecordset("SELECT rc_date, rc_id, rc_status, user_fullname, order_time_created, order_total, course_title, rc_commission FROM reseller_commission LEFT JOIN user ON rc_user_id = user_id LEFT JOIN user_order ON rc_order_id = order_id LEFT JOIN course ON rc_course_id = course_id WHERE rc_reseller_id = '$reseller_id' AND rc_status = 'PENDING' ORDER BY order_time_created DESC");

    $reseller_pay = $db->getRecordset("SELECT rc_date, rc_status, rc_date_paid, user_fullname, order_time_created, order_total, course_title, rc_commission FROM reseller_commission LEFT JOIN user ON rc_user_id = user_id LEFT JOIN user_order ON rc_order_id = order_id LEFT JOIN course ON rc_course_id = course_id  WHERE rc_reseller_id = '$reseller_id' AND rc_status = 'PAID' ORDER BY rc_date_paid DESC");

    if($reseller) {
        //found reseller, return success result
        $response['reseller'] = $reseller;
        $response['reseller_referral'] = $reseller_referral;
        $response['reseller_com'] = $reseller_com;
        $response['reseller_pay'] = $reseller_pay;
        $response['status'] = "success";
        $response["message"] = "Reseller Details Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading reseller!";
        echoResponse(201, $response);
    }
});

//markResellerCommission
$app->get('/markResellerCommission', function() use ($app) {
    $response = array();
    $db = new DbHandler();
    $rc_id = $db->purify($app->request->get('id'));
    $reseller = $db->getOneRecord("SELECT * from reseller_commission WHERE rc_id = '$rc_id' ");
    $rc_date_paid = date('Y-m-d');

    if($reseller) {
        //found reseller, return success result
        $table_to_update = "reseller_commission";
        $columns_to_update = ['rc_status'=>'PAID', 'rc_date_paid'=>$rc_date_paid];
        $where_clause = ['rc_id'=>$rc_id];

        $result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

        if ($result > 0) {
            
        $response['status'] = "success";
        $response["message"] = "Action Successful!";
        echoResponse(200, $response);
        } else{
        $response['status'] = "error";
        $response["message"] = "Error marking as paid!";
        echoResponse(201, $response);
        }
    } else {
        $response['status'] = "error";
        $response["message"] = "Errorr!!!";
        echoResponse(201, $response);
    }
});


//getResellerCommission
$app->get('/getResellerCommission', function() use ($app) {
    $response = array();
    $db = new DbHandler();
    $reseller = $db->getRecordset("SELECT ad_name, rc_id, rc_status, rc_date, user_fullname, order_time_created, order_total, course_title, rc_commission FROM reseller_commission LEFT JOIN user ON rc_user_id = user_id LEFT JOIN user_order ON rc_order_id = order_id LEFT JOIN course ON rc_course_id = course_id LEFT JOIN admin ON rc_reseller_id = ad_id ORDER BY order_time_created DESC");

    if($reseller) {
        //found reseller, return success result
        $response['resellers'] = $reseller;
        $response['status'] = "success";
        $response["message"] = "Reseller Details Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading reseller!";
        echoResponse(201, $response);
    }
});

//getResellerDashboard
$app->get('/getResellerDashboard', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $session = $db->getSession();

    // get id of currently logged in reseller
    $reseller_id = $session['trenova_user']['ad_id'];
    
    $reseller = $db->getOneRecord("SELECT ad_email, ad_reseller_code, ad_name, ad_phone, ad_address, ad_photo, ad_time_reg, ad_last_login, ad_reseller_code, ad_creator_id, SUM(rc_commission) as commission FROM admin LEFT JOIN reseller_commission ON ad_id = rc_reseller_id WHERE ad_id='$reseller_id'");
    $reseller['paid'] = $db->getOneRecord("SELECT SUM(rc_commission) as paid FROM reseller_commission WHERE rc_reseller_id='$reseller_id' AND rc_status = 'PAID' ");
    
    $reseller_referral = $db->getRecordset("SELECT user_id, user_fullname, user_time_reg FROM user WHERE user_reseller_id = '$reseller_id' ORDER BY user_time_reg DESC LIMIT 10");

    $reseller_pay = $db->getRecordset("SELECT rc_date, rc_status, rc_commission FROM reseller_commission WHERE rc_reseller_id = '$reseller_id' ORDER BY rc_date DESC LIMIT 10");

    if($reseller) {
        //found reseller, return success result
        $response['reseller'] = $reseller;
        $response['reseller_referral'] = $reseller_referral;
        $response['reseller_pay'] = $reseller_pay;
        $response['status'] = "success";
        $response["message"] = "Reseller Details Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading reseller!";
        echoResponse(201, $response);
    }
});

//getResellerPaid
$app->get('/getResellerPaid', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $session = $db->getSession();

    // get id of currently logged in reseller
    $reseller_id = $session['trenova_user']['ad_id'];
    
    $reseller_pay = $db->getRecordset("SELECT rc_date, order_total, rc_status, rc_commission, course_title, user_fullname FROM reseller_commission LEFT JOIN user ON rc_user_id = user_id LEFT JOIN course ON rc_course_id = course_id LEFT JOIN user_order ON rc_order_id = order_id WHERE rc_reseller_id = '$reseller_id' AND rc_status = 'PAID' ORDER BY rc_date_paid DESC LIMIT 10");
    $reseller_t = $db->getOneRecord("SELECT SUM(rc_commission) AS commission FROM reseller_commission WHERE rc_reseller_id = '$reseller_id' AND rc_status = 'PAID' ");
    
    if($reseller_pay) {
        //found commisions, return success result
        $response['reseller_pay'] = $reseller_pay;
        $response['reseller_t'] = $reseller_t;
        $response['status'] = "success";
        $response["message"] = "Commission List Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading Paid Commisions!";
        echoResponse(201, $response);
    }
});

//getResellerUnPaid
$app->get('/getResellerUnPaid', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $session = $db->getSession();

    // get id of currently logged in reseller
    $reseller_id = $session['trenova_user']['ad_id'];
    
    $reseller_upay = $db->getRecordset("SELECT rc_date, order_total, rc_status, rc_commission, course_title, user_fullname FROM reseller_commission LEFT JOIN user ON rc_user_id = user_id LEFT JOIN course ON rc_course_id = course_id LEFT JOIN user_order ON rc_order_id = order_id WHERE rc_reseller_id = '$reseller_id' AND rc_status = 'PENDING' ORDER BY rc_date_paid DESC Limit 10");

    $reseller_t = $db->getOneRecord("SELECT SUM(rc_commission) AS commission FROM reseller_commission WHERE rc_reseller_id = '$reseller_id' AND rc_status = 'PENDING' ");
    
    if($reseller_id) {
        //found commisions, return success result
        $response['reseller_upay'] = $reseller_upay;
        $response['reseller_t'] = $reseller_t;
        $response['status'] = "success";
        $response["message"] = "Commission List Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading Commisions!";
        echoResponse(201, $response);
    }
});

// get getReseller list
$app->get('/getResellerList', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $session = $db->getSession();

    // get id of currently logged in admin
    $res_id = $session['trenova_user']['ad_id'];

    // compose query
    $reseller_query = "SELECT ad_id, ad_reg_status, ad_email, ad_address, ad_phone, ad_photo, ad_name, ad_last_login, ad_time_reg, (SELECT COUNT(user_id) FROM user WHERE user_reseller_id = ad_id) AS ref_count from admin WHERE ad_id <> '$res_id' AND ad_is_disabled IS NULL AND ad_type = 'RESELLER' " ;

    $resellers = $db->getRecordset($reseller_query);
    if($resellers) {

/*        //log action
        $log_details = "Accessed Admin List";
        $db->logAction($log_details);*/

        $response['resellers'] = $resellers;
        $response['status'] = "success";
        $response["message"] = "Admins Found!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No admin found!";
        echoResponse(201, $response);
    }
});

