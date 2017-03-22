<?php

/***Classes**
1. /createClass - POST(class)
2. /editClass - POST(class)
3. /deleteClass - GET(id)
4. /getClass - GET(id)
5. /getClassList - GET*/



//createClass

$app->post('/createClass', function() use ($app) {
    
    $response = array();

    $r = json_decode($app->request->getBody());
    verifyRequiredParams(['class_name', 'class_school_id'],$r->class);
    $db = new DbHandler();
    $class_name = $db->purify($r->class->class_name);
    $class_school_id = $db->purify($r->class->class_school_id);
    $isClassExists = $db->getOneRecord("SELECT 1 FROM class WHERE class_name = '$class_name'");
  
    if(!$isClassExists){
        $table_name = "class";
        $column_names = ['class_name', 'class_school_id'];
        $values = [$class_name, $class_school_id];

        $result = $db->insertToTable($values, $column_names, $table_name);

        if ($result != NULL) {
            $response["status"] = "success";
            $response["message"] = "Class created successfully";
            $response["class"] = $result;

            //log action
            $log_details = "Created New Class: $class_name (ID: $result)";
            $db->logAction($log_details);            

            echoResponse(200, $response);
        } else {
            $response["status"] = "error";
            $response["message"] = "Failed to create Class. Please try again";
            echoResponse(201, $response);
        }            
    }else{
        $response["status"] = "error";
        $response["message"] = "Class with the provided name already exists, please try another!";
        echoResponse(201, $response);
    }
});


//editClass

$app->post('/editClass', function() use ($app) {
    
    $response = array();

    $r = json_decode($app->request->getBody());
    verifyRequiredParams(['class_name', 'class_school_id', 'class_id'],$r->class);
    //require_once 'passwordHash.php';
    $db = new DbHandler();
    $class_id = $db->purify($r->class->class_id);
    $class_name = $db->purify($r->class->class_name);
    $class_school_id = $db->purify($r->class->class_school_id);
    $isClassExists = $db->getOneRecord("SELECT 1 FROM class WHERE class_id = '$class_id'");

    if($isClassExists){
        $table_to_update = "class";
        $columns_to_update = ['class_name'=>$class_name, 'class_school_id'=>$class_school_id];
        $where_clause = ['class_id'=>$class_id];

        $result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

        if ($result > 0) {
            //log action
            $log_details = "Edited Class: $class_name (ID: $class_id)";
            $db->logAction($log_details);

            $response["status"] = "success";
            $response["message"] = "Class updated successfully";
            echoResponse(200, $response);
        } else {
            $response["status"] = "error";
            $response["message"] = "Failed to update class. Please try again";
            echoResponse(201, $response);
        }            
    }else{
        $response["status"] = "error";
        $response["message"] = "ERROR: Class does not exist!";
        echoResponse(201, $response);
    }
});

//deleteClass

$app->get('/deleteClass', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $class_id = $db->purify($app->request->get('id'));

    //get class details
    $class = $db->getOneRecord("SELECT * FROM class WHERE class_id='$class_id'");

    $table_name = 'class';
    $col_name = 'class_id';
    $result = $db->deleteFromTable($table_name, $col_name, $class_id);

    if($result > 0) {
        //class deleted

        //log action
        $log_details = "Deleted Class: ".$class['class_name']." ($class_id)";
        $db->logAction($log_details);

        $response['status'] = "success";
        $response["message"] = "Class Deleted successfully!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error deleting class!";
        echoResponse(201, $response);
    }


});


// get class list
$app->get('/getClassList', function() use ($app) {
    $response = array();


    $db = new DbHandler();
    
    $classes = $db->getRecordset("SELECT class_id, class_name, class_school_id, sch_name FROM class LEFT JOIN school ON class_school_id = sch_id ORDER BY sch_name ASC, class_name ASC");
    if($classes) {
        //class found
        $class_count = count($classes);

        $response['classes'] = $classes;
        $response['status'] = "success";
        $response["message"] = "$class_count Classes Found!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No Class found!";
        echoResponse(201, $response);
    }
});


// get Class
$app->get('/getClass', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $class_id = $db->purify($app->request->get('id'));
    
    $class = $db->getOneRecord("SELECT * FROM class WHERE class_id='$class_id'");
    if($class) {
        //found class, return success result

        $response['class'] = $class;
        $response['status'] = "success";
        $response["message"] = "Class Details Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading class!";
        echoResponse(201, $response);
    }
});


?>