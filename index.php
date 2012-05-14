<?php

// require stuffs
require 'vendors/Resty/Resty.php';
require 'vendors/Slim/Slim/Slim.php';
require 'vendors/Slim-Extras/Views/MustacheView.php';

const INSIDE_BEERNIQUE = true;

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
  $cb = new Couchbase("127.0.0.1:8091", "", "", "beer-sample");
} catch (ErrorException $e) {
  die($e->getMessage());
}


// GET route
$app->get('/', function () use ($app, $cb) {
  $on_index = true;
  if (isset($_SESSION['email']) && $_SESSION['email'] !== ''
      && ($users_beers = $cb->get(sha1($_SESSION['email']))) !== null) {
    $users_beers = array_filter(explode('|', $users_beers));
    $users_beer_counts = array_count_values($users_beers);
    if (count($users_beers) > 0) {
      $breweries = array();
      $unique_beers = array();
      foreach ($users_beers as $beer_id) {
        if (in_array($beer_id, $unique_beers)) {
          continue;
        }
        $beer = json_decode($cb->get($beer_id));
        $beer->beer_url = 'beers/' . str_replace(' ', '_', $beer->name);
        $beer->brewery_url = 'breweries/' . str_replace(' ', '_', $beer->brewery);
        $beer->drank_times = $users_beer_counts[$beer_id];
        $beers[] = $beer;
        $unique_beers[] = $beer_id;
        if (!isset($breweries[$beer->brewery])) {
          $breweries[$beer->brewery] = 1;
        } else {
          $breweries[$beer->brewery]++;
        }
      }
    }
  }
  $content = $app->view()->render('index.mustache');
  if (isset($beers)) {
    $app->render('layout.mustache',
                compact('content', 'beers', 'on_index')
                  + array('has_beers' => (count($beers) > 0),
                          'mostly_drink' => str_replace('_', ' ', max_key($users_beers))
                    )
              );
  } else {
    $app->render('layout.mustache', compact('content', 'on_index'));
  }
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
  $_SESSION['email'] = null;
});

$app->get('/browserid/whoami', function() use ($app) {
  $app->response()->header('Content-Type', 'application/json');
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
