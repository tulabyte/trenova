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

    // get logged in user session details
    $session = $db->getSession(); 
    $user_id = $session['fta_id'];

    // generate other necessary values
    $ord_date_init = date("Y-m-d h:i:s");
    $ord_status = 'PENDING';

    // try a dummy select - makes no sense for now
    $dummy = $db->getOneRecord("SELECT 1 FROM user");

    if($dummy) {
        // run query to insert new order
        $table_name = "user_order";
        $column_names = ['order_user_id', 'order_total', 'order_date_init', 'order_status'];
        $values = [$user_id, $ord_total, $ord_date_init, $ord_status];

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

    // extract values needed from body of request
    $course_id = $db->purify($r->item->course_id);
    $item_qty = $db->purify($r->item->qty);

    // try a dummy select - makes no sense for now
    $dummy = $db->getOneRecord("SELECT 1 FROM user");

    if($dummy) {
        // run query to insert new order item
        $table_name = "order_item";
        $column_names = ['item_order_id', 'item_course_id', 'item_qty'];
        $values = [$order_id, $course_id, $item_qty];

        $item_id = $db->insertToTable($values, $column_names, $table_name);
        
        if($item_id) {
            //order creation complete
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