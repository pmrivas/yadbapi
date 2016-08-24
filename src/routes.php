<?php
// Routes

/*
$app->get('/[{name}]', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});*/
$app->get('/init', function ($request, $response, $args) { //Get init info...
  $this->logger->info("yadbapi init");
	//$id=$args['id']*1;
	$initdata=new stdClass;
	$initdata->user="Test User";
  return $response->withJson($initdata);
});
