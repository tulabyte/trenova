<?php

// create question
$app->post('/createQuestionModule', function() use ($app) {
    
    $response = array();

    $r = json_decode($app->request->getBody());
    verifyRequiredParams(['optiona', 'optionb' , 'optionc', 'optiond', 'option'],$r->question);
    $db = new DbHandler();
    $q_question = $db->purify($r->question->q_question);
    $optiona = $db->purify($r->question->optiona);
    $optionb = $db->purify($r->question->optionb);
    $optionc = $db->purify($r->question->optionc);
    $optiond = $db->purify($r->question->optiond);
    $correct_option = $db->purify($r->question->option);
    $q_number = isset($r->question->question_number) ? $db->purify($r->question->question_number) : '';
    $q_type = "COURSE";
    $course_id = $db->purify($r->question->course_id);
        if ($q_number == '') {
            $maxPos = $db->getOneRecord("SELECT MAX(q_number) AS max_pos FROM question WHERE q_course_id = '$course_id'");
            $q_number = $maxPos['max_pos'] + 1;
        }
    $table_name = "question";
    $column_names = ['q_question', 'q_type', 'q_number', 'q_course_id'];
    $values = [$q_question, $q_type, $q_number, $course_id];
    $result = $db->insertToTable($values, $column_names, $table_name); 

    if ($result != NULL) {
        //create question options
        $qo_option = array($optiona, $optionb, $optionc, $optiond);
        
            foreach ($qo_option as $q_option) {
                if ($q_option == $correct_option) {

                    $table_name = "question_option";
                    $qo_is_correct = 1 ;
                    $column_names = ['qo_question_id', 'qo_option', 'qo_is_correct'];
                    $values = [$result, $q_option, $qo_is_correct];
                    $option_result = $db->insertToTable($values, $column_names, $table_name);    

                } else{
                    
                        $table_name = "question_option";
                        $column_names = ['qo_question_id', 'qo_option'];
                        $values = [$result, $q_option];
                        $option_result = $db->insertToTable($values, $column_names, $table_name);  
                    }
            }
            if ($option_result != NULL) {
                $response["status"] = "success";
                $response["message"] = "Question And Options created successfully";
                $response["mod_id"] = $result;
                echoResponse(200, $response);
            }else{
                $response["status"] = "error";
                $response["message"] = "Failed to create Question Options. Please try again";
                echoResponse(201, $response);
            }
    } else {
        $response["status"] = "error";
        $response["message"] = "Failed to create question. Please try again";
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

//getQuestionDetails

$app->get('/getQuestionEditDetail', function() use ($app) {

    $response = array();

    $db = new DbHandler();
    $course_id = $db->purify($app->request->get('id'));

    $course = $db->getOneRecord("SELECT * FROM course WHERE course_id = '$course_id' ");

    if($course) {
       
        $response['course'] = $course;        
        $response['status'] = "success";
        $response["message"] = "Question!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No Question found!";
        echoResponse(201, $response);
    }
});

//getQuestion for edit

$app->get('/getQuestionEditList', function() use ($app) {

    $response = array();

    $db = new DbHandler();
    $question_id = $db->purify($app->request->get('id'));

    $question = $db->getOneRecord("SELECT * FROM question WHERE q_id = '$question_id' ");
    $question_option = $db->getRecordset("SELECT * FROM question_option WHERE qo_question_id = '$question_id' ORDER BY qo_id");
    $qo_is_correct = $db->getOneRecord("SELECT * FROM question_option WHERE qo_question_id = '$question_id' AND qo_is_correct = '1' ");

    if($question) {
       
        $response['question'] = $question;
        $response['qo_is_correct'] = $qo_is_correct;
        $response['question_options'] = $question_option;        
        $response['status'] = "success";
        $response["message"] = "Question!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No Question found!";
        echoResponse(201, $response);
    }
});


$app->get('/getQuestionLists', function() use ($app) {

    $response = array();

    $db = new DbHandler();
    $question_course_id = $db->purify($app->request->get('id'));

    $question = $db->getRecordset("SELECT * FROM question WHERE q_course_id = '$question_course_id' ORDER BY q_number ASC");
    $course = $db->getOneRecord("SELECT * FROM course WHERE course_id = '$question_course_id' ");

    if($question || $course) {
       
        $response['question'] = $question;
        $response['course'] = $course;        
        $response['status'] = "success";
        $response["message"] = "Question!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No Question found!";
        echoResponse(201, $response);
    }
});


// create import question
$app->post('/createImportCsvQuestions', function() use ($app) {
    
    $response = array();

    $r = json_decode($app->request->getBody());
//    verifyRequiredParams(['optiona', 'optionb' , 'optionc', 'optiond', 'option'],$r->question);
    $db = new DbHandler();

      $q_question = $r->question;
     foreach ($q_question as $key => $value) {
               $import_question = $value->question;
               $import_option = $value->option;
               $import_option_correct = $value->iscorrect;

                   if ($import_question != " " && $import_question != "") {
                       //create  question and first option
                            //get question number
                            $maxPos = $db->getOneRecord("SELECT MAX(q_number) AS max_pos FROM question WHERE q_course_id = '$course_id'");
                            $q_number = $maxPos['max_pos'] + 1;
                            $q_type = "COURSE";
                            $course_question = $import_question;
                            $course_question_option = $import_option;
                            $course_question_iscorrect = $import_option_correct;
                            $course_id = $db->purify($r->course);

                            $table_name = "question";
                            $column_names = ['q_question', 'q_type', 'q_number', 'q_course_id'];
                            $values = [$course_question, $q_type, $q_number, $course_id];
                            $result = $db->insertToTable($values, $column_names, $table_name);
                            $q_id = $result;

                                if ($result != NULL) {
                                    if ($course_question_iscorrect > 0) {
                                       //create question options and correct 
                                            $table_name = "question_option";
                                            $column_names = ['qo_question_id', 'qo_option', 'qo_is_correct'];
                                            $values = [$result, $course_question_option, $course_question_iscorrect];
                                            $option_result = $db->insertToTable($values, $column_names, $table_name);       
                                        }else{
                                            $table_name = "question_option";
                                            $column_names = ['qo_question_id', 'qo_option'];
                                            $values = [$result, $course_question_option];
                                            $option_result = $db->insertToTable($values, $column_names, $table_name);

                                        }
                                }

                    } else {
                        $course_question_option = $import_option;
                        $course_question_iscorrect = $import_option_correct;

                        if ($course_question_iscorrect != '0' ) {
                               //create question options and correct 
                                    $table_name = "question_option";
                                    $column_names = ['qo_question_id', 'qo_option', 'qo_is_correct'];
                                    $values = [$q_id, $course_question_option, $course_question_iscorrect];
                                    $option_result = $db->insertToTable($values, $column_names, $table_name);       
                            }else{
                                    $table_name = "question_option";
                                    $column_names = ['qo_question_id', 'qo_option'];
                                    $values = [$q_id, $course_question_option];
                                    $option_result = $db->insertToTable($values, $column_names, $table_name);
                            }

                    }
        }      
            if ($option_result != NULL) {
            $response["status"] = "success";
            $response["message"] = "Question And Options created successfully";
            $response["mod_id"] = $result;
            echoResponse(200, $response);
        }else{
            $response["status"] = "error";
            $response["message"] = "Failed to create Question Options. Please try again";
            echoResponse(201, $response);
        }
});