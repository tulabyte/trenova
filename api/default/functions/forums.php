<?php

$app->get('/getCourseComments', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $course_id = $db->purify($app->request->get('course_id'));

    $session = $db->getSession();
    $user_id = $session['trenova_user']['user_id'];
    
    $comments = $db->getRecordset("SELECT cfc_id, cfc_comment, cfc_time_posted, user_photo, user_fullname, user_id 
        FROM course_forum_comment 
        LEFT JOIN user ON cfc_user_id = user_id
        WHERE cfc_course_id = '$course_id'
        AND cfc_is_approved = '1'
        ORDER BY cfc_time_posted ASC");
    if($comments) {
        //courses found
        $count = count($comments);
        $response['comments'] = $comments;
        $response['status'] = "success";
        $response["message"] = "$count Comments Found!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No comment found!";
        echoResponse(201, $response);
    }
});

$app->post('/createForumComment', function() use ($app) {

    $response = array();

    $r = json_decode($app->request->getBody());

    $db = new DbHandler();

    verifyRequiredParams(['cfc_course_id', 'cfc_user_id', 'cfc_comment'],$r->comment);
    $cfc_course_id = $db->purify($r->comment->cfc_course_id);
    $cfc_user_id = $db->purify($r->comment->cfc_user_id);
    $cfc_comment = $db->purify($r->comment->cfc_comment);
        
	$table_name = "course_forum_comment";
	$column_names = ['cfc_course_id', 'cfc_user_id', 'cfc_comment', 'cfc_time_posted'];
	$values = [$cfc_course_id, $cfc_user_id, $cfc_comment, date("Y-m-d H:i:s")];
	$cfc_id = $db->insertToTable($values, $column_names, $table_name);

	if ($cfc_id) {
	    $new_comment = $db->getOneRecord("SELECT cfc_id, cfc_comment, cfc_time_posted, user_photo, user_fullname, user_id FROM course_forum_comment 
        	LEFT JOIN user ON cfc_user_id = user_id
        	WHERE cfc_id = '$cfc_id'");
	    $response["status"] = "success";
	    $response["message"] = "Comment created successfully";
	    $response["new_comment"] = $new_comment;            

	    echoResponse(200, $response);
	} else {
	    $response["status"] = "error";
	    $response["message"] = "Failed to create comment. Please try again";
	    echoResponse(201, $response);
	}
});

// get course list
$app->get('/getForumComment', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $cfc_id = $db->purify($app->request->get('id'));  
    
    $course = $db->getOneRecord("SELECT * FROM course  WHERE course_status = 'ACTIVE' AND course_id = '$cfc_id' ");

    $forum_comments = $db->getRecordset("SELECT cfc_comment, cfc_user_id, cfc_time_posted, user_fullname FROM course_forum_comment LEFT JOIN user ON cfc_user_id = user_id  WHERE cfc_is_approved = '1' AND cfc_course_id = '$cfc_id' ");    
                //course found
            if($course) {
                $response['course'] = $course;
                $response['forum_comments'] = $forum_comments;
                $response['status'] = "success";
                $response["message"] = "Courses Found!";
                echoResponse(200, $response);
            } else {
                $response['status'] = "error";
                $response["message"] = "No course found!";
                echoResponse(201, $response);
            }
});

$app->post('/createForumComment', function() use ($app) {
    
    $response = array();

    $r = json_decode($app->request->getBody());
    verifyRequiredParams(['comment', 'course_id'],$r->forum);
    $db = new DbHandler();

    $cfc_comment = $db->purify($r->forum->comment);
    $cfc_course_id = $db->purify($r->forum->course_id);
    $cfc_user_id = 0;
    $cfc_time_posted = date('Y-m-d h:i:s');
    
    //check if course exist
    $isCourseExists = $db->getOneRecord("SELECT 1 FROM course WHERE course_id = '$cfc_course_id'");
    if($isCourseExists){
        
        $table_name = "course_forum_comment";
        $column_names = ['cfc_comment', 'cfc_course_id', 'cfc_user_id', 'cfc_time_posted'];
        $values = [$cfc_comment, $cfc_course_id, $cfc_user_id, $cfc_time_posted];
        $result = $db->insertToTable($values, $column_names, $table_name);

        if ($result != NULL) {
            $response["status"] = "success";
            $response["message"] = "Comment created successfully";
            $response["less_id"] = $result;

            //log action
/*            $log_details = "Created New Lesson: $less_title (ID: $result)";
            $db->logAction($log_details);            */

            echoResponse(200, $response);
        } else {
            $response["status"] = "error";
            $response["message"] = "Failed to post comment. Please try again";
            echoResponse(201, $response);
        }            
    }else{
        $response["status"] = "error";
        //$response['message'] = $r->module;
        $response["message"] = "Course does not exist!";
        echoResponse(201, $response);
    }
});