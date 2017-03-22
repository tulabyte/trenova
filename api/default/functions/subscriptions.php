<?php

//pause subscription
$app->get('/pauseSubscription', function() use ($app) {
    $response = array();
    $db = new DbHandler();

    $sub_id = $db->purify($app->request->get('id'));

    // update subscription
    $table_to_update = "subscription";
    $columns_to_update = ['sub_status'=>'PAUSED'];
    $where_clause = ['sub_id'=>$sub_id];

    $result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);
    
    if($result) {
        //sub paused, return success
        $response['status'] = "success";
        $response["message"] = "Subscription paused successfully!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error while attempting to pause subscription!";
        echoResponse(201, $response);
    }
});

//resume subscription
$app->get('/resumeSubscription', function() use ($app) {
    $response = array();
    $db = new DbHandler();

    $sub_id = $db->purify($app->request->get('id'));

    // update subscription
    $table_to_update = "subscription";
    $columns_to_update = ['sub_status'=>'ACTIVE'];
    $where_clause = ['sub_id'=>$sub_id];

    $result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);
    
    if($result) {
        //sub resume, return success
        $response['status'] = "success";
        $response["message"] = "Subscription resumed successfully!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error while attempting to resume subscription!";
        echoResponse(201, $response);
    }
});



$app->get('/getSubscriptionList', function() use ($app) {

    $response = array();

    $db = new DbHandler();
    
    $subscriptions = $db->getRecordset("SELECT * FROM subscription LEFT JOIN course ON sub_course_id=course_id LEFT JOIN user ON sub_user_id=user_id");

    if($subscriptions) {
       
        $response['subscriptions'] = $subscriptions;
        $response['status'] = "success";
        $response["message"] = "Subscriptions!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No subscriptions found!";
        echoResponse(201, $response);
    }
});