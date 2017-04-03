<?php

// create course
$app->post('/createCourse', function() use ($app) {
    
    $response = array();

    $r = json_decode($app->request->getBody());
    verifyRequiredParams(['course_title', 'course_class_id', 'course_subject_id', 'course_term', 'course_summary', 'course_price', 'course_image', 'course_description'],$r->course);
    //require_once 'passwordHash.php';
    $db = new DbHandler();
    $course_title = $db->purify($r->course->course_title);
    $course_class_id = $db->purify($r->course->course_class_id);
    $course_subject_id = $db->purify($r->course->course_subject_id);
    $course_term = $db->purify($r->course->course_term);
    $course_summary = $db->purify($r->course->course_summary);
    $course_price = $db->purify($r->course->course_price);    
    $course_image = $db->purify($r->course->course_image);
    $course_description = $db->purify($r->course->course_description);
    $course_is_featured = ($r->course->course_is_featured) ? 1 : 0;
    $course_time_created = date("Y-m-d H:i:s");
    //check if course already exists with same title
    $isCourseExists = $db->getOneRecord("SELECT 1 FROM course WHERE course_title='$course_title'");
    if(!$isCourseExists){
        //the title has not yet been used
        //$r->course->password = passwordHash::hash($password);
        $table_name = "course";
        $column_names = ['course_title', 'course_class_id', 'course_subject_id', 'course_term', 'course_summary', 'course_price', 'course_image', 'course_description', 'course_is_featured', 'course_time_created'];
        $values = [$course_title, $course_class_id, $course_subject_id, $course_term, $course_summary, $course_price,$course_image, $course_description, $course_is_featured, $course_time_created];

        $result = $db->insertToTable($values, $column_names, $table_name);

        if ($result != NULL) {
            $response["status"] = "success";
            $response["message"] = "Course created successfully";
            $response["course_id"] = $result;

            //log action
/*            $log_details = "Created New Course: $course_title (ID: $result)";
            $db->logAction($log_details);            
*/
            echoResponse(200, $response);
        } else {
            $response["status"] = "error";
            $response["message"] = "Failed to create course. Please try again";
            echoResponse(201, $response);
        }            
    }else{
        $response["status"] = "error";
        //$response['message'] = $r->course;
        $response["message"] = "Course with the provided title already exists, please try another!";
        echoResponse(201, $response);
    }
});

// create module
$app->post('/createModule', function() use ($app) {
    
    $response = array();

    $r = json_decode($app->request->getBody());
    verifyRequiredParams(['less_title', 'less_content', 'less_course_id'],$r->module);
    $db = new DbHandler();
    $less_title = $db->purify($r->module->less_title);
    $less_content = $db->purify($r->module->less_content);
    $less_position = $db->purify($r->module->less_position);
    $less_course_id = $db->purify($r->module->less_course_id);
    
    //check if module already exists with same title for same course
    $isModuleExists = $db->getOneRecord("SELECT 1 FROM course_lesson WHERE less_title='$less_title' AND less_course_id = '$less_course_id'");
    if(!$isModuleExists){
        //the title has not yet been used

        //if position is empty, derive new position
        if ($less_position == '') {
            $maxPos = $db->getOneRecord("SELECT MAX(less_position) AS max_pos FROM course_lesson WHERE less_course_id = '$less_course_id'");
            $less_position = $maxPos['max_pos'] + 1;
        }

        $table_name = "course_lesson";
        $column_names = ['less_title', 'less_course_id', 'less_content', 'less_position'];
        $values = [$less_title, $less_course_id, $less_content, $less_position];

        $result = $db->insertToTable($values, $column_names, $table_name);

        if ($result != NULL) {
            $response["status"] = "success";
            $response["message"] = "Module created successfully";
            $response["less_id"] = $result;

            //log action
            $log_details = "Created New Module: $less_title (ID: $result)";
            $db->logAction($log_details);            

            echoResponse(200, $response);
        } else {
            $response["status"] = "error";
            $response["message"] = "Failed to create module. Please try again";
            echoResponse(201, $response);
        }            
    }else{
        $response["status"] = "error";
        //$response['message'] = $r->module;
        $response["message"] = "Module with the provided title already exists for this course, please try another!";
        echoResponse(201, $response);
    }
});

// create course rating
$app->post('/createCourseRating', function() use ($app) {
    
    $response = array();

    $r = json_decode($app->request->getBody());
    verifyRequiredParams(['user_id', 'course_id', 'rate'],$r->rating);
    $db = new DbHandler();
    $user_id = $db->purify($r->rating->user_id);
    $course_id = $db->purify($r->rating->course_id);
    $rate = $db->purify($r->rating->rate);
    $comment = $db->purify($r->rating->comment);
    
    //check if rating already exists for same user and course
    $isRatingExists = $db->getOneRecord("SELECT 1 FROM course_rating WHERE cr_course_id='$course_id' AND cr_user_id = '$user_id'");
    if(!$isRatingExists){
        //no rating yet
        $table_name = "course_rating";
        $column_names = ['cr_user_id', 'cr_course_id', 'cr_rating', 'cr_comment'];
        $values = [$user_id, $course_id, $rate, $comment];

        $result = $db->insertToTable($values, $column_names, $table_name);

        if ($result != NULL) {
            $response["status"] = "success";
            $response["message"] = "Rating created successfully";
            $response["less_id"] = $result;

            echoResponse(200, $response);
        } else {
            $response["status"] = "error";
            $response["message"] = "Failed to create rating. Please try again";
            echoResponse(201, $response);
        }            
    } else {
        $response["status"] = "error";
        //$response['message'] = $r->rating;
        $response["message"] = "Your rating already exists for this course, please try another!";
        echoResponse(201, $response);
    }
});

// edit course
$app->post('/editCourse', function() use ($app) {
    
    $response = array();

    $r = json_decode($app->request->getBody());
    verifyRequiredParams(['course_id', 'course_title', 'course_category_id', 'course_price',  'course_summary', 'course_image', 'course_description'],$r->course);
    //require_once 'passwordHash.php';
    $db = new DbHandler();
    $course_id = $db->purify($r->course->course_id);
    $course_title = $db->purify($r->course->course_title);
    $course_category_id = $db->purify($r->course->course_category_id);
    $course_price = $db->purify($r->course->course_price);
    $course_summary = $db->purify($r->course->course_summary);
    $course_image = $db->purify($r->course->course_image);
    $course_description = $db->purify($r->course->course_description);
    $course_is_featured = ($r->course->course_is_featured) ? '1':'';

    $isCourseExists = $db->getOneRecord("SELECT 1 FROM course WHERE course_id='$course_id'");
    if($isCourseExists){
        //$r->course->password = passwordHash::hash($password);
        $table_to_update = "course";
        $columns_to_update = ['course_title'=>$course_title,'course_category_id'=>$course_category_id, 'course_price'=>$course_price, 'course_summary'=>$course_summary, 'course_image'=>$course_image, 'course_description'=>$course_description, 'course_is_featured'=>$course_is_featured];
        $where_clause = ['course_id'=>$course_id];

        $result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

        if ($result > 0) {
            //log action
            $log_details = "Edited Course: $course_title (ID: $course_id)";
            $db->logAction($log_details);

            $response["status"] = "success";
            $response["message"] = "Course updated successfully";
            echoResponse(200, $response);
        } else {
            $response["status"] = "error";
            $response["message"] = "Failed to update course. Please try again";
            echoResponse(201, $response);
        }            
    }else{
        $response["status"] = "error";
        //$response['message'] = $r->course;
        $response["message"] = "ERROR: Course does not exist!";
        echoResponse(201, $response);
    }
});

// edit module
$app->post('/editModule', function() use ($app) {
    
    $response = array();

    $r = json_decode($app->request->getBody());
    verifyRequiredParams(['less_id', 'less_title', 'less_content', 'less_position'],$r->module);
    $db = new DbHandler();
    $less_id = $db->purify($r->module->less_id);
    $less_title = $db->purify($r->module->less_title);
    $less_content = $db->purify($r->module->less_content);
    $less_position = $db->purify($r->module->less_position);

    $isModuleExists = $db->getOneRecord("SELECT 1 FROM course_lesson WHERE less_id='$less_id'");
    if($isModuleExists){
        $table_to_update = "course_lesson";
        $columns_to_update = ['less_title'=>$less_title,'less_content'=>$less_content, 'less_position'=>$less_position];
        $where_clause = ['less_id'=>$less_id];

        $result = $db->updateInTable($table_to_update, $columns_to_update, $where_clause);

        if ($result > 0) {
            //log action
            $log_details = "Edited Module: $less_title (ID: $less_id)";
            $db->logAction($log_details);

            $response["status"] = "success";
            $response["message"] = "Module updated successfully";
            echoResponse(200, $response);
        } else {
            $response["status"] = "error";
            $response["message"] = "Failed to update module. Please try again";
            echoResponse(201, $response);
        }            
    }else{
        $response["status"] = "error";
        //$response['message'] = $r->module;
        $response["message"] = "ERROR: Module does not exist!";
        echoResponse(201, $response);
    }
});

// get course
$app->get('/getCourse', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $course_id = $db->purify($app->request->get('id'));
    
    $course = $db->getOneRecord("SELECT * FROM course WHERE course_id='$course_id'");
    if($course) {
        //found course, return success result

        $response['course'] = $course;
        $response['status'] = "success";
        $response["message"] = "Course Details Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading course!";
        echoResponse(201, $response);
    }
});

// get course list
$app->get('/getCourseList', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    
    $courses = $db->getRecordset("SELECT *, (SELECT COUNT(*) FROM subscription WHERE sub_course_id = course_id) AS sub_count FROM course LEFT JOIN class ON course_class_id = class_id LEFT JOIN subject ON course_subject_id = sb_id ORDER BY course_title");
    if($courses) {
        //courses found
        $count = count($courses);

        $response['courses'] = $courses;
        $response['status'] = "success";
        $response["message"] = "$count Courses Found!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No course found!";
        echoResponse(201, $response);
    }
});

// get course list for mobile
$app->get('/getCourseListForMobile', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    
    $courses = $db->getRecordset("SELECT *, (SELECT COUNT(*) FROM course_lesson WHERE less_course_id = course_id) AS lesson_count FROM course ORDER BY course_title");
    if($courses) {
        //courses found
        $count = count($courses);

        $response['courses'] = $courses;
        $response['status'] = "success";
        $response["message"] = "$count Courses Found!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No course found!";
        echoResponse(201, $response);
    }
});

$app->get('/getCourseListBySubject', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $subject_id = $db->purify($app->request->get('id'));

    $subject = $db->getOneRecord("SELECT * FROM subject WHERE sb_id = '$subject_id'");
    
    $courses = $db->getRecordset("SELECT *, (SELECT COUNT(*) FROM course_lesson WHERE less_course_id = course_id) AS lesson_count FROM course WHERE course_subject_id = '$subject_id' ORDER BY course_title");
    if($courses) {
        //courses found
        $count = count($courses);
        $response['subject'] = $subject;
        $response['courses'] = $courses;
        $response['status'] = "success";
        $response["message"] = "$count Courses Found!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No course found!";
        echoResponse(201, $response);
    }
});

$app->get('/getCourseListByClass', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $class_id = $db->purify($app->request->get('id'));

    $class = $db->getOneRecord("SELECT * FROM class WHERE class_id = '$class_id'");
    
    $courses = $db->getRecordset("SELECT *, (SELECT COUNT(*) FROM course_lesson WHERE less_course_id = course_id) AS lesson_count FROM course WHERE course_class_id = '$class_id' ORDER BY course_title");
    if($courses) {
        //courses found
        $count = count($courses);

        $response['class'] = $class;
        $response['courses'] = $courses;
        $response['status'] = "success";
        $response["message"] = "$count Courses Found!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No course found!";
        echoResponse(201, $response);
    }
});

// get course list - frontend
$app->get('/getCourseListF', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    
    $courses = $db->getRecordset("SELECT *, 
        (SELECT COUNT(*) FROM course_lesson WHERE less_course_id = course_id) AS lesson_count 
        FROM course 
        ORDER BY course_title");
    if($courses) {
        //courses found
        $count = count($courses);

        $response['courses'] = $courses;
        $response['status'] = "success";
        $response["message"] = "$count Courses Found!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "ERROR - No course found in database!";
        echoResponse(201, $response);
    }
});

// get course list - favourites
$app->get('/getCourseFavList', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $session = $db->getSession();
    $user_id = $session['trv_id'];
    
    $courses = $db->getRecordset("SELECT *, 
        (SELECT COUNT(*) FROM course_lesson WHERE less_course_id = course_id) AS lesson_count 
        FROM course 
        LEFT JOIN favourite ON course_id = fav_course_id 
        WHERE fav_user_id = '$user_id' 
        ORDER BY course_title");
    if($courses) {
        //courses found
        $count = count($courses);

        $response['courses'] = $courses;
        $response['status'] = "success";
        $response["message"] = "$count Courses Found!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "ERROR - No course found in your favourites!";
        echoResponse(201, $response);
    }
});

// get course list - subscriptions
$app->get('/getCourseSubList', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $session = $db->getSession();
    $user_id = $session['trv_id'];
    
    $courses = $db->getRecordset("SELECT *, 
        (SELECT COUNT(*) FROM course_lesson WHERE less_course_id = course_id) AS lesson_count 
        FROM course 
        LEFT JOIN subscription ON course_id = sub_course_id 
        WHERE sub_user_id = '$user_id' 
        ORDER BY sub_date_started DESC");
    if($courses) {
        //categories found
        $count = count($courses);

        $response['courses'] = $courses;
        $response['status'] = "success";
        $response["message"] = "$count Courses Found!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "ERROR - No course found in your subscriptions!";
        echoResponse(201, $response);
    }
});

// get module list
$app->get('/getModuleList', function() use ($app) {
    $response = array();
    
    $db = new DbHandler();
    $course_id = $db->purify($app->request->get('course_id'));
    
    $modules = $db->getRecordset("SELECT * FROM course_lesson WHERE less_course_id = '$course_id' ORDER BY less_position, less_title");
    if($modules) {
        //categories found
        $count = count($modules);

        $response['modules'] = $modules;
        $response['status'] = "success";
        $response["message"] = "$count Modules Found!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No module found for this course!";
        echoResponse(201, $response);
    }
});

// delete course
$app->get('/deleteCourse', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $course_id = $db->purify($app->request->get('id'));

    //get course details
    $course = $db->getOneRecord("SELECT * FROM course WHERE course_id='$course_id'");

    $table_name = 'course';
    $col_name = 'course_id';
    $result = $db->deleteFromTable($table_name, $col_name, $course_id);

    //delete course image file
    unlink('../../img/course-images/'.$course['course_image']);

    if($result > 0) {
        //course deleted

        //log action
        $log_details = "Deleted Course: ".$course['course_title']." ($course_id)";
        $db->logAction($log_details);

        $response['status'] = "success";
        $response["message"] = "Course Deleted successfully!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error deleting course!";
        echoResponse(201, $response);
    }

    // $response["course_id"] = $course_id;
    // echoResponse(200, $response);
});

// delete module
$app->get('/deleteModule', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $less_id = $db->purify($app->request->get('id'));

    //get module details
    $module = $db->getOneRecord("SELECT * FROM course_lesson WHERE less_id='$less_id'");

    $table_name = 'course_lesson';
    $col_name = 'less_id';
    $result = $db->deleteFromTable($table_name, $col_name, $less_id);

    if($result > 0) {
        //module deleted

        //log action
        $log_details = "Deleted Module: ".$module['less_title']." ($less_id)";
        $db->logAction($log_details);

        $response['status'] = "success";
        $response["message"] = "Module Deleted successfully!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error deleting module!";
        echoResponse(201, $response);
    }
});

// get featured course list
$app->get('/getFeaturedCourseList', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    
    $featured_courses = $db->getRecordset("SELECT *, 
        (SELECT COUNT(*) FROM course_lesson WHERE less_course_id=course_id) AS lesson_count  
        FROM course WHERE course_is_featured > 0 ");
    if($featured_courses) {
        $response['featured_courses'] = $featured_courses;
        $response['status'] = "success";
        $response["message"] = "Featured courses Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading featured courses!";
        echoResponse(201, $response);
    }
});

//get new course list
$app->get('/getNewCourseList', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    
    $new_courses = $db->getRecordset("SELECT *, (SELECT COUNT(*) FROM course_lesson WHERE less_course_id=course_id) AS lesson_count FROM course ORDER BY course_time_created DESC LIMIT 10 ");
    if($new_courses) {
        $response['new_courses'] = $new_courses;
        $response['status'] = "success";
        $response["message"] = "New courses Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error loading new courses!";
        echoResponse(201, $response);
    }
});


//getSearchResults - courses
$app->get('/getSearchResults', function() use ($app) {
    $response = array();
    $db = new DbHandler();
    $search_item = $db->purify($app->request->get('search_item')); 
    
 if (!empty($search_item)) 
    //making sure it is not empty
   {
    $search_results  = $db->getRecordset("SELECT * FROM course WHERE 
     course_title LIKE '%$search_item%' OR
     course_summary LIKE '%$search_item%' OR
     course_description LIKE '%$search_item%' ");
    
    if($search_results ) {
        //found search results, return success result

        //log action
    /*    $log_details = "Accessed Featured Course Details: ".$db->purify($course['course_title'])." (ID: ".$course['course_id'].")";
        $db->logAction($log_details);
*/
        $response['search_results '] = $search_results ;
        $response['status'] = "success";
        $response["message"] = "Details Of Search Results!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error...No Related Match!";
        echoResponse(201, $response);
    } 
}
        else {
                    $response['status'] = "error";
        $response["message"] = "Error...Query Is Empty!";
        echoResponse(201, $response);
}
});


$app->get('/getCourseDetails', function() use ($app) {

    $response = array();

    $db = new DbHandler();
    $course_id = $db->purify($app->request->get('course_id'));
    $session = $db->getSession();
    $user_id = $session['trv_id'];
    
    $course_details = $db->getOneRecord("SELECT * FROM course LEFT JOIN course_category ON course_category_id = cat_id WHERE course_id='$course_id'");

     if($course_details) {

        $response['course_details'] = $course_details;

        // get course modules for selected course
        $course_lessons = $db->getRecordset("SELECT * FROM course_lesson WHERE less_course_id = '$course_id' ORDER BY less_position ASC");
        $response['course_lessons'] = $course_lessons;

        // count number of subscriptions for selected course
        $course_sub_count = $db->getOneRecord("SELECT COUNT(*) AS sub_count FROM subscription WHERE sub_course_id='$course_id'");
        $response['course_sub_count'] = $course_sub_count ? $course_sub_count['sub_count'] : 0;
        // number of favourites
        $course_fav_count = $db->getOneRecord("SELECT COUNT(*) AS fav_count FROM favourite WHERE fav_course_id='$course_id'");
        $response['course_fav_count'] = $course_fav_count ? $course_fav_count['fav_count'] : 0;
        // number of ratings
        $course_rating_count = $db->getOneRecord("SELECT COUNT(*) AS rating_count FROM course_rating WHERE cr_course_id='$course_id'");
        $response['course_rating_count'] = $course_rating_count ? $course_rating_count['rating_count'] : 0 ;
        // average rating
        $course_rating_avg = $db->getOneRecord("SELECT AVG(cr_rating) AS rating_avg FROM course_rating WHERE cr_course_id='$course_id'");
        $response['course_rating_avg'] = $course_rating_avg ? $course_rating_avg['rating_avg'] : 0 ;
        // in favourites
        $in_favs = $db->getOneRecord("SELECT * FROM favourite WHERE fav_user_id = '$user_id' AND fav_course_id = '$course_id' ");
        $response['course_in_favs'] = $in_favs ? true : false;
        // course rated by me
        $ratedbyme = $db->getOneRecord("SELECT * FROM course_rating WHERE cr_course_id='$course_id' AND cr_user_id = '$user_id' ");
        $response['course_ratedbyme'] = $ratedbyme ? true : false;
        // course in my subs
        $inmysubs = $db->getOneRecord("SELECT * FROM subscription WHERE sub_course_id='$course_id' AND sub_user_id = '$user_id'");
        $response['course_inmysubs'] = $inmysubs ? true : false;

        $response['status'] = "success";
        $response["message"] = "Course Details Found!";
        echoResponse(200, $response);

    } else {
        $response['status'] = "error";
        $response["message"] = "Course Details Not Found!";
        echoResponse(201, $response);
    }

});

$app->get('/getTrendingCourseList', function() use ($app) {

    $response = array();

    $db = new DbHandler();
    
    $trending = $db->getRecordset("SELECT *, COUNT(sub_course_id) AS sub_count FROM subscription LEFT JOIN course ON sub_course_id=course_id GROUP BY sub_course_id HAVING sub_count > 0 ORDER BY sub_count DESC LIMIT 10 ");

    if($trending) {
        
        $response['trending_courses'] = $trending;
        $response['status'] = "success";
        $response["message"] = "Trending Courses!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "No Course Has Been Subscribed For!";
        echoResponse(201, $response);
    }
});