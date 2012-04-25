<?php

// require stuffs
require 'vendors/Slim/Slim/Slim.php';
require 'vendors/Slim-Extras/Views/MustacheView.php';
require 'vendors/couchsimple.php';

// set up the app
MustacheView::$mustacheDirectory = 'vendors';
$app = new Slim(array('view' => 'MustacheView'));

// Setup Couchbase connected objects
try {
  $cb = new Couchbase("127.0.0.1:9000", "Administrator", "asdasd", "beer-sample");
} catch (ErrorException $e) {
  die($e->getMessage());
}

$cbv = new CouchSimple(array('host'=>'127.0.0.1', 'port'=>9500));

// openbeers application goodness

// GET route
$app->get('/', function () {
    echo '<a href="beers">beers!</a><br />';
    echo '<a href="breweries">breweries!</a>';
});
// beer routes
require_once 'beers.php';
// brewery routes
require_once 'breweries.php';
// run, Slim, run
$app->run();
