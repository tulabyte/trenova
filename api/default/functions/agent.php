 <?php

// get reseller
$app->get('/getAgentDetails', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $agent_id = $db->purify($app->request->get('id'));
    
    $agent = $db->getOneRecord("SELECT ad_email, ad_name, ad_phone, ad_address, ad_photo, ad_time_reg, ad_last_login, ad_reseller_code, ad_creator_id FROM admin WHERE ad_id='$agent_id'");

    $agent_cr_pur = $db->getOneRecord("SELECT COUNT(*) as acp_credit  FROM course_credit WHERE cc_agent_id = '$agent_id' ");

    $agent_cr_usg = $db->getOneRecord("SELECT COUNT(*) as credit  FROM course_credit WHERE cc_agent_id = '$agent_id' AND cc_status = 'UNUSED' ");
    

    $agent_pur = $db->getRecordset("SELECT course_title, ao_qty, ao_amount, ao_status, ao_date FROM agent_order LEFT JOIN course ON ao_course_id = course_id WHERE ao_agent_id = '$agent_id' AND ao_status = 'COMPLETED' ORDER BY ao_date DESC LIMIT 10");

    $agent_usg = $db->getRecordset("SELECT cc_date_purchased, user_fullname, course_title, cc_code FROM course_credit LEFT JOIN user ON cc_user_id = user_id LEFT JOIN course ON cc_course_id = course_id WHERE cc_agent_id = '$agent_id' AND cc_status = 'USED' ORDER BY cc_date_purchased DESC LIMIT 10");

    if($agent) {
        //found agent, return success result

        $response['agent'] = $agent;
        $response['agent_pur'] = $agent_pur;
        $response['agent_usg'] = $agent_usg;
        $response['agent_cr_pur'] = $agent_cr_pur;
        $response['agent_cr_usg'] = $agent_cr_usg;
        $response['status'] = "success";
        $response["message"] = "Agent Details Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading agent!";
        echoResponse(201, $response);
    }
});

$app->get('/getAgentDash', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $session = $db->getSession();

    // get id of currently logged in agent
    $agent_id = $session['trenova_user']['ad_id'];
    
    $agent = $db->getOneRecord("SELECT ad_email, ad_name, ad_phone, ad_address, ad_photo, ad_time_reg, ad_last_login, ad_reseller_code, ad_creator_id FROM admin WHERE ad_id='$agent_id'");

    $agent_cr = $db->getOneRecord("SELECT COUNT(*) as credit  FROM course_credit WHERE cc_agent_id = '$agent_id' AND cc_status = 'UNUSED' ");
    

    $agent_pur = $db->getRecordset("SELECT course_title, ao_qty, ao_amount, ao_status, ao_date FROM agent_order LEFT JOIN course ON ao_course_id = course_id WHERE ao_agent_id = '$agent_id' AND ao_status = 'COMPLETED' ORDER BY ao_date DESC LIMIT 10");

    $agent_usg = $db->getRecordset("SELECT cc_date_purchased, user_fullname, course_title, cc_code FROM course_credit LEFT JOIN user ON cc_user_id = user_id LEFT JOIN course ON cc_course_id = course_id WHERE cc_agent_id = '$agent_id' AND cc_status = 'USED' ORDER BY cc_date_purchased DESC LIMIT 10");

    if($agent) {
        //found agent, return success result

        $response['agent'] = $agent;
        $response['agent_pur'] = $agent_pur;
        $response['agent_usg'] = $agent_usg;
        $response['agent_cr'] = $agent_cr;
        $response['status'] = "success";
        $response["message"] = "Agent Details Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading agent!";
        echoResponse(201, $response);
    }
});

//getAgentUnusedPurchase
$app->get('/getAgentUnusedPurchase', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $session = $db->getSession();

    // get id of currently logged in agent
    $agent_id = $session['trenova_user']['ad_id'];

    $agent_usg = $db->getRecordset("SELECT course_title, cc_code, cc_date_purchased FROM course_credit LEFT JOIN course ON cc_course_id = course_id WHERE cc_agent_id = '$agent_id' AND cc_status = 'UNUSED' ORDER BY cc_date_purchased DESC");
    if($agent_id) {
        //found agent, return success result

        $response['agent_usg'] = $agent_usg;
        $response['status'] = "success";
        $response["message"] = "Course And Code Details Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading purchases!";
        echoResponse(201, $response);
    }
});


//getAgentPurchase

$app->get('/getAgentPurchase', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $session = $db->getSession();

    // get id of currently logged in agent
    $agent_id = $session['trenova_user']['ad_id'];
    
    $agent_pur = $db->getRecordset("SELECT cc_id, cc_date_purchased, user_fullname, course_title, cc_code FROM course_credit LEFT JOIN user ON cc_user_id = user_id LEFT JOIN course ON cc_course_id = course_id WHERE cc_agent_id = '$agent_id' AND cc_status = 'USED' ORDER BY cc_date_purchased DESC");
    if($agent_id) {
        //found agent, return success result
        $response['agent_pur'] = $agent_pur;
        $response['status'] = "success";
        $response["message"] = "Purchase Details Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading purchases!";
        echoResponse(201, $response);
    }
});

$app->get('/getAgentOrder', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $session = $db->getSession();

    // get id of currently logged in agent
    $agent_id = $session['trenova_user']['ad_id'];
    
    $agent_pur = $db->getRecordset("SELECT course_title, ao_qty, ao_amount, ao_status, ao_date FROM agent_order LEFT JOIN course ON ao_course_id = course_id WHERE ao_agent_id = '$agent_id' AND ao_status = 'COMPLETED' ORDER BY ao_date DESC");

    if($agent_pur) {
        //found agent, return success result

        $response['agent_pur'] = $agent_pur;
        $response['status'] = "success";
        $response["message"] = "Order List Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading order!";
        echoResponse(201, $response);
    }
});

//getBuyDetails
$app->get('/getBuyDetails', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $course_id = $db->purify($app->request->get('id'));
    
    $agent_cr_pur = $db->getOneRecord("SELECT course_id, course_title, course_price FROM course WHERE course_id = '$course_id' ");

    $agent_pur = $db->getRecordset("SELECT less_number, less_title FROM course_lesson WHERE less_course_id ='$course_id' ");

    $agent_usg = $db->getRecordset("SELECT df_min, df_discounts FROM discount_formula");

    if($agent_cr_pur) {
        //found agent, return success result

        $response['agent_cr_pur'] = $agent_cr_pur;
        $response['agent_pur'] = $agent_pur;
        $response['agent_usg'] = $agent_usg;
        $response['status'] = "success";
        $response["message"] = "Purchase Details Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading course!";
        echoResponse(201, $response);
    }
});


//buyAgentCourse
$app->post('/buyAgentCourse', function() use ($app) {
    
    $response = array();
    $db = new DbHandler();

    // extract body of request
    $r = json_decode($app->request->getBody());
    verifyRequiredParams(['course_id', 'order_quantity', 'discount', 'order_total'],$r->agent);

    // extract values needed from body of request
    $ao_course_id = $db->purify($r->agent->course_id);
    $ao_qty = $db->purify($r->agent->order_quantity);
    $ao_amount = $db->purify($r->agent->order_total);
    $ao_discount = $db->purify($r->agent->discount);
    $ao_status = "COMPLETED";
    $ao_date =  date("Y-m-d");
    // get logged in user session details
    $session = $db->getSession(); 
    $ao_agent_id = $session['trenova_user']['ad_id'];

    // try a dummy select - makes no sense for now
    $dummy = $db->getOneRecord("SELECT 1 FROM agent_order");

    if($dummy) {
        // run query to insert new order
        $table_name = "agent_order";
        $column_names = ['ao_agent_id', 'ao_course_id', 'ao_qty', 'ao_amount','ao_status', 'ao_date', 'ao_discount'];
        $values = [$ao_agent_id, $ao_course_id, $ao_qty, $ao_amount, $ao_status, $ao_date, $ao_discount];

        $agentorder = $db->insertToTable($values, $column_names, $table_name);
        
        if($agentorder) {
            //order creation complete
            $response['agent_ord'] = $agentorder;
            $response['status'] = "success";
            $response["message"] = "Order created successfully!";
            echoResponse(200, $response);

            //generate course credit and courses
         for ($i=0; $i < $ao_qty ; $i++) {       
            $cc_code = $db->randomPassword();
            if ($cc_code) {
                $table_name = "course_credit";
                $column_names = ['cc_agent_id', 'cc_course_id', 'cc_code', 'cc_date_purchased', 'cc_order_id'];
                $values = [$ao_agent_id, $ao_course_id, $cc_code, $ao_date, $agentorder];
                $course_credit = $db->insertToTable($values, $column_names, $table_name); 
                                           
            } else{
                $response['status'] = "error";
                $response["message"] = "Error creating courses!";
                echoResponse(201, $response);
            }
        }

        } else {
            $response['status'] = "error";
            $response["message"] = "Error creating order!";
            echoResponse(201, $response);
        }  
    }

});


$app->get('/getAgentCourse', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $agent_pur = $db->getRecordset("SELECT course_id, course_title, class_name, course_price, sb_title, sch_name, course_term FROM course LEFT JOIN class ON course_class_id = class_id LEFT JOIN subject ON course_subject_id = sb_id LEFT JOIN school ON class_school_id = sch_id ORDER BY course_time_created DESC");

    if($agent_pur) {
        //found agent, return success result

        $response['agent_pur'] = $agent_pur;
        $response['status'] = "success";
        $response["message"] = "Order List Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading order!";
        echoResponse(201, $response);
    }
});
