<?php

//create admin
$app->post('/createAdmin', function() use ($app) {
    
    $response = array();

    $r = json_decode($app->request->getBody());
    verifyRequiredParams(array('ad_name','ad_email','ad_type','ad_password'),$r->admin);
    //require_once 'passwordHash.php';
    $db = new DbHandler();
    $ad_name = $db->purify($r->admin->ad_name);
    $ad_email = $db->purify($r->admin->ad_email);
    $ad_password = $db->purify($r->admin->ad_password);
    $ad_type = $db->purify($r->admin->ad_type);  
    $ad_time_reg = date("Y-m-d H:i:s");

    $isAdminExists = $db->getOneRecord("SELECT 1 FROM admin WHERE ad_email='$ad_email'");
    if(!$isAdminExists){
        //$r->admin->password = passwordHash::hash($password);
        $session = $db->getSession();
        $table_name = "admin";
        $column_names = ['ad_name','ad_email','ad_type','ad_password','ad_time_reg', 'ad_reg_status', 'ad_creator_id', 'ad_email_confirmed'];
        $values = [$ad_name, $ad_email, $ad_type, $ad_password, $ad_time_reg, 'VERIFIED', $session['trenova_user']['ad_id'], 1 ];
        // check for extra fields if AGENT or RESELLER
        if(in_array($ad_type, ['AGENT', 'RESELLER'])) {
            $ad_phone = $db->purify($r->admin->ad_phone);
            $ad_address = $db->purify($r->admin->ad_address);
            $column_names = array_merge($column_names, ['ad_phone','ad_address']);
            $values = array_merge($values, [$ad_phone , $ad_address]);
        }

        $result = $db->insertToTable($values, $column_names, $table_name);

        if ($result != NULL) {
            $response["status"] = "success";
            $response["message"] = "$ad_type created successfully";
            $response["ad_id"] = $result;

            //send email notification to new admin
            $swiftmailer = new mySwiftMailer();
            $subject = "Your new $ad_type Account on ".SHORTNAME;
            $body = "<p>Dear $ad_name,</p>
    <p>A(n) $ad_type account has been created for you on ".SHORTNAME.". You can login using the following details:</p>
    <p>
    URL: ".SITE_URL."<br>
    Email: $ad_email<br>
    Password: $ad_password
    </p>
    <p>You are advised to change your password to something more personal once you login.</p>
    <p>Thank you for using ".SHORTNAME.".</p>
    <p>NOTE: please DO NOT REPLY to this email.</p>
    <p><br><strong>".LONGNAME."</strong></p>";
            $swiftmailer->sendmail(FROM_EMAIL, SHORTNAME, [$ad_email], $subject, $body);

            //log action
            $log_details = "Created $ad_type: $ad_name (ID: $result)";
            $db->logAction($log_details);

            echoResponse(200, $response);
        } else {
            $response["status"] = "error";
            $response["message"] = "Failed to create $ad_type. Please try again";
            echoResponse(201, $response);
        }            
    }else{
        $response["status"] = "error";
        //$response['message'] = $r->admin;
        $response["message"] = "$ad_type with the provided email already exists, please try another!";
        echoResponse(201, $response);
    }
});

// edit admin
$app->post('/editAdmin', function() use ($app) {
    
    $response = array();

    $r = json_decode($app->request->getBody());
    verifyRequiredParams(array('ad_id', 'ad_name','ad_email','ad_type','ad_password'),$r->admin);
    //require_once 'passwordHash.php';
    $db = new DbHandler();
    $ad_id = $db->purify($r->admin->ad_id);
    $ad_name = $db->purify($r->admin->ad_name);
    $ad_email = $db->purify($r->admin->ad_email);
    $ad_password = $db->purify($r->admin->ad_password);
    $ad_type = $db->purify($r->admin->ad_type); 

    $isAdminExists = $db->getOneRecord("SELECT 1 FROM admin WHERE ad_id='$ad_id'");
    if($isAdminExists){
        //$r->admin->password = passwordHash::hash($password);
        $table_to_update = "admin";
        $columns_to_update = ['ad_name'=>$ad_name,'ad_email'=>$ad_email,'ad_type'=>$ad_type,'ad_password'=>$ad_password];
        // check for extra fields if AGENT or RESELLER
        if(in_array($ad_type, ['AGENT', 'RESELLER'])) {
            $ad_phone = $db->purify($r->admin->ad_phone);
            $ad_address = $db->purify($r->admin->ad_address);
            $columns_to_update = array_merge($columns_to_update, ['ad_phone'=>$ad_phone,'ad_address'=>$ad_address]);
        }
        $where_clause = ['ad_id'=>$ad_id];

        $result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

        if ($result > 0) {
            //log action
            $log_details = "Edited $ad_type: $ad_name (ID: $ad_id)";
            $db->logAction($log_details);

            $response["status"] = "success";
            $response["message"] = "$ad_type updated successfully";
            echoResponse(200, $response);
        } else {
            $response["status"] = "error";
            $response["message"] = "Failed to update $ad_type. Please try again";
            echoResponse(201, $response);
        }            
    }else{
        $response["status"] = "error";
        $response["message"] = "$ad_type does not exist!";
        echoResponse(201, $response);
    }
});

//get admin
$app->get('/getAdmin', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $ad_id = $db->purify($app->request->get('id'));
    
    $admin = $db->getOneRecord("SELECT * FROM admin WHERE ad_id='$ad_id'");
    if($admin) {
        //log action
        $log_details = "Accessed Admin Details: ".$admin['ad_name']." (ID: ".$admin['ad_id'].")";
        $db->logAction($log_details);

        //found admin, return success result
        $response['admin'] = $admin;
        $response['status'] = "success";
        $response["message"] = "Admin Details Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading admin!";
        echoResponse(201, $response);
    }

    // $response["ad_id"] = $ad_id;
    // echoResponse(200, $response);
});

// get admin list
$app->get('/getAdminList', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $session = $db->getSession();

    // list type
    $type = $db->purify($app->request->get('type'));
    $status = $db->purify($app->request->get('status'));

    // get id of currently logged in admin
    $admin_id = $session['trenova_user']['ad_id'];

    // compose query
    $admin_query = "SELECT * from admin WHERE ad_id <> '$admin_id' AND ad_is_disabled IS NULL";
    if(!empty($type)) {
        if($type == 'ADMIN') {
            $admin_query .= " AND ( ad_type = 'ADMIN' OR ad_type = 'TEACHER' OR ad_type = 'SUPER' )";
        } else {
            $admin_query .= " AND ad_type = '$type' ";
        }
    }
    if(!empty($status)) {
        $admin_query .= " AND ad_reg_status='$status' ";
    }
    $admins = $db->getRecordset($admin_query);
    if($admins) {
        //admins found

        //log action
        $log_details = "Accessed Admin List";
        $db->logAction($log_details);

        $response['admins'] = $admins;
        $response['status'] = "success";
        $response["message"] = "Admins Found!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No admin found!";
        echoResponse(201, $response);
    }
});

// get admin logs
$app->get('/getAdminLogs', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    
    $logs = $db->getRecordset("SELECT * FROM admin_log ORDER BY log_time DESC ");
    if($logs) {
        //logs found

        $response['logs'] = $logs;
        $response['status'] = "success";
        $response["message"] = "Admin Logs Found!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No admin logs found!";
        echoResponse(201, $response);
    }
});

// delete admin
$app->get('/deleteAdmin', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $ad_id = $db->purify($app->request->get('id'));

    //get admin details
    $admin = $db->getOneRecord("SELECT ad_id, ad_name FROM admin WHERE ad_id='$ad_id'");

    $table_name = 'admin';
    $col_name = 'ad_id';
    $result = $db->deleteFromTable($table_name, $col_name, $ad_id);

    if($result > 0) {
        //admin deleted

        //log action
        $log_details = "Deleted Admin: ".$admin['ad_name']." ($ad_id)";
        $db->logAction($log_details);

        $response['status'] = "success";
        $response["message"] = "Admin Deleted successfully!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error deleting admin!";
        echoResponse(201, $response);
    }

    // $response["ad_id"] = $ad_id;
    // echoResponse(200, $response);
});

// verify admin
$app->get('/verifyAdmin', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $ad_id = $db->purify($app->request->get('id'));
    $response['id_received'] = $ad_id;

    //get admin details
    $admin = $db->getOneRecord("SELECT ad_id, ad_name, ad_email, ad_type, ad_reg_status FROM admin WHERE ad_id='$ad_id'");

    if($admin) {
        // udpate the status
        $table_to_update = "admin";
        $columns_to_update = ['ad_reg_status'=>'VERIFIED'];
        $where_clause = ['ad_id'=>$ad_id];
        $result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);
        if($result > 0 || $admin['ad_reg_status'] == 'VERIFIED') {
            //admin verified

            //log action
            $log_details = "Verified $ad_type: ".$admin['ad_name']." ($ad_id)";
            $db->logAction($log_details);

            // send an email to the admin
            $swiftmailer = new mySwiftMailer();
            $subject = "Your $ad_type Account on ".SHORTNAME."has been VERIFIED";
            $body = "<p>Dear $ad_name,</p>
    <p>Your $ad_type Account has been VERIFIED! You now have full access to the ".SHORTNAME." Web Portal</p>
    <p>Thank you for using ".SHORTNAME.".</p>
    <p>NOTE: please DO NOT REPLY to this email.</p>
    <p><br><strong>".LONGNAME."</strong></p>";
            $swiftmailer->sendmail(FROM_EMAIL, SHORTNAME, [$admin['ad_email']], $subject, $body);

            // send http response
            $response['status'] = "success";
            $response["message"] = "$ad_type VERIFIED successfully!";
            echoResponse(200, $response);
        } else {
            $response['status'] = "error";
            $response["message"] = "Error verifying $ad_type! Please try again later";
            echoResponse(201, $response);
        }
    } else {
        $response['status'] = "error";
        $response["message"] = "NOT Found!";
        echoResponse(201, $response);
    }
});
