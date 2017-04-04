<?php

// getNewUsers for dashboard
$app->get('/getNewUsers', function() use ($app) {
   
$response = array();
$db = new DbHandler();
$new_users = $db->getRecordset("SELECT  * FROM user ORDER BY user_time_reg DESC LIMIT 5");
    if($new_users) {
        //found new users, return success result

        $response['new_users'] = $new_users;
        $response['status'] = "success";
        $response["message"] = "Newest users Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading new users!";
        echoResponse(201, $response);
    }
});

//getDashStats

$app->get('/getDashStats', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    
    $user_count = $db->getOneRecord("SELECT COUNT(user_id) as user_count
                                             FROM user ");

    $sub_count = $db->getOneRecord("SELECT COUNT(sub_user_id) as sub_count FROM subscription WHERE sub_status = 'ACTIVE'");

    $course_count = $db->getOneRecord("SELECT COUNT(course_id) as course_count
                                             FROM course ");

    $total_revenue = $db->getOneRecord("SELECT SUM(pay_amount) as total_revenue
                                             FROM USER_payment WHERE pay_status = 'SUCCESSFUL'");

    $stats['user_count'] = $user_count['user_count'];
    $stats['sub_count'] = $sub_count['sub_count'];
    $stats['course_count'] = $course_count['course_count'];
    $stats['total_revenue'] = $total_revenue['total_revenue'] ? $total_revenue['total_revenue'] : 0;

    if($stats) {
        //found course, return success result

       $response['stats'] = $stats;
        $response['status'] = "success";
        $response["message"] = "Dashboard Stats Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading stats!";
        echoResponse(201, $response);
    }
});


//getLatestSubs

$app->get('/getLatestSubs', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    
$subs = $db->getRecordset("SELECT user_id, user_fullname, course_title, course_id, sub_id, sub_date_started
             FROM subscription 
             LEFT JOIN user ON sub_user_id = user_id 
             LEFT JOIN course ON sub_course_id = course_id
             WHERE sub_status = 'ACTIVE' 
             ORDER BY sub_date_started DESC
             LIMIT 5 ");

  if($subs) {
        //found course, return success result

        $response['latest_subs'] = $subs;
        $response['status'] = "success";
        $response["message"] = "Latest subscriptions Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading latest subscriptions!";
        echoResponse(201, $response);
    }
});


//getTopUsers

$app->get('/getTopUsers', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    
$subs = $db->getRecordset("SELECT user_id, user_fullname, COUNT(sub_user_id) AS sub_count 
            FROM subscription 
            LEFT JOIN user ON sub_user_id = user_id 
            WHERE sub_status = 'ACTIVE' OR sub_status = 'EXPIRED' 
            GROUP BY sub_user_id
            ORDER BY sub_count  
            DESC LIMIT 5 ");

  if($subs) {
        //found course, return success result

        $response['top_users'] = $subs;
        $response['status'] = "success";
        $response["message"] = "Top 5 Users Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error Loading top users!";
        echoResponse(201, $response);
    }
});



//getTopCourses

$app->get('/getTopCourses', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    
$subs = $db->getRecordset("SELECT course_id, course_title, COUNT(sub_user_id) AS sub_count 
            FROM subscription 
            LEFT JOIN course ON sub_course_id = course_id 
            WHERE sub_status = 'ACTIVE' OR sub_status = 'EXPIRED' 
            GROUP BY sub_course_id
            ORDER BY sub_count  
            DESC LIMIT 5 ");

  if($subs) {
        //found course, return success result

        $response['top_courses'] = $subs;
        $response['status'] = "success";
        $response["message"] = "Top 5 Courses Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error Loading courses!";
        echoResponse(201, $response);
    }
});


//getNewPayments

$app->get('/getNewPayments', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    
$subs = $db->getRecordset("SELECT user_fullname, user_email, pay_id, pay_time_completed, pay_amount
            FROM user_payment
            LEFT JOIN user ON pay_user_id = user_id 
            WHERE pay_status = 'SUCCESSFUL' 
            ORDER BY pay_time_completed 
            DESC LIMIT 5 ");

  if($subs) {
        //found course, return success result

        $response['new_payments'] = $subs;
        $response['status'] = "success";
        $response["message"] = "Latest Payments Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error Loading Payments!";
        echoResponse(201, $response);
    }
});

//getLatestNotifications

$app->get('/getLatestNotifications', function() use ($app) {
    $response = array();

    $db = new DbHandler();

$notify_count = $db->getOneRecord("SELECT COUNT(*) as not_count FROM user_payment LEFT JOIN user ON pay_user_id =user_id WHERE pay_method = 'BANK' AND pay_status = 'PROCESSING' ");

$not_list = $db->getRecordset("SELECT user_fullname, pay_amount, pay_time_initiated FROM user_payment LEFT JOIN user ON pay_user_id =user_id WHERE pay_method = 'BANK' AND pay_status = 'PROCESSING' LIMIT 5 ");

$not_feed = $db->getRecordset("SELECT fd_id, fd_topic, fd_date, fd_status, user_fullname FROM feedback LEFT JOIN user ON fd_user_id = user_id WHERE fd_status = 'PENDING' LIMIT 5");

 $not_count = intval($notify_count[not_count]);

  if(!empty($not_list) || !empty($not_count)) {
        //found course, return success result

        $response['not_count'] = $not_count;
        $response['not_list'] = $not_list;
        $response['not_feed'] = $not_feed;
        $response['status'] = "success";
        $response["message"] = "Latest Notifications Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading latest notifications!";
        echoResponse(201, $response);
    }
});