<?php
//BC messages and Feedbacks were written here

$app->post('/createUser', function() use ($app) {
    
    $response = array();

    $r = json_decode($app->request->getBody());
    verifyRequiredParams(array('user_firstname','user_surname','user_email','user_password'),$r->user);
    //require_once 'passwordHash.php';
    $db = new DbHandler();
    $user_firstname = $db->purify($r->user->user_firstname);
    $user_surname = $db->purify($r->user->user_surname);
    $user_email = $db->purify($r->user->user_email);
    $user_reg_type = isset($r->user->user_reg_type) ? $db->purify($r->user->user_reg_type) : 'DEFAULT';
    $user_password = $db->purify($r->user->user_password);
    //$r->user->create_date = date("Y-m-d");
    $user_date_created = date("Y-m-d");

    $isUserExists = $db->getOneRecord("SELECT 1 FROM user WHERE user_email='$user_email'");
    if(!$isUserExists){
        //$r->user->password = passwordHash::hash($password);
        $table_name = "user";
        $column_names = ['user_firstname','user_surname','user_email','user_reg_type','user_password','user_date_created'];
        $values = [$user_firstname, $user_surname, $user_email, $user_reg_type, $user_password, $user_date_created];

        $result = $db->insertToTable($values, $column_names, $table_name);

        if ($result != NULL) {
            $response["status"] = "success";
            $response["message"] = "User account created successfully";
            $response["user_id"] = $user_id = $result;

            //send email notification to new user
            $swiftmailer = new mySwiftMailer();
            $subject = "Your new User Account on FITC Training";
            $body = "<p>Dear $user_firstname,</p>
    <p>A user account has been created for you on FITC Training. You can login using the following details:</p>
    <p>
    URL: http://fta.fitc-ng.com<br>
    Email: $user_email<br>
    Password: $user_password
    </p>
    <p>You are advised to change your password to something more personal once you login.</p>
    <p>Thank you for using FITC Training.</p>
    <p>NOTE: please DO NOT REPLY to this email.</p>
    <p><br><strong>FITC Training App</strong></p>";
            $swiftmailer->sendmail('info@fitc-ng.com', 'FITC Training', [$user_email], $subject, $body);

            //log action
            $log_details = "Created User: $user_firstname $user_surname (ID: $user_id)";
            $db->logAction($log_details);            

            echoResponse(200, $response);
        } else {
            $response["status"] = "error";
            $response["message"] = "Failed to create user. Please try again";
            echoResponse(201, $response);
        }            
    }else{
        $response["status"] = "error";
        //$response['message'] = $r->user;
        $response["message"] = "User with the provided email already exists, please try another!";
        echoResponse(201, $response);
    }
});

// edit user
$app->post('/editUser', function() use ($app) {
    
    $response = array();

    $r = json_decode($app->request->getBody());
    verifyRequiredParams(array('user_fullname','user_email','user_password'),$r->user);
    //require_once 'passwordHash.php';
    $db = new DbHandler();
    $user_id = $db->purify($r->user->user_id);
    $user_fullname = $db->purify($r->user->user_fullname);
    $user_email = $db->purify($r->user->user_email);
    $user_password = $db->purify($r->user->user_password);

    $isUserExists = $db->getOneRecord("SELECT 1 FROM user WHERE user_id='$user_id'");
    if($isUserExists){
        //$r->user->password = passwordHash::hash($password);
        $table_to_update = "user";
        $columns_to_update = ['user_fullname'=>$user_fullname,'user_email'=>$user_email,'user_password'=>$user_password];
        $where_clause = ['user_id'=>$user_id];

        $result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

        if ($result > 0) {
            //log action
            $log_details = "Edited User: $user_firstname $user_surname (ID: $user_id)";
            $db->logAction($log_details);

            $response["status"] = "success";
            $response["message"] = "User updated successfully";
            echoResponse(200, $response);
        } else {
            $response["status"] = "error";
            $response["message"] = "Failed to update user. Please try again";
            echoResponse(201, $response);
        }            
    }else{
        $response["status"] = "error";
        //$response['message'] = $r->user;
        $response["message"] = "ERROR: User does not exist!";
        echoResponse(201, $response);
    }
});

// update user profile
$app->post('/updateUserProfile', function() use ($app) {
    
    $response = array();

    $r = json_decode($app->request->getBody());
    verifyRequiredParams(['user_id','user_fullname','user_phone'],$r->user);
    //require_once 'passwordHash.php';
    $db = new DbHandler();
    $user_id = $db->purify($r->user->user_id);
    $user_fullname = $db->purify($r->user->user_fullname);
    $user_phone = $db->purify($r->user->user_phone);
    
    $isUserExists = $db->getOneRecord("SELECT 1 FROM user WHERE user_id='$user_id'");
    if($isUserExists){
        //$r->user->password = passwordHash::hash($password);
        $table_to_update = "user";
        $columns_to_update = ['user_fullname'=>$user_fullname,'user_phone'=>$user_phone];
        $where_clause = ['user_id'=>$user_id];

        $result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

        if ($result > 0) {
            $response["status"] = "success";
            $response["message"] = "User updated successfully";
            echoResponse(200, $response);
        } else {
            $response["status"] = "error";
            $response["message"] = "Failed to update user. Please try again";
            echoResponse(201, $response);
        }            
    }else{
        $response["status"] = "error";
        //$response['message'] = $r->user;
        $response["message"] = "ERROR: User does not exist!";
        echoResponse(201, $response);
    }
});

// get user
$app->get('/getUser', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $user_id = $db->purify($app->request->get('id'));
    
    $user = $db->getOneRecord("SELECT * FROM user WHERE user_id='$user_id'");

    $user_sub = $db->getRecordset("SELECT sub_status, course_title, sub_date_started, DATE_ADD(sub_date_started, INTERVAL sub_months MONTH) as expiring  FROM subscription LEFT JOIN course ON sub_course_id = course_id WHERE sub_user_id = '$user_id' AND sub_status = 'ACTIVE'");

    $user_esub = $db->getRecordset("SELECT sub_status, course_title, sub_date_started, DATE_ADD(sub_date_started, INTERVAL sub_months MONTH) as expiring  FROM subscription LEFT JOIN course ON sub_course_id = course_id WHERE sub_user_id = '$user_id' AND sub_status = 'EXPIRED'");

    $user_pmt = $db->getRecordset("SELECT pay_method, pay_amount, pay_time_initiated, pay_time_completed, pay_status  FROM user_payment WHERE pay_user_id = '$user_id' ");

    $user_pymt = $db->getRecordset("SELECT SUM(pay_amount)  as pay_amount  FROM user_payment WHERE pay_user_id = '$user_id' ");


    $user_ord =  $db->getRecordset("SELECT order_id, order_time_created, order_total, order_status  FROM user_order WHERE order_user_id = '$user_id' ");

    if($user) {
        //found user, return success result
        $response['user'] = $user;
        $response['user_sub'] = $user_sub;
        $response['user_esub'] = $user_esub;
        $response['user_pmt'] = $user_pmt;
        $response['user_ord'] = $user_ord;
        $response['status'] = "success";
        $response["message"] = "User Details Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading user!";
        echoResponse(201, $response);
    }
});


// get user list
$app->get('/getUserList', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    
    $users = $db->getRecordset("SELECT *, (SELECT COUNT(*) FROM subscription WHERE sub_user_id = user_id) AS sub_count FROM user");
    if($users) {
        //users found
        $user_count = count($users);

        $response['users'] = $users;
        $response['status'] = "success";
        $response["message"] = "$user_count Users Found!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No user found!";
        echoResponse(201, $response);
    }
});

$app->get('/deleteUser', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $user_id = $db->purify($app->request->get('id'));
    
    $table_name = 'user';
    $col_name = 'user_id';
    $result = $db->deleteFromTable($table_name, $col_name, $user_id);

    if($result > 0) {
        //user deleted
        $response['status'] = "success";
        $response["message"] = "User Deleted successfully!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error deleting user!";
        echoResponse(201, $response);
    }

    // $response["user_id"] = $user_id;
    // echoResponse(200, $response);
});

// Add course to favourites
$app->get('/addToFavourites', function() use ($app) {
    
    $response = array();
    // collect values needed
    $db = new DbHandler();
    $session = $db->getSession();
    $course_id = $db->purify($app->request->get('course_id'));
    $user_id = $session['trenova_user']['user_id'];
    $response['user_id'] = $user_id;
    // check if it's already in favourites
    $fav = $db->getOneRecord("SELECT * FROM favourite WHERE fav_user_id = '$user_id' AND fav_course_id = '$course_id' ");

    if($fav) {
        $response['status'] = "success";
        $response["message"] = "Already Added to Favourites";
        echoResponse(200, $response);
    } else {
       // run query to insert new fav
        $table_name = "favourite";
        $column_names = ['fav_user_id', 'fav_course_id'];
        $values = [$user_id, $course_id];
        $result = $db->insertToTable($values, $column_names, $table_name); 

        if($result) {
            //fav added
            $response['status'] = "success";
            $response["message"] = "Added to Favourites";
            echoResponse(200, $response);
        } else {
            $response['status'] = "error";
            $response["message"] = "Error adding to favourites!";
            echoResponse(201, $response);
        }
    }

    
});


// get Feedback list
$app->get('/getFeedbackList', function() use ($app) {
    $response = array();

    $db = new DbHandler();    
    $feedback = $db->getRecordset("SELECT fd_id, fd_topic, fd_date, fd_status, user_fullname FROM feedback LEFT JOIN user ON fd_user_id = user_id");

    $broadcast = $db->getRecordset("SELECT bc_id, bc_topic, bc_date, ad_name FROM broadcast LEFT JOIN admin ON bc_ad_id = ad_id");
    
    if($feedback || $broadcast) {
        //feedback found\
        $broadcast_count = count($broadcast);
        $feedback_count = count($feedback);

        $response['feedback'] = $feedback;
        $response['broadcast'] = $broadcast;
        $response['status'] = "success";
        $response["message"] = "$feedback_count feedback Found!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No feedback found!";
        echoResponse(201, $response);
    }
});

// get feedback details
$app->get('/getFeedbackDetails', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $fd_id = $db->purify($app->request->get('id'));

    
    $feedback = $db->getOneRecord("SELECT fd_id, fd_message, fd_topic, fd_date, fd_status, user_fullname FROM feedback LEFT JOIN user ON fd_user_id = user_id WHERE fd_id = '$fd_id'");

    $broadcast = $db->getOneRecord("SELECT bc_id, bc_message, bc_topic, bc_date, ad_name FROM broadcast LEFT JOIN admin ON bc_ad_id = ad_id WHERE bc_id = '$fd_id'");


    if($feedback || $broadcast) {
        //found feedback, return success result
        $response['feedback'] = $feedback;
        $response['broadcast'] = $broadcast;
        $response['status'] = "success";
        $response["message"] = "Feedback Message Loaded!";
        echoResponse(200, $response);
     
        // update Pending to checked
        $fd_status ="SEEN" ;
        $table_to_update = "feedback";
        $columns_to_update = ['fd_status'=>$fd_status];
        $where_clause = ['fd_id'=>$fd_id];

        $result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);
    //end of update

    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading feedback!";
        echoResponse(201, $response);
    }
});

$app->post('/createBroadCast', function() use ($app) {
    
    $db = new DbHandler();
    $session = $db->getSession();

    // get id of currently logged in admin
    $ad_id = $session['trenova_user']['ad_id'];
    
    $response = array();

    $r = json_decode($app->request->getBody());
    verifyRequiredParams(['bc_topic','bc_message'],$r->broadcast);
    $db = new DbHandler();
    $bc_topic = $db->purify($r->broadcast->bc_topic);
    $bc_message = $db->purify($r->broadcast->bc_message);
    $bc_date = date('Y-m-d');

    $isAdminExists = $db->getOneRecord("SELECT 1 FROM admin WHERE ad_id='$ad_id'");
    if($isAdminExists){
        $table_name = "broadcast";
        $column_names = ['bc_topic', 'bc_message', 'bc_date', 'bc_ad_id'];
        $values = [$bc_topic,$bc_message, $bc_date, $ad_id];

        $result = $db->insertToTable($values, $column_names, $table_name);

        if ($result > 0) {
            //log action
            $log_details = "Created Broadcast: $bc_topic  (ID: $ad_id)";
            $db->logAction($log_details);

            $response["status"] = "success";
            $response["message"] = "Broadcast created successfully";
            echoResponse(200, $response);

            //send broadcast to all users
            $users = $db->getRecordset("SELECT user_email, user_fullname FROM user ");
            foreach ($users as $user) {
                $swiftmailer = new mySwiftMailer();
                $subject = $bc_topic;
                $user_email = $user[user_email];
                $body = "<p>Dear $user[user_fullname],</p>
                <p>$bc_message</p>
                <p>Thank you for using Lenova Training.</p>
                <p>NOTE: please DO NOT REPLY to this email.</p>
                <p><br><strong>Lenova Training App</strong></p>";
                $swiftmailer->sendmail('info@tulabyte.net', 'Lenova Training', [$user_email], $subject, $body);
            }


        } else {
            $response["status"] = "error";
            $response["message"] = "Failed to craete Broadcast. Please try again";
            echoResponse(201, $response);
        }            
    }else{
        $response["status"] = "error";
        //$response['message'] = $r->module;
        $response["message"] = "ERROR: Admin does not exist!";
        echoResponse(201, $response);
    }
});


//send user a message
$app->post('/sendUserMesssage', function() use ($app) {
    
    $db = new DbHandler();
    $session = $db->getSession();

    // get id of currently logged in admin
    $ad_id = $session['trenova_user']['ad_id'];
    
    $response = array();
    $r = json_decode($app->request->getBody());
    verifyRequiredParams(['subject','body'],$r->message);
    $db = new DbHandler();
    $subject = $db->purify($r->message->subject);
    $body = $db->purify($r->message->body);
    $email = $db->purify($r->message->email);
    $attach = $db->purify($r->message->attach);
    $date = date('Y-m-d H:i:s');
    $user_id = $db->purify($r->message->user_id);
    $user_fullname = $db->purify($r->message->user_fullname);

    $isAdminExists = $db->getOneRecord("SELECT 1 FROM admin WHERE ad_id='$ad_id'");
    if($isAdminExists){
        $table_name = "message";
        $column_names = ['msg_sender_id', 'msg_receiver_id', 'msg_time_sent', 'msg_subject', 'msg_body', 'msg_attachment'];
        $values = [$ad_id,$user_id, $date, $subject,$body,$attach];

        $result = $db->insertToTable($values, $column_names, $table_name);

        if ($result > 0) {
            //log action
            $log_details = "Sent Message: $subject to  $email (ID: $ad_id)";
            $db->logAction($log_details);

            $response["status"] = "success";
            $response["message"] = "Broadcast created successfully";
            echoResponse(200, $response);

/*            //send email to user
                $swiftmailer = new mySwiftMailer();
                $email_body = "<p>Dear $user_fullname,</p>
                <p>$body</p>
                <p>Thank you for using Lenova Training.</p>
                <p>NOTE: please DO NOT REPLY to this email.</p>
                <p><br><strong>Lenova Training App</strong></p>";
                $swiftmailer->sendmail('info@tulabyte.net', 'Lenova Training', [$user_email], $subject, $e_body);*/

        } else {
            $response["status"] = "error";
            $response["message"] = "Failed to send Message. Please try again";
            echoResponse(201, $response);
        }            
    }else{
        $response["status"] = "error";
        //$response['message'] = $r->module;
        $response["message"] = "ERROR: You are not Authorize to send this User a Message!";
        echoResponse(201, $response);
    }
});
