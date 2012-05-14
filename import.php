<?php

require_once 'config.php';

// Setup Couchbase connected objects
try {
  $cb = new Couchbase(COUCHBASE_HOST.':'.COUCHBASE_PORT, COUCHBASE_USER, COUCHBASE_PASSWORD, COUCHBASE_BUCKET);
} catch (ErrorException $e) {
  die($e->getMessage());
}

// import a directory
function import($cb, $dir) {
  $d = dir($dir);
  while (false !== ($file = $d->read())) {
    if (substr($file, -5) != '.json') continue;
    echo "adding $file\n";
    $json = json_decode(file_get_contents($dir . $file), true);
    unset($json["_id"]);
    echo $cb->set(substr($file, 0, -5), json_encode($json));
    echo "\n";
  }
}

// import beers
import($cb, 'beer-sample/beer/');
import($cb, 'beer-sample/breweries/');
