<?php

// require stuffs
require 'vendors/Resty/Resty.php';
require 'vendors/Slim/Slim/Slim.php';
require 'vendors/Slim-Extras/Views/MustacheView.php';

// set up the app
MustacheView::$mustacheDirectory = 'vendors';
$app = new Slim(array('view' => 'MustacheView'));
$env = $app->environment();
$app->view()->appendData(array(
  'app_title' => 'Beernique',
  'base_url' => $env['SCRIPT_NAME'],
  'current_url' => $env['PATH_INFO']
));

$app->add(new Slim_Middleware_SessionCookie());

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
  $app->render('layout.mustache', compact('content') + array('on_index' => true));
});

// GET BrowserID verification
$app->post('/browserid/login', function () use ($app, $cb) {
  header('Content-Type: application/json');
  $resty = new Resty();
  $resty->debug(true);
  $assertion=$app->request()->post('assertion');
  // get the POSTed assertion
  $post_data = array('assertion' => $assertion, 'audience' => $_SERVER['SERVER_NAME']);
  // SERVER is my site's hostname
  $resty->setBaseURL('https://browserid.org/');
  // This makes a post request to browserid.org
  $r = $resty->post('verify',$post_data);

  if ($r['body']->status == 'okay') {
    // This logs the user in if we have an account for that email address,
    // or creates it otherwise
    //$email = sha1($r['body']['email']);
    $email = $_SESSION['email'] = $r['body']->email;
    if ($cb->get(sha1($email)) === null) {
      $cb->set(sha1($email), '');
    }
    echo json_encode($email);
  } else {
    $msg = 'Could not log you in';
    $status = false;
    echo json_encode(array('message'=>$msg,'status'=>$status));
  }
});

$app->post('/browserid/logout', function() use ($app) {
  unset($_SESSION['email']);
});

$app->get('/browserid/whoami', function() use ($app) {
  header('Content-Type: application/json');
  if (isset($_SESSION['email'])) {
    echo json_encode($_SESSION['email']);
  }
});

// beer routes
require_once 'beers.php';
// brewery routes
require_once 'breweries.php';
// run, Slim, run
$app->run();
