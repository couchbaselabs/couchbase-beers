<?php
function breweryUrl($name) {
  return '../breweries/' . str_replace(' ' , '_', $name);
}

// GET /beers
$app->get('/beers/', function() use ($app, $cb, $cbv) {
  if ($by = $app->request()->get('by')) {
    $by = str_replace('_', ' ', $by);
    $qp = '&' . http_build_query(array('startkey' => '["' . $by . '"]',
                                      'endkey' => '["' . $by . '", "Z"]'));
  } else {
    $qp = '';
  }
  $url = '/beer-sample/_design/dev_beer/_view/brewery_beers?full_set=true&reduce=false' . $qp;
  $beers = json_decode($cbv->send('GET', $url), true);
  //$beers = $cb->view('_design/dev_beer/_view/beers', '');
  $beers_by_brewery = array();
  if (!isset($beers['error']) && $beers['total_rows'] > 0) {
    $i = -1;
    $beer_count = 0;
    foreach ($beers['rows'] as $beer) {
      // if this is a brewery entry...
      if (count($beer['key']) == 1) {
        // add the count to the last brewery
        $i++;
        $beer_count = 0;
        $beers_by_brewery[$i] = array('brewery' => $beer['key'][0],
                                      'brewery_url' => breweryUrl($beer['key'][0]),
                                      'beers'=> array());
      } else if (count($beer['key']) > 1) {
        $beer_count++;
        $beers_by_brewery[$i]['beers'][] = array('beer' => $beer['key'][1],
                                                'beer_url' => urlencode($beer['key'][1]));
        $beers_by_brewery[$i]['beer_count'] = $beer_count;
      }
    }
    $app->view()->appendData(array('beers_by_brewery'=>$beers_by_brewery));
    $content = $app->view()->render('beers.mustache');
  } else {
    $content = $beers['reason'] || 'error...sorry';
  }
  $app->render('layout.mustache', compact('content'));
});

$app->get('/beers/:id', function($id) use ($app, $cb, $cbv) {
  $url = '/beer-sample/beer_' . str_replace(' ', '_', urldecode($id));
  $beer = json_decode($cbv->send('GET', $url), true);
  if (isset($beer['error']) && $beer['error'] == 'not_found') {
    $app->notFound();
  }
  if (isset($beer['brewery'])) {
    $beer['brewery_url'] = breweryUrl($beer['brewery']);
  }
  $app->view()->appendData($beer);
  $content = $app->view()->render('beer.mustache');
  $app->render('layout.mustache', compact('content'));
});

//POST route
$app->post('/beers', function () {
    echo 'This is a POST route';
});

//PUT route
$app->put('/beers/:id', function ($id) {
    echo 'This is a PUT route';
});

//DELETE route
$app->delete('/beers/:id', function ($id) {
    echo 'This is a DELETE route';
});