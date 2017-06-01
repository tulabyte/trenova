<?php

//getQuizList

$app->get('/getQuizList', function() use ($app) {
    
    $response = array();
    $db = new DbHandler();
    $course_id = $db->purify($app->request->get('id'));
    
    $quizs = $db->getRecordset("SELECT uq_id, uq_score, uq_date_taken, uq_target_id, uq_user_id, user_fullname FROM user_quiz LEFT JOIN user ON uq_user_id=user_id WHERE uq_target_id = '$course_id' ORDER BY uq_date_taken DESC");

    if($quizs) {
       
        $response['quizs'] = $quizs;
        $response['status'] = "success";
        $response["message"] = "Quiz!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No Quiz found!";
        echoResponse(201, $response);
    }
});

//getQuizDetails

$app->get('/getQuizDetails', function() use ($app) {

    $response = array();

    $db = new DbHandler();
    $quiz_id = $db->purify($app->request->get('id'));

    $course = $db->getOneRecord("SELECT course_title FROM user_quiz LEFT JOIN course ON uq_target_id = course_id WHERE uq_id = '$quiz_id' ");

    $quizs = $db->getRecordset("SELECT q.q_question, qu.qo_option, quu.qo_option AS q_opt 
            FROM user_quiz_answers 
            LEFT JOIN question q ON uqa_question_id = q.q_id
            LEFT JOIN question_option qu ON q_id = qu.qo_question_id
            LEFT JOIN question_option quu ON uqa_option_id = quu.qo_id
            WHERE qu.qo_is_correct = '1' AND uqa_quiz_id = '$quiz_id' ");
    if($quizs) {
       
        $response['quizs'] = $quizs;
        $response['course'] = $course;
        $response['status'] = "success";
        $response["message"] = "Quiz!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No Quiz found!";
        echoResponse(201, $response);
    }
});