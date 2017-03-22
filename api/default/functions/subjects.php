<?php

/*
**Subjects**
1. /createSubject - POST(subject)
2. /editSubject - POST(subject)
3. /deleteSubject - GET(id)
4. /getSubject - GET(id)
5. /getSubjectList - GET
*/

//createSubject

$app->post('/createSubject', function() use ($app) {
    
    $response = array();

    $r = json_decode($app->request->getBody());
    verifyRequiredParams(['sb_title','sb_description'],$r->subject);
    $db = new DbHandler();
    $sb_description = $db->purify($r->subject->sb_description);
    $sb_title = $db->purify($r->subject->sb_title);    
    $isSubjectExists = $db->getOneRecord("SELECT 1 FROM subject WHERE sb_title = '$sb_title'");
  
    if(!$isSubjectExists){
        $table_name = "subject";
        $column_names = ['sb_title', 'sb_description'];
        $values = [$sb_title, $sb_description];

        $result = $db->insertToTable($values, $column_names, $table_name);

        if ($result != NULL) {
            $response["status"] = "success";
            $response["message"] = "Subject created successfully";
            $response["subject"] = $result;

            //log action
            $log_details = "Subject New Subject: $sb_title (ID: $result)";
            $db->logAction($log_details);            

            echoResponse(200, $response);
        } else {
            $response["status"] = "error";
            $response["message"] = "Failed to create Subject. Please try again";
            echoResponse(201, $response);
        }            
    }else{
        $response["status"] = "error";
        $response["message"] = "Subject with the provided Title already exists, please try another!";
        echoResponse(201, $response);
    }
});


//editSubject

$app->post('/editSubject', function() use ($app) {
    
    $response = array();

    $r = json_decode($app->request->getBody());
    verifyRequiredParams(['sb_title','sb_id', 'sb_description'],$r->subject);
    //require_once 'passwordHash.php';
    $db = new DbHandler();
    $sb_id = $db->purify($r->subject->sb_id);
    $sb_title = $db->purify($r->subject->sb_title);
    $sb_description = $db->purify($r->subject->sb_description);
    $isSubjectExists = $db->getOneRecord("SELECT 1 FROM subject WHERE sb_id = '$sb_id'");

    if($isSubjectExists){
        $table_to_update = "subject";
        $columns_to_update = ['sb_title'=>$sb_title, 'sb_description'=>$sb_description];
        $where_clause = ['sb_id'=>$sb_id];

        $result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

        if ($result > 0) {
            //log action
            $log_details = "Edited Subject: $sb_title (ID: $sb_id)";
            $db->logAction($log_details);

            $response["status"] = "success";
            $response["message"] = "Subject updated successfully";
            echoResponse(200, $response);
        } else {
            $response["status"] = "error";
            $response["message"] = "Failed to update subject. Please try again";
            echoResponse(201, $response);
        }            
    }else{
        $response["status"] = "error";
        $response["message"] = "ERROR: Subject does not exist!";
        echoResponse(201, $response);
    }
});

//deleteSubject

$app->get('/deleteSubject', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $sb_id = $db->purify($app->request->get('id'));

    //get subject details
    $subject = $db->getOneRecord("SELECT * FROM subject WHERE sb_id='$sb_id'");

    $table_name = 'subject';
    $col_name = 'sb_id';
    $result = $db->deleteFromTable($table_name, $col_name, $sb_id);

    if($result > 0) {
        //subject deleted

        //log action
        $log_details = "Deleted Subject: ".$subject['sb_title']." ($sb_id)";
        $db->logAction($log_details);

        $response['status'] = "success";
        $response["message"] = "Subject Deleted successfully!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error deleting subject!";
        echoResponse(201, $response);
    }


});


// getSubjectList
$app->get('/getSubjectList', function() use ($app) {
    $response = array();


    $db = new DbHandler();
    
    $subjects = $db->getRecordset("SELECT * FROM subject ORDER BY sb_title");
    if($subjects) {
        //subject found
        $subject_count = count($subjects);

        $response['subjects'] = $subjects;
        $response['status'] = "success";
        $response["message"] = "$subject_count Subjects Found!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No Subject found!";
        echoResponse(201, $response);
    }
});


//  getSubject
$app->get('/getSubject', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $sb_id = $db->purify($app->request->get('id'));
    
    $subject = $db->getOneRecord("SELECT * FROM subject WHERE sb_id='$sb_id'");
    if($subject) {
        //found subject, return success result

        $response['subject'] = $subject;
        $response['status'] = "success";
        $response["message"] = "Subject Details Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading subject!";
        echoResponse(201, $response);
    }
});


?>