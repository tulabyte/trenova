<?php

//get order
$app->get('/getOrder', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $ord_id = $db->purify($app->request->get('id'));
    
    $order = $db->getOneRecord("SELECT order_id, order_time_created, order_total, order_status, user_fullname FROM user_order LEFT JOIN user ON order_user_id = user_id WHERE order_id = '$ord_id' ");

    $order_item = $db->getRecordset("SELECT course_title, item_qty, course_price FROM user_order_item LEFT JOIN course ON item_course_id = course_id  WHERE item_order_id = '$ord_id' ");

    if($order) {
        //found order, return success result
        $response['order_item'] = $order_item;
        $response['order'] = $order;
        $response['status'] = "success";
        $response["message"] = "Order Details Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Errorrr loading order!";
        echoResponse(201, $response);
    }
});

//orderList
$app->get('/getOrderList', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    
    $orders = $db->getRecordset("SELECT order_id, order_time_created, order_total, order_status, user_fullname FROM user_order LEFT JOIN user ON order_user_id = user_id");
    if($orders) {
        //users found
        $user_count = count($users);

        $response['orders'] = $orders;
        $response['status'] = "success";
        $response["message"] = "Order List Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No order  found!";
        echoResponse(201, $response);
    }
});




//create order
$app->post('/createOrder', function() use ($app) {
    
    $response = array();
    $db = new DbHandler();

    // extract body of request
    $r = json_decode($app->request->getBody());

    // extract values needed from body of request
    $ord_total = $db->purify($r->ord_total);
    $user_id = $db->purify($r->user_id);

    // get logged in user session details
    $session = $db->getSession(); 
    // $user_id = $session['fta_id'];

    // generate other necessary values
    $order_time_created = date("Y-m-d h:i:s");
    $order_status = $ord_total > 0? 'PENDING' : 'COMPLETED';

    // try a dummy select - makes no sense for now
    $dummy = $db->getOneRecord("SELECT 1 FROM user");

    if($dummy) {
        // run query to insert new order
        $table_name = "user_order";
        $column_names = ['order_user_id', 'order_total', 'order_time_created', 'order_status'];
        $values = [$user_id, $ord_total, $order_time_created, $order_status];

        $ord_id = $db->insertToTable($values, $column_names, $table_name);
        
        if($ord_id) {
            //order creation complete
            $response['order_id'] = $ord_id;
            $response['status'] = "success";
            $response["message"] = "Order created successfully!";
            echoResponse(200, $response);
        } else {
            $response['status'] = "error";
            $response["message"] = "Error creating order!";
            echoResponse(201, $response);
        }  
    }

});

// create order
$app->post('/createOrderItem', function() use ($app) {
    
    $response = array();
    $db = new DbHandler();

    // extract body of request
    $r = json_decode($app->request->getBody());
    $order_id = $db->purify($app->request->get('order_id'));
    $order_total = $db->purify($app->request->get('order_total'));

    // extract values needed from body of request
    $course_id = $db->purify($r->item->course_id);
    $item_qty = $db->purify($r->item->qty);
    $voucher_code = isset($r->item->voucher_code)? $db->purify($r->item->voucher_code) : '';

    // logged in user
    $session = $db->getSession();
    $user_id = $session['trenova_user']['user_id'];

    // try a dummy select - makes no sense for now
    $dummy = $db->getOneRecord("SELECT 1 FROM user");

    if($dummy) {
        // run query to insert new order item
        $table = "user_order_item";
        $columns = ['item_order_id', 'item_course_id', 'item_qty'];
        $values = [$order_id, $course_id, $item_qty];
        $item_id = $db->insertToTable($values, $columns, $table);
        
        if($item_id) {
            //order creation complete

            // if a voucher is included, mark it as used
            if(!empty($voucher_code)) {
                $table = "course_credit";
                $columns = ['cc_status'=>'USED', 'cc_user_id'=>$user_id, 'cc_used_date'=>date("Y-m-d"), 'cc_used_item_id'=>$item_id];
                $where = ['cc_code'=>$voucher_code];
                $result = $db->updateInTable($table, $columns, $where);
            }

            // if order_total is zero, activate this subscription
            if($order_total == 0) {
                $course = $db->getOneRecord("SELECT course_title FROM course WHERE course_id='$course_id'");

                $table = "subscription";
                $columns = ['sub_user_id', 'sub_course_id', 'sub_date_started', 'sub_months', 'sub_status', 'sub_order_id'];
                $values = [$user_id, $course_id, date("Y-m-d h:i:s"), $item_qty*4, 'ACTIVE', $order_id];
                $itemresult = $db->insertToTable($values, $columns, $table);

                // notify user of subscriptions
                $swiftmailer = new mySwiftMailer();
                $subject = "New Subscription Activated";
                $body = "<p>Hello,</p>
            <p>The following subscription have been activated for you:</p>
            <p>
            " . $course['course_title'] . " - ". $item_qty*4 ." months
            </p>
            <p>To access your courses, please login to the Trenova Mobile App and go to My Subscription in the menu.</p>
            <p>Thank you for using Trenova.</p>
            <p>NOTE: please DO NOT REPLY to this email.</p>
            <p><br><strong>Trenova App</strong></p>";
                $swiftmailer->sendmail('info@tulabyte.net', 'Trenova', [$session['trenova_user']['user_email']], $subject, $body);
            }

            $response['item_id'] = $item_id;
            $response['status'] = "success";
            $response["message"] = "Order item created successfully!";
            echoResponse(200, $response);
        } else {
            $response['status'] = "error";
            $response["message"] = "Error creating item!";
            echoResponse(201, $response);
        }  
    }

});

// delete order
$app->get('/deleteOrder', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $ord_id = $db->purify($app->request->get('id'));

    $table_name = 'user_order';
    $col_name = 'order_id';
    $result = $db->deleteFromTable($table_name, $col_name, $ord_id);

    if($result > 0) {
        //order deleted
        $response['status'] = "success";
        $response["message"] = "Order Deleted successfully!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error deleting order!";
        echoResponse(201, $response);
    }

});

//get user's order list
$app->get('/getUserOrderList', function() use ($app) {

    $response = array();

    $db = new DbHandler();
    $user_id = $db->purify($app->request->get('id'));
    
    $orders = $db->getRecordset("SELECT order_id, order_total, order_time_created, order_status, (SELECT COUNT(item_id) FROM user_order_item WHERE item_order_id = order_id) AS item_count FROM user_order WHERE order_user_id='$user_id' ORDER BY order_time_created DESC");
    if($orders) {
        //found order, return success result
        $response['orders'] = $orders;
        $response['status'] = "success";
        $response["message"] = "Orders Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading orders!";
        echoResponse(201, $response);
    }
});

//get full order details
$app->get('/getOrderDetails', function() use ($app) {
    
    $response = array();

    $db = new DbHandler();
    $order_id = $db->purify($app->request->get('id'));
    
    $order = $db->getOneRecord("SELECT order_id, order_total, order_time_created, order_status FROM user_order WHERE order_id='$order_id'");
    $order_items = $db->getRecordset("SELECT item_id, item_order_id, item_course_id, item_qty, course_title, course_image, course_price, cc_code FROM user_order_item
        LEFT JOIN course ON item_course_id = course_id 
        LEFT JOIN course_credit ON item_id = cc_used_item_id
        WHERE item_order_id = '$order_id' ");
    if($order && $order_items) {
        //found order, return success result
        $response['order'] = $order;
        $response['order_items'] = $order_items;
        $response['status'] = "success";
        $response["message"] = "Order Details Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading order details!";
        echoResponse(201, $response);
    }
});