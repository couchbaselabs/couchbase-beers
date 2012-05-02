<?php

// require stuffs
require 'vendors/Slim/Slim/Slim.php';
require 'vendors/Slim-Extras/Views/MustacheView.php';

// set up the app
MustacheView::$mustacheDirectory = 'vendors';
$app = new Slim(array('view' => 'MustacheView'));
$env = $app->environment();
$app->view()->appendData(array(
  'app_title' => 'Couchbase Beers',
  'base_url' => $env['SCRIPT_NAME'],
  'current_url' => $env['PATH_INFO']
));

// Setup Couchbase connected objects
try {
  $cb = new Couchbase("127.0.0.1:8091", "Administrator", "asdasd", "beer-sample");
} catch (ErrorException $e) {
  die($e->getMessage());
}

// openbeers application goodness

// GET route
$app->get('/', function () use ($app) {
  $content = $app->view()->render('index.mustache');
  $app->render('layout.mustache', compact('content'));
});
// beer routes
require_once 'beers.php';
// brewery routes
require_once 'breweries.php';
// run, Slim, run
$app->run();
