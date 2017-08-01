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