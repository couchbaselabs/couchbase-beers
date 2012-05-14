<?php
if (!defined('INSIDE_BEERNIQUE')) {
  // redirect user to index.php through which all beer flows
  header('Location: ../');
}

// GET /breweries
$app->get('/breweries/', function() use ($app, $cb) {
  $app->response()->status(501);
  // TODO: implement this in the 2.0 version
  // maybe, for now, rather than giving a list, just send a
  // "search" form for entering the *exact* brewery name
});

$app->get('/breweries/:id', function($id) use ($app, $cb) {
  $brewery_id = 'brewery_' . str_replace(' ', '_', urldecode($id));
  $brewery = json_decode($cb->get($brewery_id));
  if ($brewery !== null) {
    if (isset($brewery->geo) && isset($brewery->geo->loc)
        && is_array($brewery->geo->loc)
        && count($brewery->geo->loc) > 0) {
      $brewery->geo->latitude = $brewery->geo->loc[0];
      $brewery->geo->longitude = $brewery->geo->loc[1];
    } else {
      unset($brewery->geo);
    }

    $app->view()->appendData((array)$brewery);
    $content = $app->view()->render('brewery.mustache');
    $app->render('layout.mustache', compact('content'));
  } else {
    $app->notFound();
  }
});