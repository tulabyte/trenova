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
            FROM user_quiz_answer 
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

// create quiz session
$app->get('/createQuizSession', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $course_id = $db->purify($app->request->get('id'));
    $user_id = $db->purify($app->request->get('user'));

    // course details
    $course = $db->getOneRecord("SELECT course_questions_per_quiz FROM course WHERE course_id = '$course_id'");
    $quiz_length = $course['course_questions_per_quiz'];

    // get questions for the course
    $questions = $db->getRecordset("SELECT *, (SELECT qo_id FROM question_option WHERE qo_question_id=q_id AND qo_is_correct IS NOT NULL) AS q_correct_option FROM question WHERE q_course_id = '$course_id' ORDER BY RAND() LIMIT $quiz_length");

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

        $table = "user_quiz_answer";
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
        $course = $db->getOneRecord("SELECT course_id, course_title, course_questions_per_quiz FROM course WHERE course_id='".$quiz_results['uq_target_id']."'");



        // get questions attempted
        $questions = $db->getRecordset("
SELECT q_id, q_question, uqa_id, uqa_option_id, uqa_is_correct, uqa_quiz_id 
FROM user_quiz_answer
LEFT JOIN question ON uqa_question_id = q_id
WHERE uqa_quiz_id = '$quiz_id'");

        // get options for each question
        $options = [];
        // get options for each question
        foreach ($questions as $question) {
            $op = $db->getRecordset("SELECT * FROM question_option WHERE qo_question_id = '".$question['q_id']."'");
            $options[$question['q_id']] = $op;
        }

        // course in my subs
        $session = $db->getSession();
        $user_id = $session['trenova_user']['user_id'];
        $inmysubs = $db->getOneRecord("SELECT * FROM subscription WHERE sub_course_id='".$course['course_id']."' AND sub_user_id = '$user_id'");
        $response['course_inmysubs'] = $inmysubs ? true : false;

        $response['quiz_results'] = $quiz_results;
        $response['course'] = $course;
        $response['questions'] = $questions;
        $response['options'] = $options;
        $response['status'] = "success";
        $response["message"] = "Quiz Results Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading result!";
        echoResponse(201, $response);
    }
});

// get my quiz list - quizzes taken by logged in user
$app->get('/getMyQuizList', function() use ($app) {
    
    $response = array();
    $db = new DbHandler();
    $session = $db->getSession();
    $user_id = $session['trenova_user']['user_id'];
    
    $quizzes = $db->getRecordset("SELECT uq_id, uq_score, uq_date_taken, course_id, course_title FROM user_quiz LEFT JOIN course ON uq_target_id=course_id WHERE uq_user_id = '$user_id' ORDER BY uq_date_taken DESC");

    if($quizzes) {
        $response['quizzes'] = $quizzes;
        $response['status'] = "success";
        $response["message"] = "Quizzes Found!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No Quiz found for current user!";
        echoResponse(201, $response);
    }
});