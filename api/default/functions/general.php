<?php

//delete file
$app->get('/deleteFile', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $filename = $db->purify($app->request->get('filename'));
    
    unlink('../../img/course-images/'.$filename);

    if(!file_exists('../../img/course-images/'.$filename)) {
        //user deleted
        $response['status'] = "success";
        $response["message"] = "File Deleted successfully!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error deleting file!";
        echoResponse(201, $response);
    }

    // $response["user_id"] = $user_id;
    // echoResponse(200, $response);
});

$app->get('/test', function() use ($app) {
    $response = array();

    $db = new DbHandler();

    //test if this api is working. Return some params
    $response['param_submitted'] = $app->request->get('id');
    $response['owner'] = "Yemi Adetula";
    $response['time'] = date('Y-m-d h:i:s');
    $response['status'] = "success";
    $response["message"] = "API Working perfectly!";
    echoResponse(200, $response);
});

// toggle item i.e. set disabled field on or off
$app->get('/toggleItem', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $item_type = $db->purify($app->request->get('type'));
    $item_id = $db->purify($app->request->get('id'));
    $item_val = $db->purify($app->request->get('val'));
    

    switch ($item_type) {
        case 'admin':
            $prefix = 'ad';
            break;

        case 'message':
            $prefix = 'msg';
            break;
    }

    $table_to_update = $item_type;

    switch ($item_val) {
        case 'off':
            $columns_to_update = [$prefix.'_is_disabled'=>1];
            $where_clause = [$prefix.'_id'=>$item_id];
            $result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);
            $actioned = 'Disabled';   
        break;

        case 'on':
            $column_to_update = $prefix.'_is_disabled';
            $where_clause = [$prefix.'_id'=>$item_id];
            $result = $db->updateToNull($table_to_update, $column_to_update, $where_clause); 
            $actioned = 'Enabled';
        break;

    }

    if($result > 0) {

        //log action
        $log_details = "$actioned $item_type: ID ($item_id)";
        $db->logAction($log_details);

        $response['status'] = "success";
        $response["message"] = "$item_type $actioned successfully!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error! $item_type NOT $actioned";
        echoResponse(201, $response);
    }

});

// check if an image exists
$app->get('/checkImage', function() use ($app) {
   $response = array();

    $db = new DbHandler();
    
    $filename = $db->purify($app->request->get('filename'));
    $type = $db->purify($app->request->get('type'));

    if(empty($filename)) {
        $response['status'] = "error";
        $response["message"] = "Filename missing!";
        echoResponse(201, $response);
        break;
    }

    switch ($type) {
        case 'course':
            $location = '../../img/course-images/';
            break;
        
        case 'avatar':
            $location = '../../img/user-avatars/';
            break;

        default:
            $response['status'] = "error";
            $response["message"] = "Location missing!";
            echoResponse(201, $response);
            break;
    }

    if(file_exists($location . $filename)) {
        $response['status'] = "success";
        $response['message'] = "Image exists!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Image not found!";
        echoResponse(201, $response);
    }
});

$app->get('/testSMS', function() use ($app) {
    $response = array();

    $db = new DbHandler();

    $sms = new smsHandler();
    //SMS The User token for confirmation.
    //SMS Token 'A' to the user
    $msg_sms = 'Testing SMS from Learnnova App';
    $sms->SendSMS($msg_sms, SHORTNAME, '08055811697', 0);

    //test if this api is working. Return some params
    $response['status'] = "success";
    $response["message"] = "SMS sent successfully!";
    echoResponse(200, $response);
});