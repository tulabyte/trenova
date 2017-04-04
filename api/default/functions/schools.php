<?php

/*
1. /createSchool - POST (school)
2. /editSchool - POST(school)
3. /deleteSchool - GET(id)
4. /getSchool - GET(id)
5. /getSchoolList - GET
*/

//createSchool
$app->post('/createSchool', function() use ($app) {
    
    $response = array();

    $r = json_decode($app->request->getBody());
    verifyRequiredParams(array('sch_name'),$r->school);
    //require_once 'passwordHash.php';
    $db = new DbHandler();
    $sch_name = $db->purify($r->school->sch_name);
    $isSchoolExists = $db->getOneRecord("SELECT 1 FROM school WHERE sch_name = '$sch_name'");
  
    if(!$isSchoolExists){
        $table_name = "school";
        $column_names = ['sch_name'];
        $values = [$sch_name];

        $result = $db->insertToTable($values, $column_names, $table_name);

        if ($result != NULL) {
            $response["status"] = "success";
            $response["message"] = "School created successfully";
            $response["school"] = $result;

            //log action
            $log_details = "Created New School: $sch_name (ID: $result)";
            $db->logAction($log_details);            

            echoResponse(200, $response);
        } else {
            $response["status"] = "error";
            $response["message"] = "Failed to create School. Please try again";
            echoResponse(201, $response);
        }            
    }else{
        $response["status"] = "error";
        $response["message"] = "School with the provided name already exists, please try another!";
        echoResponse(201, $response);
    }
});


// edit school
$app->post('/editSchool', function() use ($app) {
    
    $response = array();

    $r = json_decode($app->request->getBody());
    verifyRequiredParams(array('sch_name','sch_id'),$r->school);
    //require_once 'passwordHash.php';
    $db = new DbHandler();
    $sch_id = $db->purify($r->school->sch_id);
    $sch_name = $db->purify($r->school->sch_name);
    $isSchoolExists = $db->getOneRecord("SELECT 1 FROM school WHERE sch_id = '$sch_id'");

    if($isSchoolExists){
        $table_to_update = "school";
        $columns_to_update = ['sch_name'=>$sch_name];
        $where_clause = ['sch_id'=>$sch_id];

        $result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

        if ($result > 0) {
            //log action
            $log_details = "Edited School: $sch_name (ID: $sch_id)";
            $db->logAction($log_details);

            $response["status"] = "success";
            $response["message"] = "School updated successfully";
            echoResponse(200, $response);
        } else {
            $response["status"] = "error";
            $response["message"] = "Failed to update school. Please try again";
            echoResponse(201, $response);
        }            
    }else{
        $response["status"] = "error";
        $response["message"] = "ERROR: School does not exist!";
        echoResponse(201, $response);
    }
});

// delete School
$app->get('/deleteSchool', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $sch_id = $db->purify($app->request->get('id'));

    //get school details
    $school = $db->getOneRecord("SELECT * FROM school WHERE sch_id='$sch_id'");

    $table_name = 'school';
    $col_name = 'sch_id';
    $result = $db->deleteFromTable($table_name, $col_name, $sch_id);

    if($result > 0) {
        //school deleted

        //log action
        $log_details = "Deleted School: ".$school['sch_name']." ($sch_id)";
        $db->logAction($log_details);

        $response['status'] = "success";
        $response["message"] = "School Deleted successfully!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error deleting school!";
        echoResponse(201, $response);
    }


});


// get school list
$app->get('/getSchoolList', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    
    $schools = $db->getRecordset("SELECT * FROM school ORDER BY sch_name");
    if($schools) {
        //schools found
        $sch_count = count($schools);

        $response['schools'] = $schools;
        $response['status'] = "success";
        $response["message"] = "$sch_count Schools Found!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No school found!";
        echoResponse(201, $response);
    }
});


// get school
$app->get('/getSchool', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $sch_id = $db->purify($app->request->get('id'));
    
    $school = $db->getOneRecord("SELECT * FROM school WHERE sch_id='$sch_id'");
    if($school) {
        //found school, return success result

        $response['school'] = $school;
        $response['status'] = "success";
        $response["message"] = "School Details Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading school!";
        echoResponse(201, $response);
    }
});


?>