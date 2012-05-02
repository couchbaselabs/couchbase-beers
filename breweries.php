<?php
// GET /breweries
$app->get('/breweries/', function() use ($app, $cb) {
  $app->response()->status(501);
  // TODO: implement this in the 2.0 version
  // maybe, for now, rather than giving a list, just send a
  // "search" form for entering the *exact* brewery name
});

$app->get('/breweries/:id', function($id) use ($app, $cb) {
  $brewery = $cb->get('brewery_' . str_replace(' ', '_', urldecode($id)));
  print_r($brewery);
  exit;
  if (isset($brewery['error'])) {
    $content = '<h4>Error: ' . $brewery['error'] . '</h4><pre>' . $brewery['reason'] . '</pre>';
  } else {
    if (isset($brewery['geo']['loc'])
        && is_array($brewery['geo']['loc'])
        && count($brewery['geo']['loc']) > 0) {
      $brewery['geo']['latitude'] = $brewery['geo']['loc'][0];
      $brewery['geo']['longitude'] = $brewery['geo']['loc'][1];
    } else {
      unset($brewery['geo']);
    }

    $app->view()->appendData($brewery);
    $content = $app->view()->render('brewery.mustache');
  }
  $app->render('layout.mustache', compact('content'));
});