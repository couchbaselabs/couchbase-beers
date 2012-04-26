<?php
// GET /breweries
$app->get('/breweries/', function() use ($app, $cb, $cbv) {
  // request reduced list of brewery names with their beer counts
  $breweries = json_decode($cbv->send('GET', '/beer-sample/_design/dev_beer/_view/brewery_beers?full_set=true&group_level=1'), true);
  //$breweries = $cb->view('_design/dev_beer/_view/brewery_beers?full_set=true&group_level=1', '');
  // loop through the list of brewery names removing the brewery "entries" from the index
  // and building up an array of ($brewery_name => $beer_count)
  $beer_counts = array();
  if (!isset($breweries['error']) && count($breweries['rows']) > 0) {
    foreach ($breweries['rows'] as $entry) {
      $beer_counts[] = array('name' => $entry['key'][0],
                            'url' => urlencode(str_replace(' ', '_', $entry['key'][0])),
                            // the brewery doc itself adds to the count of docs
                            // so we're removing it here.
                            'count' => $entry['value'] - 1,
                            'beers_url' => '../beers/?by=' . $entry['key'][0]);
    }
    $app->view()->appendData(array('breweries'=> $beer_counts));
    $content = $app->view()->render('breweries.mustache');
  } else {
    $content = $breweries['reason'];
  }
  $app->render('layout.mustache', compact('content'));
  exit;
  $ids = array();
  if (!isset($breweries['error']) && $breweries['total_rows'] > 0) {
    foreach ($breweries['rows'] as $beer) {
      array_push($ids, $beer['id']);
    }
    print_r(var_dump($cb->getMulti($ids)));
    //echo json_encode($ids);
  } else {
    echo $breweries['reason'];
  }
});

$app->get('/breweries/:id', function($id) use ($app, $cb, $cbv) {
  $brewery = json_decode($cbv->send('GET', '/beer-sample/brewery_' . str_replace(' ', '_', urldecode($id))), true);
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

//POST route
$app->post('/breweries', function () {
    echo 'This is a POST route';
});

//PUT route
$app->put('/breweries/:id', function ($id) {
    echo 'This is a PUT route';
});

//DELETE route
$app->delete('/breweries/:id', function ($id) {
    echo 'This is a DELETE route';
});