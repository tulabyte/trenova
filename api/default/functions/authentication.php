<?php 
//Admin Authentication
$app->get('/session', function() {
    $db = new DbHandler();
    $session = $db->getSession();
    echoResponse(200, $session);
});

//sign up on the web app - for agents and resellers
$app->post('/adminSignUp', function() use ($app) {
    $response = array();
    $r = json_decode($app->request->getBody());

    verifyRequiredParams(['ad_email', 'ad_password', 'ad_name', 'ad_phone', 'ad_address', 'ad_type'],$r->admin);

    $db = new DbHandler();
    $ad_email = $db->purify($r->admin->ad_email); 
    $ad_password = $db->purify($r->admin->ad_password);     
    $ad_name = $db->purify($r->admin->ad_name);
    $ad_phone = $db->purify($r->admin->ad_phone);
    $ad_address = $db->purify($r->admin->ad_address);
    $ad_type = $db->purify($r->admin->ad_type);
    $ad_time_reg = date("Y-m-d H:i:s");

    $record_exists  = $db->getRecordset("SELECT * FROM admin WHERE ad_email = '$ad_email' OR ad_phone = '$ad_phone'");
    // var_dump($signUp_check); die;
    
    //checking if record exists
    if($record_exists) 
    {
        //Record is Present
        $response['status'] = "error";
        $response["message"] = "Somebody with the provided E-mail/Phone number already exists!";
        echoResponse(201, $response);
    } else {
        // generate random signup code
        $ad_signup_code = $db->randomNumericPassword();

        //Now lets create new record
        $table_name = "admin";
        $column_names = ['ad_email', 'ad_password', 'ad_name', 'ad_phone', 'ad_address', 'ad_type', 'ad_time_reg', 'ad_signup_code', 'ad_reg_status'];
        $values = [$ad_email, $ad_password, $ad_name, $ad_phone, $ad_address, $ad_type, $ad_time_reg, $ad_signup_code, 'PENDING'];
        $admin = $db->insertToTable($values, $column_names, $table_name);

        //if entry was created sucessfully
        if ($admin) 
        {
            // confirmation link
            $confirm_link = SITE_URL . '/#/access/confirm/' . $ad_signup_code;

            // Send  Email containing confirmation link to new admin
            $swiftmailer = new mySwiftMailer();
            $subject = "Confirm your new Trenova Account";
            $body = "<p>Hello $ad_name,</p>
    <p>
    <p>You just created a/an $ad_type account on Trenova using this email ($ad_email). To confirm that this is your email, please click on the following link:
    </p>
    <p><strong><a href='$confirm_link'>$confirm_link</a></strong></p>
    <p>Thank you for using Trenova.</p>
    <p>NOTE: please DO NOT REPLY to this email.</p>
    <p><br><strong>Trenova App</strong></p>";
            $swiftmailer->sendmail(FROM_EMAIL, SHORTNAME, [$ad_email], $subject, $body);

            // Prepare response
            $response['status'] = "success";
            $response['admin_id'] = $admin;
            $response["message"] = "Signup Successful! Please check your email for a confirmation link to complete your registration.";

            echoResponse(200, $response);
        } else {
            $response["status"] = "error";
            $response["message"] = "Error Signing Up, Please try again";
            echoResponse(201, $response);

        } //end of else
    }

});

// confirm admin signup using signup code
$app->get('/confirmAdminSignup', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $response['code_submitted'] = $code = $db->purify($app->request->get('code'));

    
    $admin = $db->getOneRecord("SELECT * FROM admin WHERE ad_signup_code='$code'");
    if($admin) {
        //found record, update confirmation status

        //update the new password in db
        $table_to_update = "admin";
        $columns_to_update = ['ad_email_confirmed'=>1];
        $where_clause = ['ad_id'=>$admin['ad_id']];
        $affected_rows = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

        if($affected_rows > 0) {
            //return response
            $response['status'] = "success";
            $response["message"] = "Registration confirmation successful! You can now access your account.";
            echoResponse(200, $response);
        } else {
            $response['status'] = "error";
            $response["message"] = "ERROR: Something went wrong while trying to confirm your registration. Please try again or contact Administrator!";
            echoResponse(201, $response);
        }
    } else {
        $response['status'] = "error";
        $response["message"] = "ERROR: The code you supplied is NOT associated with any ".SHORTNAME." account!";
        echoResponse(201, $response);
    }
});

// admin login
$app->post('/adminLogin', function() use ($app) {
    require_once 'passwordHash.php';
    $r = json_decode($app->request->getBody());
    //var_dump($r->admin); die;
    // check that required parameters are supplied
    verifyRequiredParams(array('email', 'password'),$r->admin);
    // open array for response & instantiate db handler
    $response = array();
    $db = new DbHandler();
    // extract parameters from received object & assign to vars
    $password = $db->purify($r->admin->password);
    $email = $db->purify($r->admin->email);
    // get the admin from db using supplied email
    $admin = $db->getOneRecord("SELECT * from admin WHERE ad_email='$email'");
    if ($admin) {
        // admin found in db
        //if(passwordHash::check_password($admin['ad_password'],$password)){
        // is password correct?
        if($admin['ad_password'] == $password){
            //password is correct
            // is admin verified?
            if($admin['ad_reg_status'] == 'VERIFIED') {
                // admin is verified
                // is email confirmed?
                if($admin['ad_email_confirmed']) {
                    // email is confirmed
                    // update last login
                    $table_to_update = "admin";
                    $columns_to_update = ['ad_last_login'=>date("Y-m-d H:i:s")];
                    $where_clause = ['ad_id'=>$admin['ad_id']];
                    $lastlogin = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

                    // compose rest of HTTP response
                    $response['status'] = "success";
                    $response['message'] = 'Logged in successfully. Redirecting...';
                    $response['trenova_user'] = $admin;
                    // start and set session - LOG ADMIN IN
                    if (!isset($_SESSION)) {
                        session_start();
                    }
                    $response['sid'] = session_id();
                    $_SESSION['trenova_user'] = $admin;
                    
                    //log action
                    $log_details = "Logged In: Successful";
                    $db->logAction($log_details);
                } else {
                    // email NOT confirmed!
                    $response['status'] = "error";
                    $response['message'] = "Your email has not yet been confirmed! Please login to ($email) and click on the Confirmation Link. After then, you will be able to login.";
                }

            } else {
                // admin NOT verified
                $response['status'] = "error";
                $response['message'] = 'Sorry. Your account is pending verification. You will be notified once you can access your account. Thank you.';
            }
        } else {
            // password NOT correct
            $response['status'] = "error";
            $response['message'] = 'Login failed! Incorrect admin credentials';
        }
    } else {
        // admin NOT found!
        $response['status'] = "error";
        $response['message'] = 'No such person is registered!';
    }
    // send the response
    echoResponse(200, $response);
});

/* Reset ADMIN Password */
$app->get('/adminResetPassword', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $response['email_sent'] = $email = $db->purify($app->request->get('email'));

    
    $admin = $db->getOneRecord("SELECT * FROM admin WHERE ad_email='$email'");
    if($admin) {
        //found admin, generate new password
        $response['pass_generated'] = $newPass = $db->randomPassword();

        //update the new password in db
        $table_to_update = "admin";
        $columns_to_update = ['ad_password'=>$newPass];
        $where_clause = ['ad_id'=>$admin['ad_id']];
        $affected_rows = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

        if($affected_rows > 0) {
            //send new password to admin
            $swiftmailer = new mySwiftMailer();
            $subject = "Login Details RESET on Trenova";
            $body = "<p>Dear ".$admin['ad_name'].",</p>
    <p>You requested a Password Reset on Trenova. Your request has been completed.</p>
    <p>Your new Password is <strong>".$newPass."</strong></p>
    <p>Thank you for using Trenova.</p>
    <p><br><strong>Trenova App</strong></p>";
            $swiftmailer->sendmail(FROM_EMAIL, SHORTNAME, [$admin['ad_email']], $subject, $body);

            //return response
            $response['status'] = "success";
            $response["message"] = "Password Reset successfully! Please check your email to retrieve the new password.";
            echoResponse(200, $response);
        } else {
            $response['status'] = "error";
            $response["message"] = "ERROR: Something went wrong while trying to reset your password. Please try again or contact Administrator!";
            echoResponse(201, $response);
        }
    } else {
        $response['status'] = "error";
        $response["message"] = "ERROR: The email you supplied is NOT associated with any ".SHORTNAME." account!";
        echoResponse(201, $response);
    }
});
/* Change ADMIN Password */
$app->post('/changeAdminPassword', function() use ($app) {
    $response = array();
    $r = json_decode($app->request->getBody());
    verifyRequiredParams(array('old', 'new'),$r->password);
    //require_once 'passwordHash.php';
    $db = new DbHandler();
    $old_pass = $db->purify($r->password->old);
    $new_pass = $db->purify($r->password->new);
    $session = $db->getSession();
    $admin_id = $session['trenova_user']['ad_id'];
    //check if old password is correct
    $isPasswordCorrect = $db->getOneRecord("SELECT 1 FROM admin WHERE ad_id='$admin_id' AND ad_password='$old_pass'");

    if($isPasswordCorrect){
        //$r->user->password = passwordHash::hash($password);
        //password is correct

        //update with new password
        $table_to_update = "admin";
        $columns_to_update = ['ad_password'=>$new_pass];
        $where_clause = ['ad_id'=>$admin_id];

        $result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

        if ($result > 0) {
            //log action
            $log_details = "Changed Password";
            $db->logAction($log_details);

            $response["status"] = "success";
            $response["message"] = "Password Updated Successfully!";
            echoResponse(200, $response);
        } else {
            $response["status"] = "error";
            $response["message"] = "Failed to update password!";
            echoResponse(201, $response);
        }
    }else{
        $response["status"] = "error";
        $response["message"] = "The old password you supplied is incorrect!!!";
        echoResponse(201, $response);
    }
});

$app->post('/userLogin', function() use ($app) {
    require_once 'passwordHash.php';
    $r = json_decode($app->request->getBody());
    verifyRequiredParams(array('email', 'password'),$r->user);
    $response = array();
    $db = new DbHandler();
    $password = $db->purify($r->user->password);
    $email = $db->purify($r->user->email);
    $user = $db->getOneRecord("SELECT * from user WHERE user_email='$email'");
    if ($user != NULL) {
        //if(passwordHash::check_password($user['user_password'],$password)){
        if($user['user_password'] == $password){

            //check if user is verified
            if($user['user_reg_status'] == 'PENDING') {
                // user is NOT verified
                $response['status'] = "error";
                $response['message'] = 'Please verify your account';
                $response['need_to_verify'] = true;
                $response['user'] = $user;
            } else {
                // user is verified
                $table_to_update = "user";
                $last_login_date = date("Y-m-d H:i:s");
                $columns_to_update = ['user_last_login'=> $last_login_date];
                $where_clause = ['user_id'=>$user['user_id']];
                $lastlogin = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

                // create a new user session
                if($db->createUserSession($user)) {
                    $response['status'] = "success";
                    $response['message'] = 'Logged in successfully. Redirecting...';
                    $response['sid'] = session_id();
                    $response['trv_name'] = $user['user_fullname'];
                    $response['trv_id'] = $user['user_id'];
                    $response['trv_email'] = $user['user_email'];
                    $response['trv_phone'] = $user['user_phone'];
                    $response['trv_date_created'] = $user['user_time_reg'];
                    $response['trv_type'] = $user['user_reg_type'];
                    $response['trv_last_login'] = $last_login_date;
                }
            }
        } else {
            $response['status'] = "error";
            $response['message'] = 'Login failed! Incorrect user credentials';
        }
    }else {
            $response['status'] = "error";
            $response['message'] = 'No such user is registered!';
        }
    echoResponse(200, $response);
});

$app->post('/facebookUserLogin', function() use ($app) {
    // require_once 'passwordHash.php';
    $r = json_decode($app->request->getBody());
    verifyRequiredParams(['email', 'first_name', 'last_name'],$r->user);
    $response = array();
    $db = new DbHandler();

    $email = $db->purify($r->user->email);
    $first_name = $db->purify($r->user->first_name);
    $last_name = $db->purify($r->user->last_name);
    $facebook_id = $db->purify($r->user->id);
    $user_device_token = $db->purify($r->user->user_device_token);
    $password = $db->randomPassword();
    $now = date("Y-m-d H:i:s");
    $today = date("Y-m-d");

    // check if user is in db
    $user = $db->getOneRecord("SELECT * from user WHERE user_email='$email'");

    if(!$user) {
        // user not in db, create user
        $table_name = "user";
        $column_names = ['user_fullname', 'user_email', 'user_password', 'user_time_reg', 'user_reg_type', 'user_facebook_id'];
        $values = [$first_name.' '.$last_name, $email, $password, $today, 'FACEBOOK', $facebook_id];
        $user_created = $db->insertToTable($values, $column_names, $table_name);

        if(!$user_created) {
            // couldn't create user
            $response['status'] = "error";
            $response['message'] = 'ERROR: Authentication Failed while trying to create User Account! Please try again later.';
            echoResponse(200, $response);
        } else {
            // get created user's details
            $user = $db->getOneRecord("SELECT * from user WHERE user_id='$user_created'");
        }
    }

    // after creating/identifying user, log user in/create session
    $table_to_update = "user";
    $columns_to_update = ['user_last_login'=> $now, 'user_last_auth'=>'FACEBOOK', 'user_device_token'=>$user_device_token];
    $where_clause = ['user_id'=>$user['user_id']];
    $lastlogin = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

    // create a new user session
    if($db->createUserSession($user, 'FACEBOOK')) {
        $response['status'] = "success";
        $response['sid'] = session_id();
        $response['trv_name'] = $user['user_fullname'];
        $response['trv_id'] = $user['user_id'];
        $response['trv_email'] = $user['user_email'];
        $response['trv_phone'] = $user['user_phone'];
        $response['trv_date_created'] = $user['user_time_reg'];
        $response['trv_type'] = $user['user_reg_type'];
        $response['trv_last_login'] = $now;
        $response['trv_avatar'] = $user['user_photo'];
        $response['trv_login_type'] = 'FACEBOOK';

        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response['message'] = 'ERROR: Something went wrong!';

        echoResponse(200, $response);
    }

});

$app->post('/googleUserLogin', function() use ($app) {
    // require_once 'passwordHash.php';
    $r = json_decode($app->request->getBody());
    verifyRequiredParams(['email', 'givenName', 'familyName'],$r->user);
    $response = array();
    $db = new DbHandler();

    $email = $db->purify($r->user->email);
    $first_name = $db->purify($r->user->givenName);
    $last_name = $db->purify($r->user->familyName);
    $google_id = $db->purify($r->user->userId);
    $user_device_token = $db->purify($r->user->user_device_token);
    $password = $db->randomPassword();
    $now = date("Y-m-d H:i:s");
    $today = date("Y-m-d");

    // check if user is in db
    $user = $db->getOneRecord("SELECT * from user WHERE user_email='$email'");

    if(!$user) {
        // user not in db, create user
        $table_name = "user";
        $column_names = ['user_fullname', 'user_email', 'user_password', 'user_time_reg', 'user_reg_type', 'user_google_id'];
        $values = [$first_name." ".$last_name, $email, $password, $today, 'GOOGLE', $google_id];
        $user_created = $db->insertToTable($values, $column_names, $table_name);

        if(!$user_created) {
            // couldn't create user
            $response['status'] = "error";
            $response['message'] = 'ERROR: Something went wrong while trying to create User Account! Please try again later.';
            echoResponse(200, $response);
        } else {
            // get created user's details
            $user = $db->getOneRecord("SELECT * from user WHERE user_id='$user_created'");
        }
    }

    // after creating/identifying user, log user in/create session
    $table_to_update = "user";
    $columns_to_update = ['user_last_login'=> $now, 'user_last_auth'=>'GOOGLE', 'user_device_token'=>$user_device_token];
    $where_clause = ['user_id'=>$user['user_id']];
    $lastlogin = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

    // create a new user session
    if($db->createUserSession($user, 'GOOGLE')) {
        $response['status'] = "success";
        $response['sid'] = session_id();
        $response['trv_name'] = $user['user_fullname'];
        $response['trv_id'] = $user['user_id'];
        $response['trv_email'] = $user['user_email'];
        $response['trv_phone'] = $user['user_phone'];
        $response['trv_date_created'] = $user['user_time_reg'];
        $response['trv_type'] = $user['user_reg_type'];
        $response['trv_last_login'] = $now;
        $response['trv_avatar'] = $user['user_photo'];
        $response['trv_login_type'] = 'GOOGLE';

        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response['message'] = 'ERROR: Something went wrong!';

        echoResponse(200, $response);
    }

});

$app->get('/userResetPassword', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $email = $db->purify($app->request->get('email'));

    $user = $db->getOneRecord("SELECT * FROM user WHERE user_email='$email'");
    if($user) {
        //found user, generate new password
        $newPass = $db->randomPassword();

        //update the new password in db
        $table_to_update = "user";
        $columns_to_update = ['user_password'=>$newPass];
        $where_clause = ['user_id'=>$user['user_id']];
        $affected_rows = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

        if($affected_rows > 0) {
            //send new password to user
            $swiftmailer = new mySwiftMailer();
            $subject = "Login Details RESET on Trenova";
            $body = "<p>Dear ".$user['user_fullname'].",</p>
    <p>You requested a Password Reset on Trenova. Your request has been completed.</p>
    <p>Your new Password is <strong>".$newPass."</strong></p>
    <p>Thank you for using Trenova.</p>
    <p><br><strong>Trenova App</strong></p>";
            $swiftmailer->sendmail(FROM_EMAIL, SHORTNAME, [$user['user_email']], $subject, $body);

            //return response
            $response['status'] = "success";
            $response["message"] = "Password Reset successfully! Please check your email to retrieve the new password.";
            echoResponse(200, $response);
        } else {
            $response['status'] = "error";
            $response["message"] = "ERROR: Something went wrong while trying to reset your password. Please try again or contact Administrator!";
            echoResponse(201, $response);
        }
    } else {
        $response['status'] = "error";
        $response["message"] = "ERROR: The email you supplied is NOT associated with any user account!";
        echoResponse(201, $response);
    }
});

$app->post('/changeUserPassword', function() use ($app) {
    $response = array();
    $r = json_decode($app->request->getBody());
    verifyRequiredParams(array('old', 'new'),$r->password);
    //require_once 'passwordHash.php';
    $db = new DbHandler();
    $old_pass = $db->purify($r->password->old);
    $new_pass = $db->purify($r->password->new);
    $session = $db->getSession();
    $user_id = $session['trenova_user']['user_id'];
    //check if old password is correct
    $isPasswordCorrect = $db->getOneRecord("SELECT 1 FROM user WHERE user_id='$user_id' AND user_password='$old_pass'");

    if($isPasswordCorrect){
        //$r->user->password = passwordHash::hash($password);
        //password is correct

        //update with new password
        $table_to_update = "user";
        $columns_to_update = ['user_password'=>$new_pass];
        $where_clause = ['user_id'=>$user_id];

        $result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

        if ($result > 0) {
            $response["status"] = "success";
            $response["message"] = "Password Updated Successfully!";
            echoResponse(200, $response);
        } else {
            $response["status"] = "error";
            $response["message"] = "Failed to update password!";
            echoResponse(201, $response);
        }
    }else{
        $response["status"] = "error";
        $response["message"] = "The old password you supplied is incorrect!!!";
        echoResponse(201, $response);
    }
});

$app->get('/logout', function() {
    $db = new DbHandler();

    //log action
    $log_details = "Logged Out";
    $db->logAction($log_details);

    $session = $db->destroySession();
    $response["status"] = "success";
    $response["message"] = "Logged out successfully";
    echoResponse(200, $response);
});

$app->get('/userLogout', function() {
    $db = new DbHandler();

    $session = $db->destroySession();
    $response["status"] = "success";
    $response["message"] = "Logged out successfully";
    echoResponse(200, $response);
});

//sign up with hashed token
$app->post('/signUp', function() use ($app) {
    $response = array();
    $r = json_decode($app->request->getBody());

    verifyRequiredParams(array('name', 'email', 'phone', 'password'),$r->user);

    $db = new DbHandler();

    $reseller_id = $db->purify($r->user->reseller);

    // get the reseller ID
    $reseller = $db->getOneRecord("SELECT ad_id FROM admin WHERE ad_reseller_code = '$reseller_id'");

    $user_fullname = $db->purify($r->user->name);
    $user_email = $db->purify($r->user->email);
    $user_phone = $db->purify($r->user->phone);
    $user_password = $db->purify($r->user->password);
    $user_reseller_id = $reseller ? $reseller['ad_id'] : '';
    $user_time_reg = date("Y-m-d H:i:s");

    $signUp_check  = $db->getOneRecord("SELECT * FROM user WHERE user_email = '$user_email' OR user_phone = '$user_phone'");
    // var_dump($signUp_check); die;

    //checking if record exists
    if($signUp_check)
    {
        //Record is Present
        $response['status'] = "error";
        $response["message"] = "User with provided E-mail/Phone Details Already Exists!";
        echoResponse(201, $response);
    } else {
        //Now lets create new user
        $table_name = "user";
        $column_names = ['user_fullname', 'user_email','user_password','user_phone', 'user_reseller_id', 'user_time_reg'];
        $values = [$user_fullname, $user_email, $user_password, $user_phone, $user_reseller_id, $user_time_reg];
        $signUp = $db->insertToTable($values, $column_names, $table_name);

        //generating random token
        //we retrieve user info from the database
        $user =  $db->getOneRecord("SELECT * FROM user WHERE user_email = '$user_email'");
        //proceeding with query
        if ($user) 
        {
            $token_table_name = "signup_token"; 
            $token_time_created = date("Y-m-d H:i:s");
            $token_code_full = $db->randomNumericPassword();
            $token_code_a = substr($token_code_full, 0, 6);
            $token_code_b = substr($token_code_full, 6, 6);
            $token_column_names = ['token_user_id','token_code_full', 'token_code_a', 'token_code_b', 'token_time_created'];
            $token_values = [$user['user_id'], $token_code_full, $token_code_a, $token_code_b, $token_time_created];
            $token_querry = $db->insertToTable($token_values, $token_column_names, $token_table_name);

            /*$sms = new smsHandler();
            //SMS The User token for confirmation.
            //SMS Token 'A' to the user
            $msg_sms = $token_code_a.' is your Signup Token for Trenova App';
            $sms->SendSMS($msg_sms, SHORTNAME, $user_phone, 0);*/

            /*// Send Email containing SMS details
            $swiftmailer = new mySwiftMailer();
            $subject = "SMS Token for your new account on Trenova";
            $body = "<p>Hello $user_fullname,</p>
    <p>
    <p>You just created an account on the Trenova App using email ($user_email) and phone number ($user_phone). To complete your registration, please enter the following SMS Token in the mobile app:</p>
    Token: $token_code_a<br>
    </p>
    <p>Thank you for using Trenova App.</p>
    <p>NOTE: please DO NOT REPLY to this email.</p>
    <p><br><strong>Trenova App</strong></p>";
            $swiftmailer->sendmail(FROM_EMAIL, SHORTNAME, [$user_email], $subject, $body);*/

            // Prepare response
            $response['status'] = "success";
            $response['token'] = $token_code_a;
            $response['user'] = $user;
            $response["message"] = "Signup Successful! An SMS has been sent to the number you provided. Please provide the Token received to verify your Account. ";

            echoResponse(200, $response);
        } else {
            $response["status"] = "error";
            $response["message"] = "Error Signing Up, Please try again";
            echoResponse(201, $response);

        } //end of else
    }

});

//verifySignupToken
$app->post('/verifySignupToken', function() use ($app) {
    $response = array();
    $r = json_decode($app->request->getBody());

    verifyRequiredParams(array('token_code', 'user_id'),$r->token);

    $db = new DbHandler();
    $token_code = sprintf('%06d', $db->purify($r->token->token_code));
    $response['token_received'] = $token_code;
    $user_id = $db->purify($r->token->user_id);
    $token_check = $db->getOneRecord("SELECT * FROM signup_token WHERE token_code_a = '$token_code' AND token_user_id = '$user_id' AND NOW() <= DATE_ADD(token_time_created, INTERVAL 24 HOUR) AND token_is_used IS NULL");

    $response['token_check_query'] = "SELECT * FROM signup_token WHERE token_code_a = '$token_code' AND token_user_id = '$user_id' AND NOW() <= DATE_ADD(token_time_created, INTERVAL 24 HOUR) AND token_is_used IS NULL";
    $response['token_check_result'] = $token_check;

    //checking if record exists
    if(!$token_check)
    {
        //Record is not present
        $response['status'] = "error";
        $response["message"] = "Invalid, Already Used or Expired Token!";
        echoResponse(201, $response);
    } else {
        // check token integrity
        $fulltoken = $token_code . $token_check['token_code_b'];
        if($fulltoken != $token_check['token_code_full']) {
            //failed simple hash test
            $response['status'] = "error";
            $response["message"] = "Bad Token!";
            echoResponse(201, $response);
        }

        //Token is valid and good
        //update token status
        $table_to_update = "signup_token";
        $columns_to_update = ['token_is_used'=>'1'];
        $where_clause = ['token_id'=>$token_check['token_id']];
        $token_updated = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

        if ($token_updated) 
        {
            $last_login_date = date("Y-m-d H:i:s");
            $table_to_update = "user";
            $columns_to_update = ['user_reg_status'=>'VERIFIED', 'user_last_login' => $last_login_date];
            $where_clause = ['user_id'=>$user_id];

            $user_updated = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

            // get user details
            $user = $db->getOneRecord("SELECT * FROM user WHERE user_id = '$user_id'");

            // create a new user session
            if($db->createUserSession($user)) {
                $response['status'] = "success";
                $response['sid'] = session_id();
                $response['trv_name'] = $user['user_fullname'];
                $response['trv_id'] = $user['user_id'];
                $response['trv_email'] = $user['user_email'];
                $response['trv_phone'] = $user['user_phone'];
                $response['trv_date_created'] = $user['user_time_reg'];
                $response['trv_type'] = $user['user_reg_type'];
                $response['trv_last_login'] = $last_login_date;
            }

            // Send Email to notify user
            $swiftmailer = new mySwiftMailer();
            $subject = "Registration Completed on Trenova";
            $body = "<p>Hello,</p>
    <p>Your registration on Trenova is now complete. You have been automatically logged into the app. You can log into the app any time from any device using the Email and Password you provided when registering.</p>
    <p>Thank you for using Trenova App.</p>
    <p>NOTE: please DO NOT REPLY to this email.</p>
    <p><br><strong>Trenova App</strong></p>";
            $swiftmailer->sendmail(FROM_EMAIL, SHORTNAME, [$user['user_email']], $subject, $body);

            $response["status"] = "success";
            $response["message"] = "Verification Successful";
            echoResponse(200, $response);
        } //end user update

    } //end else of token check

});

//generateSignUpToken by Kess
$app->get('/GenerateSignUpToken', function() use ($app) {
    $response = array();
    $db = new DbHandler();
    $token_user_id = $db->purify($app->request->get('user_id'));
    if($token_user_id){

    $user = $db->getOneRecord("SELECT * FROM user WHERE user_id = '$token_user_id'");
    $token_code_full = $db->randomNumericPassword();
    $token_code_a = substr($token_code_full, 0, 6);
    $token_code_b = substr($token_code_full, 6, 6);
    $token_table_name = "signup_token"; 
    $token_time_created = date("Y-m-d H:i:s");
    $token_get_id =  $db->getOneRecord("SELECT token_id FROM signup_token WHERE token_user_id = '$token_user_id'");
    $token_id = $token_get_id['token_id'];

    if($token_id) {
        //token generated
        $token_update_column = ['token_code_full'=>$token_code_full,'token_code_a'=>$token_code_a,'token_code_b'=>$token_code_b,'token_time_created'=>$token_time_created];
        $token_where = ['token_id'=>$token_id];
        $token_querry = $db->updateInTable($token_table_name, $token_update_column, $token_where);

        // set the usage status to null
        $token_status = $db->updateToNull($token_table_name, 'token_is_used', $token_where);

         /*$sms = new smsHandler();
        //SMS The User token for confirmation.
        //SMS Token 'A' to the user
        $msg_sms = $token_code_a.' is your Signup Token for Trenova App';
        $smsresult = $sms->SendSMS($msg_sms, SHORTNAME, $user['user_phone'], 0);
        die($smsresult);*/

        $response['token_user_id'] = $token_user_id;
        $response['token'] = $token_code_a;
        $response['status'] = "success";
        $response["message"] = "Token Successfully Generated. ";
        echoResponse(200, $response);
    } else {
        $response['token_user_id'] = $token_user_id;
        $response['status'] = "error";
        $response["message"] = "Error Generating Token, Please Try Again!";
        echoResponse(201, $response);
    }
} else {
        $response['status'] = "error";
        $response["message"] = "Error Please Login And Re-Generate Token!";
        echoResponse(201, $response);
    }
});

// update user device token
$app->post('/updateUserDeviceToken', function() use ($app) {
   
   $response = array();
   $db = new DbHandler();

   $r = json_decode($app->request->getBody());

   verifyRequiredParams(array('user_id', 'device_token'),$r->token);
   $user_id = $db->purify($r->token->user_id);
   $device_token = $db->purify($r->token->device_token);

   $table_to_update = "user";
    $columns_to_update = ['user_device_token'=>$device_token];
    $where_clause = ['user_id'=>$user_id];
    $result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

    if($result) {
        $response['status'] = "success";
        $response["message"] = "Token Successfully Updated. ";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error Updating Token";
        echoResponse(201, $response);
    }

});