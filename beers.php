<?php
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
    $beer = json_decode($cb->get($beer_id), true);
    if ($beer !== null) {
      $cb->append(sha1($_SESSION['email']), $beer_id . '|');
      if (isset($beer['brewery'])) {
        $beer['brewery_url'] = breweryUrl($beer['brewery']);
      }
      $app->view()->appendData($beer);
      $content = $app->view()->render('beer.mustache');
      $app->render('layout.mustache', compact('content'));
    } else {
      $app->notFound();
    }
  }
});

//POST route for "drinking"
$app->post('/beers/:id', function () {
    echo 'This is a POST route';
    // TODO: require {"drank_at": $timestamp}
    // TODO: record entry in the user's doc (with timestamp?)
});