<?php

// create bundle
$app->post('/createNewBundle', function() use ($app) {
    
    $response = array();

    $r = json_decode($app->request->getBody());
    verifyRequiredParams(['bdl_description', 'bdl_name' , 'bdl_price', 'bdl_type'],$r->bundle);
    $db = new DbHandler();
    $session = $db->getSession();
    $bdl_creator = $session['trenova_user']['ad_name'];
    $bdl_created_by = $session['trenova_user']['ad_id'];
    $bdl_description = $db->purify($r->bundle->bdl_description);
    $bdl_name = $db->purify($r->bundle->bdl_name);
    $bdl_price = $db->purify($r->bundle->bdl_price);
    $bdl_type = $db->purify($r->bundle->bdl_type);
    $bdl_date_created = date('Y-m-d');
    $table_name = "course_bundle";
    //using switch to determine the case

    switch ($bdl_type) {
        case 'TERM':
        //verify parameters for school and id
        verifyRequiredParams(['selected_school', 'selected_subject'],$r->bundle);
     
        $bdl_school_id = $db->purify($r->bundle->selected_school);
        $bdl_subject_id = $db->purify($r->bundle->selected_subject);
        $bdl_term = $db->purify($r->bundle->bdl_term_id);     
        //create bundle
            $column_names = ['bdl_description', 'bdl_name', 'bdl_price', 'bdl_school_id', 'bdl_subject_id', 'bdl_type', 'bdl_creator', 'bdl_created_by','bdl_date_created', 'bdl_term'];
            $values = [$bdl_description,$bdl_name,$bdl_price,$bdl_school_id,$bdl_subject_id,$bdl_type,$bdl_creator,$bdl_created_by, $bdl_date_created, $bdl_term];
            $result = $db->insertToTable($values, $column_names, $table_name); 
                   
                    if ($result != NULL) {
                        $response["status"] = "success";
                        $response["message"] = "Bundle created successfully";
                        $response["mod_id"] = $result;
                        echoResponse(200, $response);
                    }else{
                        $response["status"] = "error";
                        $response["message"] = "Failed to create bundle. Please try again";
                        echoResponse(201, $response);
                    }
            break;
        
        case 'YEAR':
        //verify parameters for school and id
        verifyRequiredParams(['selected_school', 'selected_subject'],$r->bundle);
     
        $bdl_school_id = $db->purify($r->bundle->selected_school);
        $bdl_subject_id = $db->purify($r->bundle->selected_subject);
     
        //create bundle
            $column_names = ['bdl_description', 'bdl_name', 'bdl_price', 'bdl_school_id', 'bdl_subject_id', 'bdl_type', 'bdl_creator', 'bdl_created_by','bdl_date_created'];
            $values = [$bdl_description,$bdl_name,$bdl_price,$bdl_school_id,$bdl_subject_id,$bdl_type,$bdl_creator,$bdl_created_by, $bdl_date_created];
            $result = $db->insertToTable($values, $column_names, $table_name); 
                   
                    if ($result != NULL) {
                        $response["status"] = "success";
                        $response["message"] = "Bundle created successfully";
                        $response["mod_id"] = $result;
                        echoResponse(200, $response);
                    }else{
                        $response["status"] = "error";
                        $response["message"] = "Failed to create bundle. Please try again";
                        echoResponse(201, $response);
                    }
            break; 

        case 'CLASS':
        //verify parameters for school and id
        verifyRequiredParams(['selected_school', 'selected_subject'],$r->bundle);
     
        $bdl_school_id = $db->purify($r->bundle->selected_school);
        $bdl_subject_id = $db->purify($r->bundle->selected_subject);
        $bdl_class_id = $db->purify($r->bundle->selected_class);

        //create bundle
            $column_names = ['bdl_description', 'bdl_name', 'bdl_price', 'bdl_school_id', 'bdl_subject_id', 'bdl_type', 'bdl_creator', 'bdl_created_by','bdl_date_created', 'bdl_class_id'];
            $values = [$bdl_description,$bdl_name,$bdl_price,$bdl_school_id,$bdl_subject_id,$bdl_type,$bdl_creator,$bdl_created_by, $bdl_date_created, $bdl_class_id];
            $result = $db->insertToTable($values, $column_names, $table_name); 
                   
                    if ($result != NULL) {
                        $response["status"] = "success";
                        $response["message"] = "Bundle created successfully";
                        $response["mod_id"] = $result;
                        echoResponse(200, $response);
                    }else{
                        $response["status"] = "error";
                        $response["message"] = "Failed to create bundle. Please try again";
                        echoResponse(201, $response);
                    }
            break;

        case 'CUSTOM':
        $course_name = $r->bundle->selected_course;
        //create bundle
            $column_names = ['bdl_description', 'bdl_name', 'bdl_price', 'bdl_type', 'bdl_creator', 'bdl_created_by','bdl_date_created'];
            $values = [$bdl_description,$bdl_name,$bdl_price,$bdl_type,$bdl_creator,$bdl_created_by, $bdl_date_created];
            $result = $db->insertToTable($values, $column_names, $table_name); 
                    if ($result > 0) {
                        foreach ($course_name as $value) {
                                $tbl_name = "course_bundle_item";
                                $column_names = ['cbi_bundle_id', 'cbi_course_id'];
                                $values = [$result,$value];
                                $itm_result = $db->insertToTable($values, $column_names, $tbl_name); 
                                                
                               }
                                        if ($itm_result >= 0) {
                                                $response["status"] = "success";
                                                $response["message"] = "Bundle created successfully";
                                                echoResponse(200, $response);
                                        } else {
                                                $response["status"] = "error";
                                                $response["message"] = "Failed to create bundle items. Please try again";
                                                echoResponse(201, $response);
                                        }       
                     } else{
                                    $response["status"] = "error";
                                    $response["message"] = "Failed to create bundle. Please try again";
                                    echoResponse(201, $response);
                                }
            break;      

        default:
            //someone trying to be smart eh!!
                    $response["status"] = "error";
                    $response["message"] = "Bundle TYPE Not recognised. Please Select one from the options and again";
                    echoResponse(201, $response);
            break;
    }
    
});

//edit existing bundle

$app->post('/editExistingBundle', function() use ($app) {
    
    $response = array();

    $r = json_decode($app->request->getBody());
    verifyRequiredParams(['bdl_id', 'bdl_name','bdl_description', 'bdl_name', 'bdl_price', 'bdl_type'],$r->bundle);
    $db = new DbHandler();
    $bdl_id = $db->purify($r->bundle->bdl_id);
    $bdl_name = $db->purify($r->bundle->bdl_name);
    $bdl_description = $db->purify($r->bundle->bdl_description);
    $bdl_price = $db->purify($r->bundle->bdl_price);
    $bdl_type = $db->purify($r->bundle->bdl_type); 

    $isBundleExists = $db->getOneRecord("SELECT 1 FROM course_bundle WHERE bdl_id = '$bdl_id' ");
    if($isBundleExists){

        switch ($bdl_type) {
            case 'TERM':
                    //verify
                    verifyRequiredParams(['selected_school', 'selected_subject', 'bdl_term_id'],$r->bundle);
                    $bdl_school_id = $db->purify($r->bundle->selected_school);
                    $bdl_subject_id = $db->purify($r->bundle->selected_subject);
                    $bdl_term_id = $db->purify($r->bundle->bdl_term_id);
                    
                    //update bundle 
                    $table_to_update = 'course_bundle';
                    $columns_to_update = ['bdl_description'=>$bdl_description, 'bdl_price'=>$bdl_price, 'bdl_school_id'=>$bdl_school_id, 'bdl_subject_id'=>$bdl_subject_id, 'bdl_type'=>$bdl_type, 'bdl_name'=>$bdl_name, 'bdl_term'=>$bdl_term_id];
                    $where_clause = ['bdl_id'=>$bdl_id];
                    $result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

                    if ($result > 0 ) {
                                $response["status"] = "success";
                                $response["message"] = " Bundle updated successfully";
                                echoResponse(200, $response);                
                                        //just incase it was converted from a custom bundle to this type of bundle          
                                        $table_name = 'course_bundle_item';
                                        $col_name = ['cbi_bundle_id'=>$bdl_id];
                                        $r = $db->deleteFromTableWhere($table_name, $col_name);
                    } else {
                        $response["status"] = "error";
                        $response["message"] = "Failed to update bundle. Please try again";
                        echoResponse(201, $response);
                    }
                break;
           
            case 'CLASS':
                    //verify
                    verifyRequiredParams(['selected_school', 'selected_subject', 'selected_class'],$r->bundle);
                    $bdl_school_id = $db->purify($r->bundle->selected_school);
                    $bdl_subject_id = $db->purify($r->bundle->selected_subject);
                    $bdl_class_id = $db->purify($r->bundle->selected_class);
                    
                    //update bundle 
                    $table_to_update = 'course_bundle';
                    $columns_to_update = ['bdl_description'=>$bdl_description, 'bdl_price'=>$bdl_price, 'bdl_school_id'=>$bdl_school_id, 'bdl_subject_id'=>$bdl_subject_id, 'bdl_type'=>$bdl_type, 'bdl_name'=>$bdl_name, 'bdl_class_id'=>$bdl_class_id];
                    $where_clause = ['bdl_id'=>$bdl_id];
                    $result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

                    if ($result > 0 ) {
                                $response["status"] = "success";
                                $response["message"] = " Bundle updated successfully";
                                echoResponse(200, $response);                
                                        //just incase it was converted from a custom bundle to this type of bundle          
                                        $table_name = 'course_bundle_item';
                                        $col_name = ['cbi_bundle_id'=>$bdl_id];
                                        $r = $db->deleteFromTableWhere($table_name, $col_name);
                    } else {
                        $response["status"] = "error";
                        $response["message"] = "Failed to update bundle. Please try again";
                        echoResponse(201, $response);
                    }
                break;
           
            case 'YEAR':
                    //verify
                    verifyRequiredParams(['selected_school', 'selected_subject'],$r->bundle);
                    $bdl_school_id = $db->purify($r->bundle->selected_school);
                    $bdl_subject_id = $db->purify($r->bundle->selected_subject);
                    
                    //update bundle 
                    $table_to_update = 'course_bundle';
                    $columns_to_update = ['bdl_description'=>$bdl_description, 'bdl_price'=>$bdl_price, 'bdl_school_id'=>$bdl_school_id, 'bdl_subject_id'=>$bdl_subject_id, 'bdl_type'=>$bdl_type, 'bdl_name'=>$bdl_name];
                    $where_clause = ['bdl_id'=>$bdl_id];
                    $result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

                    if ($result > 0 ) {
                                $response["status"] = "success";
                                $response["message"] = " Bundle updated successfully";
                                echoResponse(200, $response);
                                        //just incase it was converted from a custom bundle to this type of bundle          
                                        $table_name = 'course_bundle_item';
                                        $col_name = ['cbi_bundle_id'=>$bdl_id];
                                        $r = $db->deleteFromTableWhere($table_name, $col_name);   
                    } else {
                        $response["status"] = "error";
                        $response["message"] = "Failed to update bundle. Please try again";
                        echoResponse(201, $response);
                    }
                break;
           
            case 'CUSTOM':
            /*delete existing custom bundles, then re-insert the new ones.*/
            $course_name = $r->bundle->selected_course;
            $table_to_update = 'course_bundle';
            $columns_to_update = ['bdl_description'=>$bdl_description, 'bdl_price'=>$bdl_price, 'bdl_type'=>$bdl_type, 'bdl_name'=>$bdl_name];
            $where_clause = ['bdl_id'=>$bdl_id];
            $result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);
            //delete existing bundle
            if ($result >= 0) {
                $table_name = 'course_bundle_item';
                $col_name = ['cbi_bundle_id'=>$bdl_id];
                $r = $db->deleteFromTableWhere($table_name, $col_name);
                    if($r >0) {
                        //create updated bundle
                            foreach ($course_name as $value) {
                                    $tbl_name = "course_bundle_item";
                                    $column_names = ['cbi_bundle_id', 'cbi_course_id'];
                                    $values = [$bdl_id,$value];
                                    $itm_result = $db->insertToTable($values, $column_names, $tbl_name);
                                             }       
                                            if ($itm_result >= 0) {
                                                    $response["status"] = "success";
                                                    $response["message"] = "Bundle updated successfully";
                                                    echoResponse(200, $response);
                                            } else {
                                                    $response["status"] = "error";
                                                    $response["message"] = "Failed to updated bundle items. Please try again";
                                                    echoResponse(201, $response);
                                            }
                        } else{
                            $response["status"] = "success";
                            $response["message"] = "Bundle update failed";
                            echoResponse(201, $response);
                        }
            }else{
                    $response["status"] = "success";
                    $response["message"] = "Failed to update bundle";
                    echoResponse(201, $response);
                }
                break;
            
            default:
                    $response["status"] = "error";
                    $response["message"] = "Bundle TYPE does not exist!!!";
                    echoResponse(201, $response);
                break;
        }
    }else{
        $response["status"] = "error";
        $response["message"] = "ERROR: Bundle does not exist!";
        echoResponse(201, $response);
    }
});

//get Bundle Details

$app->get('/getBundleDetails', function() use ($app) {

    $response = array();

    $db = new DbHandler();
    $cbi_bundle_id = $db->purify($app->request->get('id'));

    $course_bundles = $db->getRecordset("SELECT course_title, course_id FROM course_bundle_item
    LEFT JOIN course ON cbi_course_id = course_id WHERE cbi_bundle_id = '$cbi_bundle_id' ");

    $bundle = $db->getOneRecord("SELECT * FROM course_bundle WHERE bdl_id = '$cbi_bundle_id' ");

    if($bundle) {
       
        $response['course_bundles'] = $course_bundles;        
        $response['bundle'] = $bundle;
        $response['status'] = "success";
        $response["message"] = "Bundle Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No bundle found!";
        echoResponse(201, $response);
    }
});

//delete Single Course Bundle

$app->get('/deleteSingleCourseBundle', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $cbi_course_id = $db->purify($app->request->get('id'));
    $cbi_bundle_id = $db->purify($app->request->get('bid'));

    $table_name = 'course_bundle_item';
    $col_name = ['cbi_course_id'=>$cbi_course_id, 'cbi_bundle_id'=>$cbi_bundle_id];
    $result = $db->deleteFromTableWhere($table_name, $col_name);

    if($result > 0) {
        //course deleted
        $response['status'] = "success";
        $response["message"] = "Course Deleted successfully!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error deleting course!";
        echoResponse(201, $response);
    }


});

//delete Bundle

$app->get('/deleteBundle', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $bdl_id = $db->purify($app->request->get('id'));

    $table_name = 'course_bundle';
    $col_name = ['bdl_id'=>$bdl_id];
    $result = $db->deleteFromTableWhere($table_name, $col_name);

    if($result > 0) {
        //course deleted
        $response['status'] = "success";
        $response["message"] = "Bundle Deleted successfully!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error deleting bundle!";
        echoResponse(201, $response);
    }


});

//getbundle for edit

$app->get('/getBundle', function() use ($app) {

    $response = array();

    $db = new DbHandler();
    $bundle_id = $db->purify($app->request->get('id'));
    
    $bundle = $db->getOneRecord("SELECT bdl_term AS bdl_term_id, bdl_class_id AS selected_class, bdl_subject_id AS selected_subject, bdl_school_id AS selected_school , bdl_id, bdl_name, bdl_description, bdl_type, bdl_price FROM course_bundle WHERE bdl_id = '$bundle_id' ");

    $bundle_select = $db->getRecordset("SELECT cbi_course_id  FROM course_bundle_item WHERE cbi_bundle_id = '$bundle_id' ");
    foreach ($bundle_select as $key => $value) {
        foreach ($value as $k => $v) {
            $array[] = $v;
        }
    }
$bundle['selected_course'] = $array;
    if($bundle) {       
        $response['bundle'] = $bundle;
        $response['status'] = "success";
        $response["message"] = "Bundle Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No Bundle found!";
        echoResponse(201, $response);
    }
});

//get list of bundles
$app->get('/getBundleLists', function() use ($app) {

    $response = array();

    $db = new DbHandler();

    $bundles = $db->getRecordset("SELECT * FROM course_bundle ORDER BY bdl_date_created DESC");

    if($bundles) {
       
        $response['bundles'] = $bundles;       
        $response['status'] = "success";
        $response["message"] = "Bundles Loaded Successfully";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No Bundle found!";
        echoResponse(201, $response);
    }
});

$app->get('/getCourseBundleList', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    
    $course = $db->getRecordset("SELECT * FROM course WHERE course_status = 'ACTIVE' ");
    if($course) {
        //found course, return success result
        $response['courses'] = $course;
        $response['status'] = "success";
        $response["message"] = "Course Details Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading course!";
        echoResponse(201, $response);
    }
});

$app->get('/getBundleList', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    
    $bundles = $db->getRecordset("SELECT * FROM course_bundle LEFT JOIN subject ON bdl_subject_id = sb_id LEFT JOIN school ON bdl_school_id = sch_id LEFT JOIN class ON bdl_class_id = class_id ORDER BY bdl_name");
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


$app->get('/getBundleItems', function() use ($app) {

    $response = array();

    $db = new DbHandler();
    $cbi_bundle_id = $db->purify($app->request->get('id'));
    $bundle = $db->getOneRecord("SELECT * FROM course_bundle WHERE bdl_id='$cbi_bundle_id'");

    switch ($bundle['bdl_type']) {
        case 'CUSTOM':
            $bundle_items = $db->getRecordset("SELECT course_title, course_id FROM course_bundle_item LEFT JOIN course ON cbi_course_id = course_id WHERE cbi_bundle_id = '$cbi_bundle_id'");
            break;
        
        case 'TERM':
            $bundle_items = $db->getRecordset("SELECT course_title, course_id FROM course LEFT JOIN class ON course_class_id = class_id WHERE class_school_id = '".$bundle['bdl_school_id']."' AND course_subject_id = '".$bundle['bdl_subject_id']."' AND course_term = '".$bundle['bdl_term']."' ");
            break;

        case 'CLASS':
            $bundle_items = $db->getRecordset("SELECT course_title, course_id FROM course WHERE course_class_id = '".$bundle['bdl_class_id']."' AND course_subject_id = '".$bundle['bdl_subject_id']."' ORDER BY course_term ");
            break;

        case 'YEAR':
            $bundle_items = $db->getRecordset("SELECT course_title, course_id FROM course LEFT JOIN class ON course_class_id = class_id WHERE class_school_id = '".$bundle['bdl_school_id']."' AND course_subject_id = '".$bundle['bdl_subject_id']."' ORDER BY course_class_id, course_term ");
            break;
    }

    if($bundle_items) {
       
        $response['bundle_items'] = $bundle_items;        
        $response['status'] = "success";
        $response["message"] = "Bundle Items Found!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No item found in bundle!";
        echoResponse(201, $response);
    }
});
