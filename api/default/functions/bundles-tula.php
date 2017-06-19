<?php

$app->get('/getBundleList', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    
    $bundles = $db->getRecordset("SELECT * FROM course_bundle LEFT JOIN subject ON bdl_subject_id = sb_id LEFT JOIN school ON bdl_school_id = sch_id ORDER BY bdl_name");
    if($bundles) {
        //bundles found
        $response['bundles'] = $bundles;
        $response['status'] = "success";
        $response["message"] = "Bundles Found!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No bundle found!";
        echoResponse(201, $response);
    }
});