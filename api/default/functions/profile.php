<?php
$app->post('/editProfile', function() use ($app) {
    
    $db = new DbHandler();
    $session = $db->getSession();

    // get id of currently logged in admin
    $ad_id = $session['trenova_user']['ad_id'];
    
    $response = array();

    $r = json_decode($app->request->getBody());
    verifyRequiredParams(['ad_name'],$r->profile);
    $db = new DbHandler();
    $ad_email = $db->purify($r->profile->ad_email);
    $ad_name = $db->purify($r->profile->ad_name);
    $ad_photo = $db->purify($r->profile->ad_photo);
    $ad_phone = $db->purify($r->profile->ad_phone);
    $ad_address = $db->purify($r->profile->ad_address);

    $isAdminExists = $db->getOneRecord("SELECT 1 FROM admin WHERE ad_id='$ad_id'");
    if($isAdminExists){
        $table_to_update = "admin";
        $columns_to_update = ['ad_email'=>$ad_email, 'ad_name'=>$ad_name, 'ad_photo'=>$ad_photo, 'ad_phone'=>$ad_phone, 'ad_address'=>$ad_address ];
        $where_clause = ['ad_id'=>$ad_id];

        $result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

        if ($result > 0) {
            //log action
            $log_details = "Edited Admin: $ad_email (ID: $ad_id)";
            $db->logAction($log_details);

            $response["status"] = "success";
            $response["message"] = "Update successfully";
            echoResponse(200, $response);
        } else {
            $response["status"] = "error";
            $response["message"] = "Failed to update. Please try again";
            echoResponse(201, $response);
        }            
    }else{
        $response["status"] = "error";
        //$response['message'] = $r->module;
        $response["message"] = "ERROR: Admin does not exist!";
        echoResponse(201, $response);
    }
});

//get Profile
$app->get('/getProfile', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $session = $db->getSession();
    // get id of currently logged in admin
    $ad_id = $session['trenova_user']['ad_id'];
    
    $profile = $db->getOneRecord("SELECT ad_email, ad_name, ad_phone, ad_address, ad_photo FROM admin WHERE ad_id='$ad_id'");
    if($profile) {
        //found course, return success result

        $response['profile'] = $profile;
        $response['status'] = "success";
        $response["message"] = "Profile Details Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading profile!";
        echoResponse(201, $response);
    }
});

//delete profile
$app->get('/deleteProfile', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $filename = $db->purify($app->request->get('filename'));
    
    unlink('../../img/admin-images/'.$filename);

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