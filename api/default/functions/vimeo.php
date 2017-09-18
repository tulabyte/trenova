<?php

// get school
$app->get('/getVimeoVideo', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $id = $db->purify($app->request->get('id'));

    $vimeo = new \Vimeo\Vimeo('6bc8dc86eb8c9dad69c4ac0ee0d3c22603a83375', 'sNZZOiGcA8y6gg3p61TPe/nNYzvBlI7lQ3X3d/+FoA2hlD5b21PwGaydtVlBnnp+BaEalb+i8DeYtGsm+dm0aCoMw4mbPWjAI9ykMQUmC8EoPQ0VDI9FGPU3PYNLRGhn');

	$vimeo->setToken('01384295fe0a810b56aecf904b8a4358');

    $vimeo_response = $vimeo->request('/me/videos/'.$id, array('per_page' => 1), 'GET');

    if($vimeo_response) {
    	// return vimeo response
        $response['vimeo_response'] = $vimeo_response;
        $response['status'] = "success";
        $response["message"] = "Vimeo Video Loaded!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Error connecting to Vimeo!";
        echoResponse(201, $response);
    }
});