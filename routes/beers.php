<?php
if (!defined('INSIDE_BEERNIQUE')) {
  // redirect user to index.php through which all beer flows
  header('Location: ../');
}

function breweryUrl($name) {
  return '../breweries/' . str_replace(' ' , '_', $name);
}

// GET /beers
$app->get('/beers/', function() use ($app, $cb) {
  if ($app->request()->params('id') !== null) {
    $app->response()->redirect(str_replace(' ', '_', urldecode($app->request()->params('id'))));
  } else {
    $app->response()->status(501);
  }
  // TODO: implement this in the 2.0 version
  // maybe, for now, rather than giving a list, just send a
  // "search" form for entering the *exact* beer name
});

$app->get('/beers/:id', function($id) use ($app, $cb) {
  if (!isset($_SESSION['email'])) {
    $app->response()->status(401);
  } else {
    $beer_id = 'beer_' . str_replace(' ', '_', urldecode($id));
    $beer = json_decode($cb->get($beer_id));
    if ($beer !== null) {
      if (isset($beer->brewery)) {
        $beer->brewery_url = breweryUrl($beer->brewery);
      }
      $app->view()->appendData((array)$beer);
      $content = $app->view()->render('beer.mustache');
      $app->render('layout.mustache', compact('content'));
    } else {
      $app->notFound();
    }
  }
});

// POST route for "drinking"
$app->post('/beers/', function () use ($app, $cb) {
  // TODO: add better login required handler thing
  if (!isset($_SESSION['email'])) {
    $app->halt(401);
  }
  $id = $app->request()->params('id');
  if ($id === null) {
    // TODO: switch to $app->halt();
    $app->response()->status(501);
  }
  // TODO: handler errors

  $beer_id = 'beer_' . str_replace(' ', '_', urldecode($id));
  if ($cb->get($beer_id) === null) {
    $app->halt(404);
  }
  $email = sha1($_SESSION['email']);
  if ($cb->get($email) !== null) {
    $cb->append($email, '|' . $beer_id);
  } else {
    $cb->set($email, $beer_id);
  }
  $app->redirect('../beers/' . $id);
});