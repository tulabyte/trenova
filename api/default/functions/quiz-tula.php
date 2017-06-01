<?php

// create quiz session
$app->get('/createQuizSession', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $course_id = $db->purify($app->request->get('id'));
    $user_id = $db->purify($app->request->get('user'));

    // get 5 questions for the course
    $questions = $db->getRecordset("SELECT *, (SELECT qo_id FROM question_option WHERE qo_question_id=q_id AND qo_is_correct IS NOT NULL) AS q_correct_option FROM question WHERE q_course_id = '$course_id' ORDER BY RAND() LIMIT 3");

    if($questions) {
    	$options = [];
    	// get options for each question
    	foreach ($questions as $question) {
    		$op = $db->getRecordset("SELECT * FROM question_option WHERE qo_question_id = '".$question['q_id']."'");
    		$options[$question['q_id']] = $op;
    	}
    	// create quiz session
    	$table = "user_quiz";
        $columns = ['uq_user_id', 'uq_target_id', 'uq_date_taken'];
        $values = [$user_id, $course_id, date("Y-m-d")];
        $quiz_id = $db->insertToTable($values, $columns, $table);

        if($quiz_id) {
        	$response['questions'] = $questions;
    		$response['options'] = $options;
        	$response['quiz_id'] = $quiz_id;
	        $response['status'] = "success";
	        $response["message"] = "Quiz Generated Successfully!";
	        echoResponse(200, $response);
        } else {
	        $response['status'] = "error";
	        $response["message"] = "Error occured while trying to initiate your quiz session!";
	        echoResponse(201, $response);	
        }
    } else {
    	$response['course_id'] = $course_id;
    	$response['status'] = "error";
        $response["message"] = "No questions found for this quiz!";
        echoResponse(201, $response);
    }
    
});

// save quiz result
$app->post('/saveQuizResult', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    
    $r = json_decode($app->request->getBody());
    // var_dump($r); die;

    $quiz = $r->quiz;
    $questions = $r->questions;

    $quiz_id = $db->purify($quiz->id);
    $quiz_score = $db->purify($quiz->score);

    // update the quiz details with result
    $table = "user_quiz";
    $columns = ['uq_endtime'=>date("Y-m-d H:i:s"), 'uq_score'=>$quiz_score];
    $where_clause = ['uq_id'=>$quiz_id];
    $result = $db->updateInTable($table, $columns, $where_clause);

    // store quiz answers
    foreach ($questions as $key => $question) {
        $uqa_question_id = $db->purify($question->q_id);
        $uqa_option_id = $question->q_answer > 0? $db->purify($question->q_answer) : NULL;
        $uqa_is_correct = $question->q_answer == $question->q_correct_option ? 1 : NULL;

        $table = "user_quiz_answers";
        $columns = ['uqa_quiz_id', 'uqa_question_id', 'uqa_option_id', 'uqa_is_correct'];
        $values = [$quiz_id, $uqa_question_id, $uqa_option_id, $uqa_is_correct];
        $uqa_id = $db->insertToTable($values, $columns, $table);
    }

    if($result && $uqa_id) {
        $response['status'] = "success";
        $response["message"] = "Results saved successfully!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error Occured while trying to save result!";
        echoResponse(201, $response);
    }
    
});

// get quiz result
$app->get('/getQuizResult', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $quiz_id = $db->purify($app->request->get('id'));
    
    $quiz_results = $db->getOneRecord("SELECT * FROM user_quiz WHERE uq_id='$quiz_id'");

    if($quiz_results) {
        $course = $db->getOneRecord("SELECT course_id, course_title FROM course WHERE course_id='".$quiz_results['uq_target_id']."'");

        $response['quiz_results'] = $quiz_results;
        $response['course'] = $course;
        $response['status'] = "success";
        $response["message"] = "Quiz Results Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading result!";
        echoResponse(201, $response);
    }
});