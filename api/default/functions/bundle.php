<?php

// create bundle
$app->post('/createNewBundle', function() use ($app) {
    
    $response = array();

    $r = json_decode($app->request->getBody());
    verifyRequiredParams(['bdl_description', 'bdl_name' , 'bdl_price', 'selected_school', 'selected_subject', 'bdl_type'],$r->bundle);
    $db = new DbHandler();
    $session = $db->getSession();
    $bdl_creator = $session['trenova_user']['ad_name'];
    $bdl_created_by = $session['trenova_user']['ad_id'];
    $bdl_description = $db->purify($r->bundle->bdl_description);
    $bdl_name = $db->purify($r->bundle->bdl_name);
    $bdl_price = $db->purify($r->bundle->bdl_price);
    $bdl_school_id = $db->purify($r->bundle->selected_school);
    $bdl_subject_id = $db->purify($r->bundle->selected_subject);
    $bdl_type = $db->purify($r->bundle->bdl_type);
    $bdl_date_created = date('Y-m-d');

//create bundle
    $table_name = "course_bundle";
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

    
});

//edit question Module

$app->post('/editQuestionModule', function() use ($app) {
    
    $response = array();

    $r = json_decode($app->request->getBody());
    verifyRequiredParams(['q_id', 'optiona', 'optionb' , 'optionc', 'optiond', 'option','optiona_id','optionb_id','optionc_id','optiond_id'],$r->question);
    $db = new DbHandler();
    $q_id = $db->purify($r->question->q_id);
    $question = $db->purify($r->question->q_question);
    $option1 = $db->purify($r->question->optiona);
    $option1_id = $db->purify($r->question->optiona_id);
    $option2 = $db->purify($r->question->optionb);
    $option2_id = $db->purify($r->question->optionb_id);
    $option3 = $db->purify($r->question->optionc);
    $option3_id = $db->purify($r->question->optionc_id);
    $option4 = $db->purify($r->question->optiond);
    $option4_id = $db->purify($r->question->optiond_id);
    $correct_option = $db->purify($r->question->option); 

    $isQuestionExists = $db->getOneRecord("SELECT 1 FROM question WHERE q_id = '$q_id' ");
    if($isQuestionExists){
        //update question 
        $table_to_update = "question";
        $columns_to_update = ['q_question'=>$question];
        $where_clause = ['q_id'=>$q_id];
        $result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

        if (empty($result) || !empty($result)) {

            //update options
             $qo_option = array($option1_id=>$option1, $option2_id=>$option2, $option3_id=>$option3, $option4_id =>$option4);
            
                foreach ($qo_option as $id => $q_option) {
                    if ($q_option == $correct_option) {

                        $table_to_update = "question_option";
                        $qo_is_correct = 1 ;
                        $columns_to_update = ['qo_option' => $q_option, 'qo_is_correct' =>$qo_is_correct];
                        $where_clause = ['qo_id' => $id];
                        $option_result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause); 
                        $count = 1;   

                    } else{
                            $column_to_null = 'qo_is_correct';
                            $table_to_update = "question_option";
                            $columns_to_update = ['qo_option' => $q_option];
                            $where_clause = ['qo_id' => $id];
                            $option_result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);
                               
                                if ($option_result >=0 ) {
                                    $option_to_null = $db->updateToNull($table_to_update, $column_to_null, $where_clause);
                                    }
                        }
                }
                if ($result >= 0 || $option_result >= 0) {
                    $response["status"] = "success";
                    $response["message"] = $counter;"Update successfully";
                    $response["q_id"] = $result;
                    echoResponse(200, $response);
                }else{
                    $response["status"] = "error";
                    $response["message"] = "Update failed, Please try again.";
                    echoResponse(201, $response);
                }
        } else {
            $response["status"] = "error";
            $response["message"] = "Failed to update question. Please try again";
            echoResponse(201, $response);
        }            
    }else{
        $response["status"] = "error";
        //$response['message'] = $r->module;
        $response["message"] = "ERROR: Lesson does not exist!";
        echoResponse(201, $response);
    }
});

$app->post('/editQuestionTitle', function() use ($app) {
    
    $response = array();
    $r = json_decode($app->request->getBody());
    verifyRequiredParams(['q_id', 'q_question'],$r->question);
    $db = new DbHandler();
    $q_id = $db->purify($r->question->q_id);
    $question = $db->purify($r->question->q_question);

    $isQuestionExists = $db->getOneRecord("SELECT 1 FROM question WHERE q_id = '$q_id' ");
    if($isQuestionExists){
        //update question 
        $table_to_update = "question";
        $columns_to_update = ['q_question'=>$question];
        $where_clause = ['q_id'=>$q_id];
        $result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

        if ($result > 0)  {
                    $response["status"] = "success";
                    $response["message"] = "Question updated successfully ";
                    $response["q_id"] = $result;
                    echoResponse(200, $response);
        } else {
            $response["status"] = "error";
            $response["message"] = "Failed to update question. Please try again";
            echoResponse(201, $response);
        }            
    }else{
        $response["status"] = "error";
        //$response['message'] = $r->module;
        $response["message"] = "ERROR: Question does not exist!";
        echoResponse(201, $response);
    }
});



$app->post('/updateQuizLimit', function() use ($app) {
    
    $response = array();
    $r = json_decode($app->request->getBody());
    verifyRequiredParams(['course_id', 'course_quiz_minutes', 'course_questions_per_quiz'],$r->course);
    $db = new DbHandler();
    $course_id = $db->purify($r->course->course_id);
    $course_quiz_minutes = $db->purify($r->course->course_quiz_minutes);
    $course_questions_per_quiz = $db->purify($r->course->course_questions_per_quiz);

    $isCourseExists = $db->getOneRecord("SELECT 1 FROM course WHERE course_id = '$course_id' ");
    if($isCourseExists){
        //update question 
        $table_to_update = "course";
        $columns_to_update = ['course_quiz_minutes'=>$course_quiz_minutes, 'course_questions_per_quiz'=>$course_questions_per_quiz];
        $where_clause = ['course_id'=>$course_id];
        $result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

        if ($result > 0)  {
                    $response["status"] = "success";
                    $response["message"] = "Update successfully ";
                    echoResponse(200, $response);
        } else {
            $response["status"] = "error";
            $response["message"] = "Update failed. Please try again";
            echoResponse(201, $response);
        }            
    }else{
        $response["status"] = "error";
        $response["message"] = "ERROR: Course does not exist!";
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
        $response["message"] = "No Question found!";
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

//getbundle for edit

$app->get('/getBundle', function() use ($app) {

    $response = array();

    $db = new DbHandler();
    $bundle_id = $db->purify($app->request->get('id'));
    
    $bundle = $db->getOneRecord("SELECT bdl_subject_id AS selected_subject, bdl_school_id AS selected_school , sb_title, sch_name, bdl_id, bdl_name, bdl_description, bdl_type, bdl_price FROM course_bundle
            LEFT JOIN subject ON bdl_subject_id = sb_id
            LEFT JOIN school ON  bdl_school_id = sch_id 
                WHERE bdl_id = '$bundle_id' ");

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
